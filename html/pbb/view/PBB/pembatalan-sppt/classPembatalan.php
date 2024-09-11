<?php

class SvcPembatalanSPPT {
	private $dbSpec = null;
	
        public $C_HOST_PORT;
        public $C_USER;
        public $C_PWD;
        public $C_DB;
		public $C_PORT = 3306;
		    
	protected $limitRiwayat = null;
    protected $offsetRiwayat = null;
                                
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	public function getGateWayPBBSPPTMulti($noplist,$thn) {
		$LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
                
		// $nop   = mysql_real_escape_string(trim($nop));	
                
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
					PBB_SPPT WHERE NOP in ($noplist)  AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) 
					
					  ";
					  // echo "$query";
					// echo $query;
		if($thn!=""){
			$query .= " and  SPPT_TAHUN_PAJAK = '$thn' ";
		}
					  // AND 
		// if($thn!=""){
		// 	$query .= " (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) AND SPPT_TAHUN_PAJAK = '$thn'";
		// }
		// echo $query;
		$result = mysqli_query($LDBLink, $query);
         // return $result;
                // return $result;
        if (!$result) {
            return false;
        } else 
			return $result;

	}
	
	public function getGateWayPBBSPPTMultiV2($thn=false, $kode=false, $arrKode=array()) {
		if(!$thn) return false;

		$LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
                
		// $nop   = mysql_real_escape_string(trim($nop));
		$arrKode = implode(',',$arrKode);
        $addKode = ($kode) ? " AND NOP LIKE '$kode%'" : " AND NOP IN ($arrKode)";
		$query = "SELECT
					NOP,
					WP_NAMA,
					WP_ALAMAT,
					OP_ALAMAT,
					SPPT_TAHUN_PAJAK,
					SPPT_TANGGAL_JATUH_TEMPO,
					SPPT_PBB_HARUS_DIBAYAR,
					PAYMENT_FLAG
				FROM pbb_sppt 
				WHERE 
					(PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) 
					AND SPPT_TAHUN_PAJAK='$thn' 
					$addKode";

		$result = mysqli_query($LDBLink, $query);

        if (!$result) {
            return false;
        } else 
			return $result;
	}
	
	public function getGateWayPBBSPPT($nop,$thn='') {
		$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
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
				FROM pbb_sppt WHERE NOP LIKE '$nop%' ";
		if($thn!=""){
			$query .= "AND SPPT_TAHUN_PAJAK = '$thn' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL)";
		}
		$query .= "ORDER BY SPPT_TAHUN_PAJAK DESC";
		// echo $query;
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        } else 
			return $result;
    }

    public function getTahunRiwayat($nop) {
    	$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
                
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
                
		$query = "SELECT
					SPPT_TAHUN_PAJAK
				FROM
					PBB_SPPT_PEMBATALAN WHERE NOP LIKE '$nop%' 
				
				";
		// echo $query;
		$result = mysqli_query($LDBLink, $query);
		$string_row = " ";
		while ($row = mysqli_fetch_assoc($result)){
			$string_row.= $row['SPPT_TAHUN_PAJAK'].",";
		}
		$string_row = rtrim($string_row,",");
        return $string_row;

    }
	
	public function limitRiwayatPembatalan($page, $perpage) {
	    $offset = null;
	    if ($page !== null && $perpage !== null) {
            $offset = ($page - 1) * $perpage;
        }

	    $this->limitRiwayat = $perpage;
	    $this->offsetRiwayat = $offset;

	    return $this;
	}

	
    public function getRiwayatPembatalan($jns, $kec, $kel, $nop, $thn='') {
    	$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

		$arrwhere[] = '1=1';

		// echo '<pre>'; print_r($jns . $kec . $kel . $nop); exit;

		if($kel) {
			$arrwhere[] = "p.OP_KELURAHAN_KODE='$kel'";
		}elseif($kec) {
			$arrwhere[] = "p.OP_KECAMATAN_KODE='$kec'";
		}

		$joinFinal = "";
		if($jns) {
			$joinFinal = "INNER JOIN sw_pbb.cppmod_pbb_sppt_final f ON f.CPM_NOP=l.BTL_LOG_NOP";
			$arrwhere[] = "f.CPM_OT_JENIS='$jns'";
		}
		
		if($thn!='') {
			$arrwhere[] = "l.BTL_LOG_TAHUN='$thn'";
		}

        $arrNop = explode(',', $nop);
        if(count($arrNop) > 1) {
            $nopIN = array();
            foreach ($arrNop as $nop) {
                $nop = mysqli_real_escape_string($LDBLink, trim($nop));
				$nop = (int)$nop;
                if($nop==0) continue;
                $nopIN[] = "'$nop'";
            }
            $nopIN = implode(',', $nopIN);
			$arrwhere[] = "(l.BTL_LOG_NOP IN ($nopIN))";
        }else {
            $nop = mysqli_real_escape_string($LDBLink, trim($nop));
			$nop = (int)$nop;
			if($nop!=0) $arrwhere[] = "l.BTL_LOG_NOP='$nop'";
        }


		$where = implode(' AND ', $arrwhere);

		$query="SELECT
					p.NOP,
					p.WP_NAMA,
					p.WP_ALAMAT,
					p.OP_ALAMAT,
					p.OP_KELURAHAN AS KELURAHAN,
					p.OP_KECAMATAN AS KECAMATAN,
					p.SPPT_TAHUN_PAJAK,
					p.SPPT_TANGGAL_JATUH_TEMPO,
					p.SPPT_PBB_HARUS_DIBAYAR,
					p.PAYMENT_FLAG
					/*,NO_SK, ALASAN*/
				FROM pbb_pembatalan_sppt_log l 
				INNER JOIN pbb_sppt_pembatalan p ON l.BTL_LOG_NOP=p.NOP AND l.BTL_LOG_TAHUN=p.SPPT_TAHUN_PAJAK
				$joinFinal 
				WHERE $where
				ORDER BY BTL_LOG_TIME DESC";
				// group by NOP
		

		if($this->limitRiwayat !== null && $this->offsetRiwayat !== null) {
		    $query .= " LIMIT {$this->limitRiwayat} OFFSET {$this->offsetRiwayat}";
        }

		// echo $query;
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        } else 
			return $result;
    }
    public function getRiwayatPenerbitan($jns, $kec, $kel, $nop, $thn='') {
    	$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
        //mysql_select_db($this->C_DB,$LDBLink);

		$arrwhere[] = '1=1';

		// echo '<pre>'; print_r($jns . $kec . $kel . $nop); exit;

		if($kel) {
			$arrwhere[] = "p.OP_KELURAHAN_KODE='$kel'";
		}elseif($kec) {
			$arrwhere[] = "p.OP_KECAMATAN_KODE='$kec'";
		}

		$joinFinal = "";
		if($jns) {
			$joinFinal = "INNER JOIN sw_pbb.cppmod_pbb_sppt_final f ON f.CPM_NOP=t.TBT_LOG_NOP";
			$arrwhere[] = "f.CPM_OT_JENIS='$jns'";
		}
		
		if($thn!='') {
			$arrwhere[] = "t.TBT_LOG_TAHUN='$thn'";
		}

        $arrNop = explode(',', $nop);
        if(count($arrNop) > 1) {
            $nopIN = array();
            foreach ($arrNop as $nop) {
                $nop = mysqli_real_escape_string($LDBLink, trim($nop));
				$nop = (int)$nop;
                if($nop==0) continue;
                $nopIN[] = "'$nop'";
            }
            $nopIN = implode(',', $nopIN);
			$arrwhere[] = "(t.TBT_LOG_NOP IN ($nopIN))";
        }else {
            $nop = mysqli_real_escape_string($LDBLink, trim($nop));
			$nop = (int)$nop;
			if($nop!=0) $arrwhere[] = "t.TBT_LOG_NOP='$nop'";
        }


		$where = implode(' AND ', $arrwhere);

		$query="SELECT
					p.NOP,
					p.WP_NAMA,
					p.WP_ALAMAT,
					p.OP_ALAMAT,
					p.OP_KELURAHAN AS KELURAHAN,
					p.OP_KECAMATAN AS KECAMATAN,
					p.SPPT_TAHUN_PAJAK,
					p.SPPT_TANGGAL_JATUH_TEMPO,
					p.SPPT_PBB_HARUS_DIBAYAR,
					p.PAYMENT_FLAG,
					t.TBT_LOG_ALASAN
				FROM pbb_penerbitan_sppt_log t 
				INNER JOIN pbb_sppt p ON t.TBT_LOG_NOP = p.NOP AND t.TBT_LOG_TAHUN = p.SPPT_TAHUN_PAJAK
				$joinFinal 
				WHERE $where
				ORDER BY t.TBT_LOG_TIME DESC";
				// group by NOP
		

		if($this->limitRiwayat !== null && $this->offsetRiwayat !== null) {
		    $query .= " LIMIT {$this->limitRiwayat} OFFSET {$this->offsetRiwayat}";
        }

		// echo $query;
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        } else 
			return $result;
    }
	
	//==============ROLLBACK PENETAPAN
	public function copyToPembatalanPerKel($nop,$tahun,$no_sk,$alasan,$uname) {
		$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_connect_error());
		//mysql_select_db($this->C_DB,$LDBLink);
		
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "INSERT INTO PBB_SPPT_PEMBATALAN 
				(
					 NOP
					,SPPT_TAHUN_PAJAK
					,SPPT_TANGGAL_JATUH_TEMPO
					,SPPT_PBB_HARUS_DIBAYAR
					,WP_NAMA
					,WP_TELEPON
					,WP_NO_HP
					,WP_ALAMAT
					,WP_RT
					,WP_RW
					,WP_KELURAHAN
					,WP_KECAMATAN
					,WP_KOTAKAB
					,WP_KODEPOS
					,SPPT_TANGGAL_TERBIT
					,SPPT_TANGGAL_CETAK
					,OP_LUAS_BUMI
					,OP_LUAS_BANGUNAN
					,OP_KELAS_BUMI
					,OP_KELAS_BANGUNAN
					,OP_NJOP_BUMI
					,OP_NJOP_BANGUNAN
					,OP_NJOP
					,OP_NJOPTKP
					,OP_NJKP
					,PAYMENT_FLAG
					,PAYMENT_PAID
					,PAYMENT_REF_NUMBER
					,PAYMENT_BANK_CODE
					,PAYMENT_SW_REFNUM
					,PAYMENT_GW_REFNUM
					,PAYMENT_SW_ID
					,PAYMENT_MERCHANT_CODE
					,PAYMENT_SETTLEMENT_DATE
					,PBB_COLLECTIBLE
					,PBB_DENDA
					,PBB_ADMIN_GW
					,PBB_MISC_FEE
					,PBB_TOTAL_BAYAR
					,OP_ALAMAT
					,OP_RT
					,OP_RW
					,OP_KELURAHAN
					,OP_KECAMATAN
					,OP_KOTAKAB
					,OP_KELURAHAN_KODE
					,OP_KECAMATAN_KODE
					,OP_KOTAKAB_KODE
					,OP_PROVINSI_KODE
					,TGL_STPD
					,TGL_SP1
					,TGL_SP2
					,TGL_SP3
					,STATUS_SP
					,STATUS_CETAK
					,WP_PEKERJAAN
					,PAYMENT_OFFLINE_USER_ID
					,ID_WP
					
					
				)					
				  SELECT 
				  	NOP
					,SPPT_TAHUN_PAJAK
					,SPPT_TANGGAL_JATUH_TEMPO
					,SPPT_PBB_HARUS_DIBAYAR
					,WP_NAMA
					,WP_TELEPON
					,WP_NO_HP
					,WP_ALAMAT
					,WP_RT
					,WP_RW
					,WP_KELURAHAN
					,WP_KECAMATAN
					,WP_KOTAKAB
					,WP_KODEPOS
					,SPPT_TANGGAL_TERBIT
					,SPPT_TANGGAL_CETAK
					,OP_LUAS_BUMI
					,OP_LUAS_BANGUNAN
					,OP_KELAS_BUMI
					,OP_KELAS_BANGUNAN
					,OP_NJOP_BUMI
					,OP_NJOP_BANGUNAN
					,OP_NJOP
					,OP_NJOPTKP
					,OP_NJKP
					,PAYMENT_FLAG
					,PAYMENT_PAID
					,PAYMENT_REF_NUMBER
					,PAYMENT_BANK_CODE
					,PAYMENT_SW_REFNUM
					,PAYMENT_GW_REFNUM
					,PAYMENT_SW_ID
					,PAYMENT_MERCHANT_CODE
					,PAYMENT_SETTLEMENT_DATE
					,PBB_COLLECTIBLE
					,PBB_DENDA
					,PBB_ADMIN_GW
					,PBB_MISC_FEE
					,PBB_TOTAL_BAYAR
					,OP_ALAMAT
					,OP_RT
					,OP_RW
					,OP_KELURAHAN
					,OP_KECAMATAN
					,OP_KOTAKAB
					,OP_KELURAHAN_KODE
					,OP_KECAMATAN_KODE
					,OP_KOTAKAB_KODE
					,OP_PROVINSI_KODE
					,TGL_STPD
					,TGL_SP1
					,TGL_SP2
					,TGL_SP3
					,STATUS_SP
					,STATUS_CETAK
					,WP_PEKERJAAN
					,PAYMENT_OFFLINE_USER_ID
					,ID_WP
					
					
				  FROM PBB_SPPT 
				  WHERE NOP LIKE '$nop%' 
				  AND SPPT_TAHUN_PAJAK = '$tahun' 
				  AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ";

		// echo $query;exit;		
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
			$queryReplace = "REPLACE INTO PBB_SPPT_PEMBATALAN
				(
					NOP
					,SPPT_TAHUN_PAJAK
					,SPPT_TANGGAL_JATUH_TEMPO
					,SPPT_PBB_HARUS_DIBAYAR
					,WP_NAMA
					,WP_TELEPON
					,WP_NO_HP
					,WP_ALAMAT
					,WP_RT
					,WP_RW
					,WP_KELURAHAN
					,WP_KECAMATAN
					,WP_KOTAKAB
					,WP_KODEPOS
					,SPPT_TANGGAL_TERBIT
					,SPPT_TANGGAL_CETAK
					,OP_LUAS_BUMI
					,OP_LUAS_BANGUNAN
					,OP_KELAS_BUMI
					,OP_KELAS_BANGUNAN
					,OP_NJOP_BUMI
					,OP_NJOP_BANGUNAN
					,OP_NJOP
					,OP_NJOPTKP
					,OP_NJKP
					,PAYMENT_FLAG
					,PAYMENT_PAID
					,PAYMENT_REF_NUMBER
					,PAYMENT_BANK_CODE
					,PAYMENT_SW_REFNUM
					,PAYMENT_GW_REFNUM
					,PAYMENT_SW_ID
					,PAYMENT_MERCHANT_CODE
					,PAYMENT_SETTLEMENT_DATE
					,PBB_COLLECTIBLE
					,PBB_DENDA
					,PBB_ADMIN_GW
					,PBB_MISC_FEE
					,PBB_TOTAL_BAYAR
					,OP_ALAMAT
					,OP_RT
					,OP_RW
					,OP_KELURAHAN
					,OP_KECAMATAN
					,OP_KOTAKAB
					,OP_KELURAHAN_KODE
					,OP_KECAMATAN_KODE
					,OP_KOTAKAB_KODE
					,OP_PROVINSI_KODE
					,TGL_STPD
					,TGL_SP1
					,TGL_SP2
					,TGL_SP3
					,STATUS_SP
					,STATUS_CETAK
					,WP_PEKERJAAN
					,PAYMENT_OFFLINE_USER_ID
					,ID_WP
					
					
				)
				  SELECT * FROM PBB_SPPT 
				  WHERE NOP LIKE '$nop%' 
				  AND SPPT_TAHUN_PAJAK = '$tahun' 
				  AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ";
				  
			$resultReplace = mysqli_query($LDBLink, $queryReplace);
			if (!$resultReplace) {
				return false;
			}
        } 
        // jika berhasil insert ke table PBB_SPPT_PEMBATALAN
   //      	$query_update = "UPDATE  PBB_SPPT_PEMBATALAN 
			// 	  SET

			// 	   no_sk = '$no_sk' ,
			// 	   alasan = '$alasan',
			// 	   TGL_PEMBATALAN = now(),
			// 	   USER_PEMBATALAN = '$uname'


			// 	  WHERE NOP LIKE '$nop%' 
			// 	  AND SPPT_TAHUN_PAJAK = '$tahun' 
			// 	  AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ";
			// $eksekusi_update = mysql_query($query_update);       
			//  if ($eksekusi_update)
	        	return true;
	  //       else
	        	// return false;
    }
	
	public function delGateWayPBBSPPTPerKel($nop,$tahun) {
		$LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD, $this->C_DB) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
				
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "DELETE FROM PBB_SPPT WHERE NOP LIKE '$nop%' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ";
		if($tahun){
			$query .= "AND SPPT_TAHUN_PAJAK = '$tahun'";
		}
		//print_r($query);		
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        }
        return true;
    }
	
	public function copySPPTCurrentToPembatalanPerKel($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "INSERT INTO cppmod_pbb_sppt_current_pembatalan 
				(
				 NOP
				,SPPT_TAHUN_PAJAK
				,SPPT_TANGGAL_JATUH_TEMPO
				,SPPT_PBB_HARUS_DIBAYAR
				,WP_NAMA
				,WP_TELEPON
				,WP_NO_HP
				,WP_ALAMAT
				,WP_RT
				,WP_RW
				,WP_KELURAHAN
				,WP_KECAMATAN
				,WP_KOTAKAB
				,WP_KODEPOS
				,SPPT_TANGGAL_TERBIT
				,SPPT_TANGGAL_CETAK
				,OP_LUAS_BUMI
				,OP_LUAS_BANGUNAN
				,OP_KELAS_BUMI
				,OP_KELAS_BANGUNAN
				,OP_NJOP_BUMI
				,OP_NJOP_BANGUNAN
				,OP_LUAS_BUMI_BERSAMA
				,OP_LUAS_BANGUNAN_BERSAMA
				,OP_KELAS_BUMI_BERSAMA
				,OP_KELAS_BANGUNAN_BERSAMA
				,OP_NJOP_BUMI_BERSAMA
				,OP_NJOP_BANGUNAN_BERSAMA
				,OP_NJOP
				,OP_NJOPTKP
				,OP_NJKP
				,PBB_COLLECTIBLE
				,OP_ALAMAT
				,OP_RT
				,OP_RW
				,OP_KELURAHAN
				,OP_KECAMATAN
				,OP_KOTAKAB
				,OP_KELURAHAN_KODE
				,OP_KECAMATAN_KODE
				,OP_KOTAKAB_KODE
				,OP_PROVINSI_KODE
				,FLAG
				,OP_KELAS
				,SPPT_PBB_PENGURANGAN
				,SPPT_PBB_PERSEN_PENGURANGAN
				,OP_TARIF
				,SPPT_DOC_ID
				)

				SELECT
					A.NOP
					,A.SPPT_TAHUN_PAJAK
					,A.SPPT_TANGGAL_JATUH_TEMPO
					,A.SPPT_PBB_HARUS_DIBAYAR
					,A.WP_NAMA
					,A.WP_TELEPON
					,A.WP_NO_HP
					,A.WP_ALAMAT
					,A.WP_RT
					,A.WP_RW
					,A.WP_KELURAHAN
					,A.WP_KECAMATAN
					,A.WP_KOTAKAB
					,A.WP_KODEPOS
					,A.SPPT_TANGGAL_TERBIT
					,A.SPPT_TANGGAL_CETAK
					,A.OP_LUAS_BUMI
					,A.OP_LUAS_BANGUNAN
					,A.OP_KELAS_BUMI
					,A.OP_KELAS_BANGUNAN
					,A.OP_NJOP_BUMI
					,A.OP_NJOP_BANGUNAN
					,A.OP_LUAS_BUMI_BERSAMA
					,A.OP_LUAS_BANGUNAN_BERSAMA
					,A.OP_KELAS_BUMI_BERSAMA
					,A.OP_KELAS_BANGUNAN_BERSAMA
					,A.OP_NJOP_BUMI_BERSAMA
					,A.OP_NJOP_BANGUNAN_BERSAMA
					,A.OP_NJOP
					,A.OP_NJOPTKP
					,A.OP_NJKP
					,A.PBB_COLLECTIBLE
					,A.OP_ALAMAT
					,A.OP_RT
					,A.OP_RW
					,A.OP_KELURAHAN
					,A.OP_KECAMATAN
					,A.OP_KOTAKAB
					,A.OP_KELURAHAN_KODE
					,A.OP_KECAMATAN_KODE
					,A.OP_KOTAKAB_KODE
					,A.OP_PROVINSI_KODE
					,A.FLAG
					,A.OP_KELAS
					,A.SPPT_PBB_PENGURANGAN
					,A.SPPT_PBB_PERSEN_PENGURANGAN
					,A.OP_TARIF
					,A.SPPT_DOC_ID
				FROM
					cppmod_pbb_sppt_current A
				LEFT JOIN ".$this->C_DB.".PBB_SPPT B ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK
				WHERE A.NOP LIKE '$nop%' AND A.SPPT_TAHUN_PAJAK = '$tahun'
				AND (B.PAYMENT_FLAG != '1' OR B.PAYMENT_FLAG IS NULL)";
		 // echo $query; exit;
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function replaceSPPTCurrentToPembatalanPerKel($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "REPLACE INTO  cppmod_pbb_sppt_current_pembatalan  
				(
				 NOP
				,SPPT_TAHUN_PAJAK
				,SPPT_TANGGAL_JATUH_TEMPO
				,SPPT_PBB_HARUS_DIBAYAR
				,WP_NAMA
				,WP_TELEPON
				,WP_NO_HP
				,WP_ALAMAT
				,WP_RT
				,WP_RW
				,WP_KELURAHAN
				,WP_KECAMATAN
				,WP_KOTAKAB
				,WP_KODEPOS
				,SPPT_TANGGAL_TERBIT
				,SPPT_TANGGAL_CETAK
				,OP_LUAS_BUMI
				,OP_LUAS_BANGUNAN
				,OP_KELAS_BUMI
				,OP_KELAS_BANGUNAN
				,OP_NJOP_BUMI
				,OP_NJOP_BANGUNAN
				,OP_LUAS_BUMI_BERSAMA
				,OP_LUAS_BANGUNAN_BERSAMA
				,OP_KELAS_BUMI_BERSAMA
				,OP_KELAS_BANGUNAN_BERSAMA
				,OP_NJOP_BUMI_BERSAMA
				,OP_NJOP_BANGUNAN_BERSAMA
				,OP_NJOP
				,OP_NJOPTKP
				,OP_NJKP
				,PBB_COLLECTIBLE
				,OP_ALAMAT
				,OP_RT
				,OP_RW
				,OP_KELURAHAN
				,OP_KECAMATAN
				,OP_KOTAKAB
				,OP_KELURAHAN_KODE
				,OP_KECAMATAN_KODE
				,OP_KOTAKAB_KODE
				,OP_PROVINSI_KODE
				,FLAG
				,OP_KELAS
				,SPPT_PBB_PENGURANGAN
				,SPPT_PBB_PERSEN_PENGURANGAN
				,OP_TARIF
				,SPPT_DOC_ID
				)

				SELECT
					A.NOP
					,A.SPPT_TAHUN_PAJAK
					,A.SPPT_TANGGAL_JATUH_TEMPO
					,A.SPPT_PBB_HARUS_DIBAYAR
					,A.WP_NAMA
					,A.WP_TELEPON
					,A.WP_NO_HP
					,A.WP_ALAMAT
					,A.WP_RT
					,A.WP_RW
					,A.WP_KELURAHAN
					,A.WP_KECAMATAN
					,A.WP_KOTAKAB
					,A.WP_KODEPOS
					,A.SPPT_TANGGAL_TERBIT
					,A.SPPT_TANGGAL_CETAK
					,A.OP_LUAS_BUMI
					,A.OP_LUAS_BANGUNAN
					,A.OP_KELAS_BUMI
					,A.OP_KELAS_BANGUNAN
					,A.OP_NJOP_BUMI
					,A.OP_NJOP_BANGUNAN
					,A.OP_LUAS_BUMI_BERSAMA
					,A.OP_LUAS_BANGUNAN_BERSAMA
					,A.OP_KELAS_BUMI_BERSAMA
					,A.OP_KELAS_BANGUNAN_BERSAMA
					,A.OP_NJOP_BUMI_BERSAMA
					,A.OP_NJOP_BANGUNAN_BERSAMA
					,A.OP_NJOP
					,A.OP_NJOPTKP
					,A.OP_NJKP
					,A.PBB_COLLECTIBLE
					,A.OP_ALAMAT
					,A.OP_RT
					,A.OP_RW
					,A.OP_KELURAHAN
					,A.OP_KECAMATAN
					,A.OP_KOTAKAB
					,A.OP_KELURAHAN_KODE
					,A.OP_KECAMATAN_KODE
					,A.OP_KOTAKAB_KODE
					,A.OP_PROVINSI_KODE
					,A.FLAG
					,A.OP_KELAS
					,A.SPPT_PBB_PENGURANGAN
					,A.SPPT_PBB_PERSEN_PENGURANGAN
					,A.OP_TARIF
					,A.SPPT_DOC_ID
				FROM
					cppmod_pbb_sppt_current A
				LEFT JOIN ".$this->C_DB.".PBB_SPPT B ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK
				WHERE A.NOP LIKE '$nop%' AND A.SPPT_TAHUN_PAJAK = '$tahun'
				AND (B.PAYMENT_FLAG != '1' OR B.PAYMENT_FLAG IS NULL)";
		 //echo $query; exit;
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function deleteSPPTCurrentPerKel($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "DELETE A
					FROM
						cppmod_pbb_sppt_current A
					LEFT JOIN ".$this->C_DB.".PBB_SPPT B ON A.NOP = B.NOP
					AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK 
					WHERE A.NOP LIKE '$nop%' AND A.SPPT_TAHUN_PAJAK = '$tahun'
					AND (B.PAYMENT_FLAG != '1' OR B.PAYMENT_FLAG IS NULL)";
		// echo $query; exit;
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function isCurrentExistPerKel($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "SELECT NOP FROM cppmod_pbb_sppt_current WHERE NOP LIKE '$nop%' AND SPPT_TAHUN_PAJAK = '$tahun'";
        
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
	}
	
	public function updateTahunPenetapan($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		// $query = "UPDATE cppmod_pbb_sppt_final SET CPM_SPPT_THN_PENETAPAN = '0' WHERE CPM_NOP LIKE '$nop%' ";
		$query = "UPDATE
					cppmod_pbb_sppt_final A
				JOIN ".$this->C_DB.".PBB_SPPT B ON A.CPM_NOP = B.NOP
				SET CPM_SPPT_THN_PENETAPAN = '0'
				WHERE
					B.SPPT_TAHUN_PAJAK = '$tahun'
				AND (
					B.PAYMENT_FLAG != '1'
					OR B.PAYMENT_FLAG IS NULL
				)
				AND CPM_NOP LIKE '$nop%' ";
        // echo $query; exit;
         $result=$this->dbSpec->sqlQuery($query);
		if(!$result){
			// $query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_SPPT_THN_PENETAPAN = '0' WHERE CPM_NOP LIKE '$nop%' ";	
			$query = "UPDATE
					cppmod_pbb_sppt_susulan A
				JOIN ".$this->C_DB.".PBB_SPPT B ON A.CPM_NOP = B.NOP
				SET CPM_SPPT_THN_PENETAPAN = '0'
				WHERE
					B.SPPT_TAHUN_PAJAK = '$tahun'
				AND (
					B.PAYMENT_FLAG != '1'
					OR B.PAYMENT_FLAG IS NULL
				)
				AND CPM_NOP LIKE '$nop%' ";	
			$result=$this->dbSpec->sqlQuery($query);
		}
		return $result;
	}
	
	//===================================
	
	public function copyToPembatalan($nop,$tahun) {
    	$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
				
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "REPLACE 
		INTO PBB_SPPT_PEMBATALAN 
		(
					NOP
					,SPPT_TAHUN_PAJAK
					,SPPT_TANGGAL_JATUH_TEMPO
					,SPPT_PBB_HARUS_DIBAYAR
					,WP_NAMA
					,WP_TELEPON
					,WP_NO_HP
					,WP_ALAMAT
					,WP_RT
					,WP_RW
					,WP_KELURAHAN
					,WP_KECAMATAN
					,WP_KOTAKAB
					,WP_KODEPOS
					,SPPT_TANGGAL_TERBIT
					,SPPT_TANGGAL_CETAK
					,OP_LUAS_BUMI
					,OP_LUAS_BANGUNAN
					,OP_KELAS_BUMI
					,OP_KELAS_BANGUNAN
					,OP_NJOP_BUMI
					,OP_NJOP_BANGUNAN
					,OP_NJOP
					,OP_NJOPTKP
					,OP_NJKP
					,PAYMENT_FLAG
					,PAYMENT_PAID
					,PAYMENT_REF_NUMBER
					,PAYMENT_BANK_CODE
					,PAYMENT_SW_REFNUM
					,PAYMENT_GW_REFNUM
					,PAYMENT_SW_ID
					,PAYMENT_MERCHANT_CODE
					,PAYMENT_SETTLEMENT_DATE
					,PBB_COLLECTIBLE
					,PBB_DENDA
					,PBB_ADMIN_GW
					,PBB_MISC_FEE
					,PBB_TOTAL_BAYAR
					,OP_ALAMAT
					,OP_RT
					,OP_RW
					,OP_KELURAHAN
					,OP_KECAMATAN
					,OP_KOTAKAB
					,OP_KELURAHAN_KODE
					,OP_KECAMATAN_KODE
					,OP_KOTAKAB_KODE
					,OP_PROVINSI_KODE
					,TGL_STPD
					,TGL_SP1
					,TGL_SP2
					,TGL_SP3
					,STATUS_SP
					,STATUS_CETAK
					,WP_PEKERJAAN
					,PAYMENT_OFFLINE_USER_ID
					,ID_WP
					
					
				)
		SELECT 
		NOP
		,SPPT_TAHUN_PAJAK
		,SPPT_TANGGAL_JATUH_TEMPO
		,SPPT_PBB_HARUS_DIBAYAR
		,WP_NAMA
		,WP_TELEPON
		,WP_NO_HP
		,WP_ALAMAT
		,WP_RT
		,WP_RW
		,WP_KELURAHAN
		,WP_KECAMATAN
		,WP_KOTAKAB
		,WP_KODEPOS
		,SPPT_TANGGAL_TERBIT
		,SPPT_TANGGAL_CETAK
		,OP_LUAS_BUMI
		,OP_LUAS_BANGUNAN
		,OP_KELAS_BUMI
		,OP_KELAS_BANGUNAN
		,OP_NJOP_BUMI
		,OP_NJOP_BANGUNAN
		,OP_NJOP
		,OP_NJOPTKP
		,OP_NJKP
		,PAYMENT_FLAG
		,PAYMENT_PAID
		,PAYMENT_REF_NUMBER
		,PAYMENT_BANK_CODE
		,PAYMENT_SW_REFNUM
		,PAYMENT_GW_REFNUM
		,PAYMENT_SW_ID
		,PAYMENT_MERCHANT_CODE
		,PAYMENT_SETTLEMENT_DATE
		,PBB_COLLECTIBLE
		,PBB_DENDA
		,PBB_ADMIN_GW
		,PBB_MISC_FEE
		,PBB_TOTAL_BAYAR
		,OP_ALAMAT
		,OP_RT
		,OP_RW
		,OP_KELURAHAN
		,OP_KECAMATAN
		,OP_KOTAKAB
		,OP_KELURAHAN_KODE
		,OP_KECAMATAN_KODE
		,OP_KOTAKAB_KODE
		,OP_PROVINSI_KODE
		,TGL_STPD
		,TGL_SP1
		,TGL_SP2
		,TGL_SP3
		,STATUS_SP
		,STATUS_CETAK
		,WP_PEKERJAAN
		,PAYMENT_OFFLINE_USER_ID
		,ID_WP
		
		
		FROM PBB_SPPT WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun' ";

		// echo $query;exit;		
		$result = mysqli_query($LDBLink, $query);
		// echo $query;
		// exit;
		// var_dump($result);
		// ex
                
        if (!$result) {
            return false;
        }else{	
  //       $query_update = "UPDATE  PBB_SPPT_PEMBATALAN 
		// SET 
		// 	no_sk = '$no_sk' ,
		// 	alasan = '$alasan',
		// 	TGL_PEMBATALAN = now(),
		// 	USER_PEMBATALAN = '$uname'
		// WHERE NOP LIKE '$nop%' 
		// AND SPPT_TAHUN_PAJAK = '$tahun' 
		// AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ";
		// $eksekusi_update = mysql_query($query_update);       
		// if ($eksekusi_update)
		// 	return true;
		// else
		// 	return false;
        return true;
        }
    }
	
	public function addToLog($user,$nop,$tahun, $no_sk = '', $alasan = '') {
		$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
		
		$user   = mysqli_real_escape_string($LDBLink, trim($user));		
		$nop   	= mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun 	= mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "INSERT INTO PBB_PEMBATALAN_SPPT_LOG VALUES (UUID(),'$user','$nop','$tahun',now(), '{$no_sk}', '{$alasan}') ";

		// echo $query;exit;		
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        }
        return true;
    }
	
	public function delGateWayPBBSPPT($nop,$tahun) {
		$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
				
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "DELETE FROM PBB_SPPT WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun'";

		//echo $query;exit;		
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        }
        return true;
    }

 //    public function copySPPTCurrentToPembatalan($nop,$tahun,$tahun_tagihan) {
	// 	$nop = mysql_real_escape_string(trim($nop));
	// 	if (intval($tahun_tagihan)==intval($tahun) ){
	// 		$table = "cppmod_pbb_sppt_current";
	// 	}else{
	// 		$table = "cppmod_pbb_sppt_cetak_".$tahun;
	// 	}
	// 	$query = "REPLACE INTO cppmod_pbb_sppt_current_pembatalan SELECT * FROM $table WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
	// 	// echo $query; exit;
	// 	return $this->dbSpec->sqlQuery($query);
	// }
	
	public function copySPPTCurrentToPembatalan($nop,$tahun,$tahun_tagihan) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

		if (intval($tahun_tagihan)==intval($tahun) ){
			$table = "cppmod_pbb_sppt_current";
		}else{
			$table = "cppmod_pbb_sppt_cetak_".$tahun;
		}
		
		$query = "REPLACE INTO cppmod_pbb_sppt_current_pembatalan
			(
				NOP
				,SPPT_TAHUN_PAJAK
				,SPPT_TANGGAL_JATUH_TEMPO
				,SPPT_PBB_HARUS_DIBAYAR
				,WP_NAMA
				,WP_TELEPON
				,WP_NO_HP
				,WP_ALAMAT
				,WP_RT
				,WP_RW
				,WP_KELURAHAN
				,WP_KECAMATAN
				,WP_KOTAKAB
				,WP_KODEPOS
				,SPPT_TANGGAL_TERBIT
				,SPPT_TANGGAL_CETAK
				,OP_LUAS_BUMI
				,OP_LUAS_BANGUNAN
				,OP_KELAS_BUMI
				,OP_KELAS_BANGUNAN
				,OP_NJOP_BUMI
				,OP_NJOP_BANGUNAN
				,OP_LUAS_BUMI_BERSAMA
				,OP_LUAS_BANGUNAN_BERSAMA
				,OP_KELAS_BUMI_BERSAMA
				,OP_KELAS_BANGUNAN_BERSAMA
				,OP_NJOP_BUMI_BERSAMA
				,OP_NJOP_BANGUNAN_BERSAMA
				,OP_NJOP
				,OP_NJOPTKP
				,OP_NJKP
				,PBB_COLLECTIBLE
				,OP_ALAMAT
				,OP_RT
				,OP_RW
				,OP_KELURAHAN
				,OP_KECAMATAN
				,OP_KOTAKAB
				,OP_KELURAHAN_KODE
				,OP_KECAMATAN_KODE
				,OP_KOTAKAB_KODE
				,OP_PROVINSI_KODE
				,FLAG
				,OP_KELAS
				,SPPT_PBB_PENGURANGAN
				,SPPT_PBB_PERSEN_PENGURANGAN
				,OP_TARIF
				,SPPT_DOC_ID
				)
		 SELECT 
		 		NOP
				,SPPT_TAHUN_PAJAK
				,SPPT_TANGGAL_JATUH_TEMPO
				,SPPT_PBB_HARUS_DIBAYAR
				,WP_NAMA
				,WP_TELEPON
				,WP_NO_HP
				,WP_ALAMAT
				,WP_RT
				,WP_RW
				,WP_KELURAHAN
				,WP_KECAMATAN
				,WP_KOTAKAB
				,WP_KODEPOS
				,SPPT_TANGGAL_TERBIT
				,SPPT_TANGGAL_CETAK
				,OP_LUAS_BUMI
				,OP_LUAS_BANGUNAN
				,OP_KELAS_BUMI
				,OP_KELAS_BANGUNAN
				,OP_NJOP_BUMI
				,OP_NJOP_BANGUNAN
				,OP_LUAS_BUMI_BERSAMA
				,OP_LUAS_BANGUNAN_BERSAMA
				,OP_KELAS_BUMI_BERSAMA
				,OP_KELAS_BANGUNAN_BERSAMA
				,OP_NJOP_BUMI_BERSAMA
				,OP_NJOP_BANGUNAN_BERSAMA
				,OP_NJOP
				,OP_NJOPTKP
				,OP_NJKP
				,PBB_COLLECTIBLE
				,OP_ALAMAT
				,OP_RT
				,OP_RW
				,OP_KELURAHAN
				,OP_KECAMATAN
				,OP_KOTAKAB
				,OP_KELURAHAN_KODE
				,OP_KECAMATAN_KODE
				,OP_KOTAKAB_KODE
				,OP_PROVINSI_KODE
				,FLAG
				,OP_KELAS
				,SPPT_PBB_PENGURANGAN
				,SPPT_PBB_PERSEN_PENGURANGAN
				,OP_TARIF
				,SPPT_DOC_ID
		 FROM $table WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
		// echo $query; exit;


		// if ($this->dbSpec->sqlQuery($query) ){
		// $query = "UPDATE cppmod_pbb_sppt_current_pembatalan 
		// 		  SET 
		// 		  NO_SK = '$no_sk',
		// 		  ALASAN = '$alasan',
		// 		  TGL_PEMBATALAN = now(),
		// 		  USER_PEMBATALAN = '$uname'  

		// 		  WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'
		// 		  ";
		// }
		return $this->dbSpec->sqlQuery($query);
	}
		
	public function deleteSPPTCurrent($nop,$tahun,$tahun_tagihan) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));

		if (intval($tahun_tagihan)==intval($tahun) ){
			$table = "cppmod_pbb_sppt_current";
		}else{
			$table = "cppmod_pbb_sppt_cetak_".$tahun;
		}
		$query = "DELETE FROM $table WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
		
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function isCurrentExist($nop,$tahun,$tahun_tagihan) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		if (intval($tahun_tagihan)==intval($tahun) ){
			$table = "cppmod_pbb_sppt_current";
		}else{
			$table = "cppmod_pbb_sppt_cetak_".$tahun;
		}

		$query = "SELECT NOP FROM $table WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
        
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
	}
	
	#PENERBITAN ======================================================
	public function getGateWayPBBSPPTPembatalan($nop) {
		$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
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
				FROM pbb_sppt_pembatalan 
				WHERE NOP='$nop' ";
		// echo $query;exit;
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        } else 
			return $result;
    }
	
	public function copyToPBBSPPT($nop,$tahun) {
		$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
				
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "INSERT INTO pbb_sppt (
					 NOP
					,SPPT_TAHUN_PAJAK
					,SPPT_TANGGAL_JATUH_TEMPO
					,SPPT_PBB_HARUS_DIBAYAR
					,WP_NAMA
					,WP_TELEPON
					,WP_NO_HP
					,WP_ALAMAT
					,WP_RT
					,WP_RW
					,WP_KELURAHAN
					,WP_KECAMATAN
					,WP_KOTAKAB
					,WP_KODEPOS
					,SPPT_TANGGAL_TERBIT
					,SPPT_TANGGAL_CETAK
					,OP_LUAS_BUMI
					,OP_LUAS_BANGUNAN
					,OP_KELAS_BUMI
					,OP_KELAS_BANGUNAN
					,OP_NJOP_BUMI
					,OP_NJOP_BANGUNAN
					,OP_NJOP
					,OP_NJOPTKP
					,OP_NJKP
					,PAYMENT_FLAG
					,PAYMENT_PAID
					,PAYMENT_REF_NUMBER
					,PAYMENT_BANK_CODE
					,PAYMENT_SW_REFNUM
					,PAYMENT_GW_REFNUM
					,PAYMENT_SW_ID
					,PAYMENT_MERCHANT_CODE
					,PAYMENT_SETTLEMENT_DATE
					,PBB_COLLECTIBLE
					,PBB_DENDA
					,PBB_ADMIN_GW
					,PBB_MISC_FEE
					,PBB_TOTAL_BAYAR
					,OP_ALAMAT
					,OP_RT
					,OP_RW
					,OP_KELURAHAN
					,OP_KECAMATAN
					,OP_KOTAKAB
					,OP_KELURAHAN_KODE
					,OP_KECAMATAN_KODE
					,OP_KOTAKAB_KODE
					,OP_PROVINSI_KODE
					,TGL_STPD
					,TGL_SP1
					,TGL_SP2
					,TGL_SP3
					,STATUS_SP
					,STATUS_CETAK
					,WP_PEKERJAAN
					,PAYMENT_OFFLINE_USER_ID
					,ID_WP
					
					
				) SELECT 
					NOP
					,SPPT_TAHUN_PAJAK
					,SPPT_TANGGAL_JATUH_TEMPO
					,SPPT_PBB_HARUS_DIBAYAR
					,WP_NAMA
					,WP_TELEPON
					,WP_NO_HP
					,WP_ALAMAT
					,WP_RT
					,WP_RW
					,WP_KELURAHAN
					,WP_KECAMATAN
					,WP_KOTAKAB
					,WP_KODEPOS
					,SPPT_TANGGAL_TERBIT
					,SPPT_TANGGAL_CETAK
					,OP_LUAS_BUMI
					,OP_LUAS_BANGUNAN
					,OP_KELAS_BUMI
					,OP_KELAS_BANGUNAN
					,OP_NJOP_BUMI
					,OP_NJOP_BANGUNAN
					,OP_NJOP
					,OP_NJOPTKP
					,OP_NJKP
					,IF(SPPT_PBB_HARUS_DIBAYAR <= 0, 1, PAYMENT_FLAG) AS PAYMENT_FLAG
					,PAYMENT_PAID
					,PAYMENT_REF_NUMBER
					,PAYMENT_BANK_CODE
					,PAYMENT_SW_REFNUM
					,PAYMENT_GW_REFNUM
					,PAYMENT_SW_ID
					,PAYMENT_MERCHANT_CODE
					,PAYMENT_SETTLEMENT_DATE
					,PBB_COLLECTIBLE
					,PBB_DENDA
					,PBB_ADMIN_GW
					,PBB_MISC_FEE
					,PBB_TOTAL_BAYAR
					,OP_ALAMAT
					,OP_RT
					,OP_RW
					,OP_KELURAHAN
					,OP_KECAMATAN
					,OP_KOTAKAB
					,OP_KELURAHAN_KODE
					,OP_KECAMATAN_KODE
					,OP_KOTAKAB_KODE
					,OP_PROVINSI_KODE
					,TGL_STPD
					,TGL_SP1
					,TGL_SP2
					,TGL_SP3
					,STATUS_SP
					,STATUS_CETAK
					,WP_PEKERJAAN
					,PAYMENT_OFFLINE_USER_ID
					,ID_WP
					
					
				 FROM pbb_sppt_pembatalan WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun' ";

		// echo $query;exit;		
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        }
        return true;
    }
	
	public function addToLogPenerbitan($user,$nop,$tahun,$alasan = null) {
		$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
		
		$user   = mysqli_real_escape_string($LDBLink, trim($user));		
		$nop   	= mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun 	= mysqli_real_escape_string($LDBLink, trim($tahun));
		$alasan 	= mysqli_real_escape_string($LDBLink, trim($alasan));
		
		$query = "INSERT INTO PBB_PENERBITAN_SPPT_LOG VALUES (UUID(),'$user','$nop','$tahun',now(), '$alasan') ";

		// echo $query;exit;		
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        }
        return true;
    }
	
	public function delGateWayPBBSPPTPembatalan($nop,$tahun) {
		$LDBLink =mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
				
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "DELETE FROM pbb_sppt_pembatalan WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun'";

		//echo $query;exit;		
		$result = mysqli_query($LDBLink, $query);
                
        if (!$result) {
            return false;
        }
        return true;
    }
	
	// public function copyPembatalanToSPPTCurrent($nop,$tahun,$tahun_tagihan) {
	// 	$nop = mysql_real_escape_string(trim($nop));

	// 	if (intval($tahun_tagihan)==intval($tahun) ){
	// 		$table = "cppmod_pbb_sppt_current";
	// 	}else{
	// 		$table = "cppmod_pbb_sppt_cetak_".$tahun;
	// 	}
		
	// 	$query = "REPLACE INTO $table SELECT * FROM cppmod_pbb_sppt_current_pembatalan WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
	// 	// echo $query; exit;
	// 	return $this->dbSpec->sqlQuery($query);
	// }
	public function copyPembatalanToSPPTCurrent($nop,$tahun,$tahun_tagihan) {
		// var_dump($tahun);
		// var_dump($tahun_tagihan);
		// exit;
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		if (intval($tahun_tagihan)==intval($tahun) ){
			$table = "cppmod_pbb_sppt_current";
		}else{
			$table = "cppmod_pbb_sppt_cetak_".$tahun;
		}


		// exit;
		
		$query = "REPLACE INTO $table (
				 NOP
				,SPPT_TAHUN_PAJAK
				,SPPT_TANGGAL_JATUH_TEMPO
				,SPPT_PBB_HARUS_DIBAYAR
				,WP_NAMA
				,WP_TELEPON
				,WP_NO_HP
				,WP_ALAMAT
				,WP_RT
				,WP_RW
				,WP_KELURAHAN
				,WP_KECAMATAN
				,WP_KOTAKAB
				,WP_KODEPOS
				,SPPT_TANGGAL_TERBIT
				,SPPT_TANGGAL_CETAK
				,OP_LUAS_BUMI
				,OP_LUAS_BANGUNAN
				,OP_KELAS_BUMI
				,OP_KELAS_BANGUNAN
				,OP_NJOP_BUMI
				,OP_NJOP_BANGUNAN
				,OP_LUAS_BUMI_BERSAMA
				,OP_LUAS_BANGUNAN_BERSAMA
				,OP_KELAS_BUMI_BERSAMA
				,OP_KELAS_BANGUNAN_BERSAMA
				,OP_NJOP_BUMI_BERSAMA
				,OP_NJOP_BANGUNAN_BERSAMA
				,OP_NJOP
				,OP_NJOPTKP
				,OP_NJKP
				,PBB_COLLECTIBLE
				,OP_ALAMAT
				,OP_RT
				,OP_RW
				,OP_KELURAHAN
				,OP_KECAMATAN
				,OP_KOTAKAB
				,OP_KELURAHAN_KODE
				,OP_KECAMATAN_KODE
				,OP_KOTAKAB_KODE
				,OP_PROVINSI_KODE
				,FLAG
				,OP_KELAS
				,SPPT_PBB_PENGURANGAN
				,SPPT_PBB_PERSEN_PENGURANGAN
				,OP_TARIF
				,SPPT_DOC_ID
				) SELECT 
				 NOP
				,SPPT_TAHUN_PAJAK
				,SPPT_TANGGAL_JATUH_TEMPO
				,SPPT_PBB_HARUS_DIBAYAR
				,WP_NAMA
				,WP_TELEPON
				,WP_NO_HP
				,WP_ALAMAT
				,WP_RT
				,WP_RW
				,WP_KELURAHAN
				,WP_KECAMATAN
				,WP_KOTAKAB
				,WP_KODEPOS
				,SPPT_TANGGAL_TERBIT
				,SPPT_TANGGAL_CETAK
				,OP_LUAS_BUMI
				,OP_LUAS_BANGUNAN
				,OP_KELAS_BUMI
				,OP_KELAS_BANGUNAN
				,OP_NJOP_BUMI
				,OP_NJOP_BANGUNAN
				,OP_LUAS_BUMI_BERSAMA
				,OP_LUAS_BANGUNAN_BERSAMA
				,OP_KELAS_BUMI_BERSAMA
				,OP_KELAS_BANGUNAN_BERSAMA
				,OP_NJOP_BUMI_BERSAMA
				,OP_NJOP_BANGUNAN_BERSAMA
				,OP_NJOP
				,OP_NJOPTKP
				,OP_NJKP
				,PBB_COLLECTIBLE
				,OP_ALAMAT
				,OP_RT
				,OP_RW
				,OP_KELURAHAN
				,OP_KECAMATAN
				,OP_KOTAKAB
				,OP_KELURAHAN_KODE
				,OP_KECAMATAN_KODE
				,OP_KOTAKAB_KODE
				,OP_PROVINSI_KODE
				,FLAG
				,OP_KELAS
				,SPPT_PBB_PENGURANGAN
				,SPPT_PBB_PERSEN_PENGURANGAN
				,OP_TARIF
				,SPPT_DOC_ID
				 FROM cppmod_pbb_sppt_current_pembatalan WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
		// echo $query; exit;
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function deleteSPPTCurrentPembatalan($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "DELETE FROM cppmod_pbb_sppt_current_pembatalan WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
		
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function isCurrentPembatalanExist($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "SELECT NOP FROM cppmod_pbb_sppt_current_pembatalan WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun'";
        
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
	}
	
	#===============================================
	
	public function getDataFinal($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        // echo $query;exit;	
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_fetch_assoc($res);
            return $nRes;
        }
	}
	
	public function isFinalExist($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "SELECT CPM_NOP FROM cppmod_pbb_sppt_final WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        // echo $query;exit;	
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
	}
	
	public function isFinalExtExist($id) {
		$id = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($id));
		
		$queryExt = "SELECT * FROM cppmod_pbb_sppt_ext_final WHERE CPM_SPPT_DOC_ID = '$id' ";
		if ($this->dbSpec->sqlQuery($queryExt, $res)) {
           $nRes = mysqli_num_rows($res);
           return ($nRes == 1);
        }
		
	}
	
	public function isSusulanExist($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "SELECT CPM_NOP FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
        // echo $query;exit;	
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
	}
	
	public function isSusulanExtExist($nop) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$queryExt = "SELECT * FROM cppmod_pbb_sppt_ext_susulan A JOIN cppmod_pbb_sppt_susulan B ON A.CPM_SPPT_DOC_ID = B.CPM_SPPT_DOC_ID AND A.CPM_SPPT_DOC_VERSION = B.CPM_SPPT_DOC_VERSION WHERE B.CPM_NOP = '".$nop."' ";
		if ($this->dbSpec->sqlQuery($queryExt, $res)) {
           $nRes = mysqli_num_rows($res);
           return ($nRes == 1);
        }
		
	}
	
	public function isPBBSPPTExist($nop) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "SELECT CPM_NOP FROM cppmod_pbb_sppt WHERE CPM_NOP = '$nop' ";
        // echo $query;exit;	
        if ($this->dbSpec->sqlQuery($query, $res)) {
            $nRes = mysqli_num_rows($res);
            return ($nRes == 1);
        }
	}
	
	public function isPBBSPPTExtExist($nop) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$queryExt = "SELECT * FROM cppmod_pbb_sppt_ext A JOIN cppmod_pbb_sppt B ON A.CPM_SPPT_DOC_ID = B.CPM_SPPT_DOC_ID AND A.CPM_SPPT_DOC_VERSION = B.CPM_SPPT_DOC_VERSION WHERE B.CPM_NOP = '".$nop."' ";
		if ($this->dbSpec->sqlQuery($queryExt, $res)) {
           $nRes = mysqli_num_rows($res);
           return ($nRes == 1);
        }
	}
	
	public function updateJenisTanah($nop,$tahun,$table,$ot) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		$table = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($table));
		
		$query = "UPDATE ".$table." SET CPM_OT_JENIS = '".$ot."' WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '".$tahun."'";
		// echo $query; 
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function updateJenisTanahFinal($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "UPDATE cppmod_pbb_sppt_final SET CPM_OT_JENIS = '4' WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
		// echo $query; 
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function updateJenisTanahSusulan($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_OT_JENIS = '4' WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
		
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function updateJenisTanahPBBSPPT($nop) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "UPDATE cppmod_pbb_sppt SET CPM_OT_JENIS = '4' WHERE CPM_NOP = '$nop'";
		
		return $this->dbSpec->sqlQuery($query);
	}
	public function updateJenisTanahFinalNO($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "UPDATE cppmod_pbb_sppt_final SET CPM_OT_JENIS = '5' WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
		// echo $query; 
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function updateJenisTanahSusulanNO($nop,$tahun) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_OT_JENIS = '5' WHERE CPM_NOP = '$nop' AND CPM_SPPT_THN_PENETAPAN = '$tahun'";
		
		return $this->dbSpec->sqlQuery($query);
	}
	
	public function updateJenisTanahPBBSPPTNO($nop) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "UPDATE cppmod_pbb_sppt SET CPM_OT_JENIS = '5' WHERE CPM_NOP = '$nop'";
		
		return $this->dbSpec->sqlQuery($query);
	}
}


?>