<?php
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan_khusus_2016', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);


if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}



$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] :1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;
$isViewData = @isset($_REQUEST['isViewData']) ? $_REQUEST['isViewData'] :0;
// echo $page;
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$s = $q->s;
$uname = $q->u;
$kepala = $q->kepala;
$nip = $q->nip;
$jabatan = $q->jabatan;
$kota = $q->kota;
$sp1 = $q->sp1;
$sp2 = $q->sp2;
$sp3 = $q->sp3;
$bank = $q->bank;
$perpage = $q->perpage;
$kodekota = $q->kodekota;
$isAdminPenagihan = $q->adm;
$lblkel = $q->lblkel;
$thn = $q->thn;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig = $User->GetModuleConfig($m);
$thnPenagihanKhusus = $arConfig['tahun'];
SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $q->dbhost, $q->dbuser, $q->dbpwd, $q->dbname);


//set new page
if(isset($_SESSION['stLaporan'])){
    if($_SESSION['stLaporan'] != $s){
        $_SESSION['stLaporan'] = $s;
        $find = "";
        $page = 1;
        $np = 1;
        echo "<script language=\"javascript\">page=1;</script>";
        $isViewData=0;
    }
}else{
    $_SESSION['stLaporan'] = $s;
}

$condition = explode("&", $find);

$modNotaris = new  SPTPDService($uname);
$modNotaris->setStatus($s);
$modNotaris->setDataPerPage($perpage);
$modNotaris->setDefaultPage(1);

$modNotaris->displayDataNotaris();


class SPTPDService{
        public $user;
        public $status;
        public $perpage = 0;
        public $totalRows = 0;
        public $defaultPage = 0;

	function __construct($user) {
		$this->user = $user;
	}

        function setDataPerPage($perpage){
            $this->perpage = $perpage;
        }

        function setDefaultPage($defaultPage){
            $this->defaultPage = $defaultPage;
        }

        function setStatus($status){
            $this->status = $status;
        }

	function mysql2json($mysql_result,$name){
		 $json="{\n'$name': [\n";
		 $field_names = array();
		 $fields = mysqli_num_fields($mysql_result);
		 for($x=0;$x<$fields;$x++){
			  $field_name = mysqli_fetch_field($mysql_result);
			  if($field_name){
				   $field_names[$x]=$field_name->name;
			  }
		 }
		 $rows = mysqli_num_rows($mysql_result);
		 for($x=0;$x<$rows;$x++){
			  $row = mysqli_fetch_array($mysql_result);
			  $json.="{\n";
			  for($y=0;$y<count($field_names);$y++) {
				   $json.="'$field_names[$y]' :	'".addslashes($row[$y])."'";
				   if($y==count($field_names)-1){
						$json.="\n";
				   }
				   else{
						$json.=",\n";
				   }
			  }
			  if($x==$rows-1){
				   $json.="\n}\n";
			  }
			  else{
				   $json.="\n},\n";
			  }
		 }
		 $json.="]\n}";
		 return($json);
	}

	function getTotalRows($query) {
		global $DBLinkLookUp;
		$res = mysqli_query($DBLinkLookUp, $query);
		if ( $res === false ){
			echo $query ."<br>";
			echo mysqli_error($DBLink);
		}

		$row = mysqli_fetch_array($res);
		return $row['TOTALROWS'];
	}

        function conditionBuilder(){
            global $condition;

            $condQuery = "";

            if($condition[0] != "") $condQuery .= " AND (A.WP_NAMA LIKE '%".$condition[0]."%') ";

            if($condition[1] != 0){
                switch ($condition[1]){
                    case 1 : $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR < 5000000) "; break;
                    case 2 : $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 5000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 10000000) "; break;
                    case 3 : $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 10000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 20000000) "; break;
                    case 4 : $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 20000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 30000000) "; break;
                    case 5 : $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 30000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 40000000) "; break;
                    case 6 : $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 40000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 50000000) "; break;
                    case 7 : $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 100000000) "; break;
                    case 8 : $condQuery .= " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 100000000) "; break;
                }
            }

            if($condition[3] != "") $condQuery .= " AND (A.NOP like'".$condition[3]."%') ";
            else if($condition[2] != "") $condQuery .= " AND (A.NOP like'".$condition[2]."%') ";
            
            if($condition[4] != "") $condQuery .= " AND (A.SPPT_TAHUN_PAJAK ='".$condition[4]."') ";
            if($condition[5] != "") $condQuery .= " AND (A.NOP like'%".$condition[5]."%') ";

            return $condQuery;
        }
		
		function conditionBuilderNotView(){
            global $condition;

            $condQuery = "";


            if($condition[0] != "") $condQuery .= " AND (C.WP_NAMA LIKE '%".$condition[0]."%') ";

            if($condition[1] != 0){
                switch ($condition[1]){
                    case 1 : $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR < 5000000) "; break;
                    case 2 : $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 5000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 10000000) "; break;
                    case 3 : $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 10000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 20000000) "; break;
                    case 4 : $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 20000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 30000000) "; break;
                    case 5 : $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 30000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 40000000) "; break;
                    case 6 : $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 40000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 50000000) "; break;
                    case 7 : $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND C.SPPT_PBB_HARUS_DIBAYAR < 100000000) "; break;
                    case 8 : $condQuery .= " AND (C.SPPT_PBB_HARUS_DIBAYAR >= 100000000) "; break;
                }
            }

            if($condition[3] != "") $condQuery .= " AND (C.NOP like'".$condition[3]."%') ";
            else if($condition[2] != "") $condQuery .= " AND (C.NOP like'".$condition[2]."%') ";
            
            if($condition[4] != "") $condQuery .= " AND (C.SPPT_TAHUN_PAJAK ='".$condition[4]."') ";
            if($condition[5] != "") $condQuery .= " AND (C.NOP like'%".$condition[5]."%') ";

            return $condQuery;
        }

	function getDocument($sts,&$dat) {
		global $DBLinkLookUp, $DBLink, $sRootPath,$json,$a,$m,$page, $sp1, $sp2, $sp3, $isAdminPenagihan,$thnPenagihanKhusus;
		
		// echo $sts; exit;
		if($sts==1)
			$where = $this->conditionBuilder();
		else 
			$where = $this->conditionBuilderNotView();
		$sp = "";
                
		$query ="";
		$hal = (($page-1) > 0 ? ($page-1) : 0) * $this->perpage;
				$fieldKetetapan = "";
                switch ($sts){
                        case 2 : $sp .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NULL OR A.TGL_SP2 = '') AND A.STATUS_SP = 1 AND A.STATUS_PERSETUJUAN = 1";
								 break;
                        case 3 : $sp .= "AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '') AND A.STATUS_PERSETUJUAN = 2 ";
								 break;
                        case 6 : $sp .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NULL OR A.TGL_SP2 = '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '') AND A.STATUS_SP <> 1 AND A.TAHUN_SP1 = '{$thnPenagihanKhusus}' ";
								 break;
                        case 7 : $sp .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '') AND A.STATUS_SP <> 1 AND A.TAHUN_SP1 = '{$thnPenagihanKhusus}' ";
								 break;
                        case 8 : $sp .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NOT NULL OR A.TGL_SP3 <> '') AND A.STATUS_SP <> 1 AND A.TAHUN_SP1 = '{$thnPenagihanKhusus}' ";
								 break;
                        case 9 : $sp .= "AND A.STATUS_SP = 1";
								 break;
                }

                if($sts==1){ //Tab SP1
                        //$query = "SELECT * FROM VIEW_SP1_GROUPED WHERE SPPT_TAHUN_PAJAK >= '2007' $where LIMIT ".$hal.",".$this->perpage;
                        $query = "SELECT
                                        A.`NOP` AS `NOP`,
                                        A.`SPPT_TAHUN_PAJAK` AS `SPPT_TAHUN_PAJAK`,
                                        A.`WP_NAMA` AS `WP_NAMA`,
                                        A.`WP_ALAMAT` AS `WP_ALAMAT`,
                                        A.`WP_KELURAHAN` AS `WP_KELURAHAN`,
                                        A.`OP_ALAMAT` AS `OP_ALAMAT`,
                                        A.`OP_KECAMATAN` AS `OP_KECAMATAN`,
                                        A.`OP_KELURAHAN` AS `OP_KELURAHAN`,
                                        A.`OP_RT` AS `OP_RT`,
                                        A.`OP_RW` AS `OP_RW`,
                                        A.`OP_LUAS_BUMI` AS `OP_LUAS_BUMI`,
                                        A.`OP_LUAS_BANGUNAN` AS `OP_LUAS_BANGUNAN`,
                                        A.`OP_NJOP_BUMI` AS `OP_NJOP_BUMI`,
                                        A.`OP_NJOP_BANGUNAN` AS `OP_NJOP_BANGUNAN`,
                                        A.`SPPT_TANGGAL_JATUH_TEMPO` AS `SPPT_TANGGAL_JATUH_TEMPO`,
                                        A.`SPPT_PBB_HARUS_DIBAYAR` AS `SPPT_PBB_HARUS_DIBAYAR`
                                FROM
                                        PBB_SPPT A
                                LEFT JOIN PBB_SPPT_PENAGIHAN_KHUSUS B ON A.NOP = B.NOP
                                WHERE
                                        (
                                                A.PAYMENT_FLAG != '1'
                                                OR A.PAYMENT_FLAG IS NULL
                                        )
                                AND A.SPPT_TAHUN_PAJAK = '$thnPenagihanKhusus' AND B.NOP IS NULL AND NOW() > SPPT_TANGGAL_JATUH_TEMPO $where ORDER BY A.SPPT_PBB_HARUS_DIBAYAR DESC,A.NOP LIMIT ".$hal.",".$this->perpage;
                        // echo $query; exit;
                        $qry = "SELECT COUNT(*) AS TOTALROWS FROM
                                        PBB_SPPT A
                                LEFT JOIN PBB_SPPT_PENAGIHAN_KHUSUS B ON A.NOP = B.NOP
                                WHERE
                                        (
                                                A.PAYMENT_FLAG != '1'
                                                OR A.PAYMENT_FLAG IS NULL
                                        )
                                AND A.SPPT_TAHUN_PAJAK = '$thnPenagihanKhusus' AND B.NOP IS NULL AND NOW() > SPPT_TANGGAL_JATUH_TEMPO $where ORDER BY A.SPPT_PBB_HARUS_DIBAYAR DESC,A.NOP ";

                } else{
                        $query = "SELECT * FROM
                                 (SELECT
                                    A.NOP,
                                    C.WP_NAMA,
                                    C.WP_ALAMAT,
                                    C.WP_KELURAHAN,
                                    C.OP_ALAMAT,
                                    C.OP_KECAMATAN,
                                    C.OP_KELURAHAN,
                                    C.OP_RT,
                                    C.OP_RW,
                                    C.OP_LUAS_BUMI,
                                    C.OP_LUAS_BANGUNAN,
                                    C.OP_NJOP_BUMI,
                                    C.OP_NJOP_BANGUNAN,
                                    C.SPPT_TANGGAL_JATUH_TEMPO,
                                    C.PAYMENT_FLAG,
                                    C.SPPT_TAHUN_PAJAK,
                                    C.SPPT_PBB_HARUS_DIBAYAR,
                                    A.TAHUN_SP1,
                                    A.STATUS_SP,
                                    A.TGL_SP1,
                                    A.TGL_SP2,
                                    A.TGL_SP3,
									A.KETETAPAN_SP1,
									A.KETETAPAN_SP2,
									A.KETETAPAN_SP3,
                                    A.KETERANGAN_SP1,
                                    A.KETERANGAN_SP2,
                                    A.KETERANGAN_SP3,
                                    A.STATUS_PERSETUJUAN
                            FROM
                                    PBB_SPPT_PENAGIHAN_KHUSUS A
                            JOIN PBB_SPPT C
                            WHERE
                                    A.NOP = C.NOP
									$sp $where
                                ORDER BY C.SPPT_TAHUN_PAJAK DESC) AS PENAGIHAN
                                GROUP BY
                                NOP LIMIT ".$hal.",".$this->perpage; 
                        // echo $query; exit;
                        if($sts==9)
								$qry = "SELECT COUNT(*) AS TOTALROWS
										FROM
												PBB_SPPT_PENAGIHAN_KHUSUS A
										JOIN PBB_SPPT C
										WHERE
												A.NOP = C.NOP $sp $where
											ORDER BY C.SPPT_TAHUN_PAJAK DESC";
                        else
								$qry = "SELECT COUNT(DISTINCT(A.NOP)) AS TOTALROWS
										FROM
												PBB_SPPT_PENAGIHAN_KHUSUS A
										JOIN PBB_SPPT C
										WHERE
												A.NOP = C.NOP $sp $where
											ORDER BY C.SPPT_TAHUN_PAJAK DESC";
                }

		$res = mysqli_query($DBLinkLookUp, $query);
		if ( $res === false || mysqli_num_rows($res) <= 0 ){
			return false;
		}

        $this->totalRows = $this->getTotalRows($qry);
		$HTML = $startLink = $endLink = "";
		//$data = $d;
		$params = "a=".$a."&m=".$m;
		
		$arrStatus = array(
			1 => "SP1 yang sudah diterima Wajib Pajak",
			2 => "Wajib Pajak yang sudah membayar PBB setelah penerbitaan SP 1",
			3 => "Data Wajib Pajak yang dibatalkan",
			4 => "Alamat tidak ditemukan",
			5 => "Tanah sengketa",
			6 => "Wajib Pajak sudah melakukan perubahan data"
		);

		while($tmp = mysqli_fetch_assoc($res)){
			$tgltempo = explode("-",$tmp['SPPT_TANGGAL_JATUH_TEMPO']);
					
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			$newStyle = $newStyle = "";

			$linkprint = "linkto";
			$tahunPajak = $tmp['SPPT_TAHUN_PAJAK'];
			if($sts == 5){
				$linkprint = "linkstpd";
				$newStyle = "";
			} elseif($sts==6 || $sts==7 || $sts==8 || $sts==9){
				$linkprint = "linkcetakpsp".$sts;
				$tahunPajak = $tmp['TAHUN_SP1'];
			} else if($sts==2 || $sts==3){
				$linkprint = "linkcetakup".$sts;
				$tahunPajak = $tmp['TAHUN_SP1'];
			}
			$tagihan	 = $tmp['SPPT_PBB_HARUS_DIBAYAR'];
			if(($tmp['TGL_SP1']) && (!$tmp['TGL_SP2']) && (!$tmp['TGL_SP3'])){
				$jnsSP = "SP1";
				$tagihan = $tmp['KETETAPAN_SP1'];
				$statusPersetujuan = 1;
			}elseif(($tmp['TGL_SP1']) && ($tmp['TGL_SP2']) && (!$tmp['TGL_SP3'])){
				$jnsSP = "SP2";
				$tagihan = $tmp['KETETAPAN_SP2'];
				$statusPersetujuan = 2;
			}elseif(($tmp['TGL_SP1']) && ($tmp['TGL_SP2']) && ($tmp['TGL_SP3'])){
				$jnsSP = "SP3";
				$tagihan = $tmp['KETETAPAN_SP3'];
				$statusPersetujuan = 3;
			}
			
			$HTML .= "\t<div class=\"container\"><tr class=\"row-content\" $newStyle>\n";
			if ($sts==9)
				$HTML .= "\t\t<td class=\"".$class." $linkprint\" id=\"".$tmp['NOP']."+".$tahunPajak."+".$statusPersetujuan."\">".$tmp['NOP']."</td> \n";
			else 
				$HTML .= "\t\t<td class=\"".$class." $linkprint\" id=\"".$tmp['NOP']."+".$tahunPajak."\">".$tmp['NOP']."</td> \n";
			
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['WP_NAMA']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['WP_ALAMAT']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['WP_KELURAHAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['OP_ALAMAT']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['OP_KECAMATAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['OP_KELURAHAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['OP_RT']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['OP_RW']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['OP_LUAS_BUMI']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['OP_LUAS_BANGUNAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($tmp['OP_NJOP_BUMI'],0,',','.')."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($tmp['OP_NJOP_BANGUNAN'],0,',','.')."</td> \n";
			// $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['SPPT_TAHUN_PAJAK']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".($tgltempo[2]."-".$tgltempo[1]."-".$tgltempo[0])."</td>\n";
                        
                        if(($sts==2)||($sts==6)){
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($tagihan,0,',','.')."</td> \n";
                        }elseif($sts==7){
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($tagihan,0,',','.')."</td> \n";
                        }
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($tmp['SPPT_PBB_HARUS_DIBAYAR'],0,',','.')."</td> \n";
//			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['STATUS_CETAK']."</td>\n";
                        // if($isAdminPenagihan == 1){
                            if($sts != 4){
                                    if($sts == 5){
                                            $linkdate = "linkdate";
                                            if($tmp['TGL_STPD'] == "")
                                                $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+".$tmp['STATUS_SP']."\" align=\"center\">Input</td> \n";
                                            else $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+".$tmp['STATUS_SP']."\" align=\"center\">".substr($tmp['TGL_STPD'],8,2)."-".substr($tmp['TGL_STPD'],5,2)."-".substr($tmp['TGL_STPD'],0,4)."</td> \n";
                                            
                                            $linkdate = "linkketerangan";
                                            if($tmp['KETERANGAN_STPD'] == "")
                                                $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP".$tmp['STATUS_SP']."\" align=\"center\">Input</td> \n";
                                            else $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP".$tmp['STATUS_SP']."\" align=\"center\">".$tmp['KETERANGAN_STPD']."</td> \n";
                                    }
                                    elseif($sts == 6 || $sts == 7 || $sts == 8){
											switch($sts){
												case 6 : $keterangan = $tmp['KETERANGAN_SP1']; break;
												case 7 : $keterangan = $tmp['KETERANGAN_SP2']; break;
												case 8 : $keterangan = $tmp['KETERANGAN_SP3']; break;
											}
                                            $linkdate = "linkketpsp1";
                                            $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP".$sts."+".$tmp['STATUS_SP']."+".$keterangan."\" align=\"center\">".(($tmp['STATUS_SP'] == '' || $tmp['STATUS_SP'] == NULL || $tmp['STATUS_SP'] == 0)? "Input" : $arrStatus[$tmp['STATUS_SP']])."</td> \n";
                                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$keterangan."</td> \n";
                                    } else if($sts == 9){
											$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$jnsSP."</td> \n";
											$HTML .= "\t\t<td class=\"".$class."\" align=\"center\" id=\"{$tmp['NOP']}\">".(($tmp['STATUS_PERSETUJUAN'] == '1' || $tmp['STATUS_PERSETUJUAN'] == '2' || $tmp['STATUS_PERSETUJUAN'] == '3')? "Sudah disetujui" : "<button class=\"btnApprove\" page=\"{$page}\" status=\"{$statusPersetujuan}\" value=\"{$tmp['NOP']}\">Setuju</button>")."</td> \n";
									}
                            }else{
                                    $HTML .= "\t\t<td class=\"$class\" align=\"center\">".$tmp['STATUS_SP']."</td> \n";
                                    $HTML .= "\t\t<td class=\"$class\" align=\"center\"></td> \n";
                            }
                        // }
			$HTML .= "\t</tr></div>\n";
		}
		$dat = $HTML;
		return true;
	}
        
        function getDocumentAllSP($sts,&$dat) {
		global $DBLinkLookUp, $DBLink, $sRootPath,$json,$a,$m,$page, $sp1, $sp2, $sp3, $isAdminPenagihan;

		$where = $this->conditionBuilder();
                
		$query ="";
		$hal = (($page-1) > 0 ? ($page-1) : 0) * $this->perpage;

                $qrangetime = "";
                
                $query = "SELECT NOP,
                        SPPT_TAHUN_PAJAK, WP_NAMA, WP_KELURAHAN, WP_ALAMAT, OP_ALAMAT, OP_KECAMATAN, OP_KELURAHAN,
                        OP_RT, OP_RW, OP_LUAS_BUMI, OP_LUAS_BANGUNAN, OP_NJOP_BUMI, OP_NJOP_BANGUNAN, SPPT_TANGGAL_JATUH_TEMPO, SPPT_PBB_HARUS_DIBAYAR,
                        TGL_SP1, TGL_SP2, TGL_SP3, TGL_STPD, KETERANGAN_STPD, KETERANGAN_SP 
                        FROM VIEW_PBB_SPPT_PENAGIHAN_KHUSUS WHERE SPPT_TAHUN_PAJAK >= '2007' $qrangetime $where ORDER BY WP_NAMA ASC, SPPT_TAHUN_PAJAK DESC LIMIT ".$hal.",".$this->perpage;
				echo $query;
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM VIEW_PBB_SPPT_PENAGIHAN_KHUSUS WHERE SPPT_TAHUN_PAJAK >= '2007' $qrangetime $where";
        
		$res = mysqli_query($DBLinkLookUp, $query);
		if ( $res === false ){
			return false;
		}
		
		$this->totalRows = $this->getTotalRows($qry);
                
		$HTML = $startLink = $endLink = "";
		
		$params = "a=".$a."&m=".$m;

		while($tmp = mysqli_fetch_assoc($res)){
			$tgltempo = explode("-",$tmp['SPPT_TANGGAL_JATUH_TEMPO']);
					
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			$newStyle = $newStyle = "";

			$linkprint = "linkto";
			if($sts == 5){
				$linkprint = "linkstpd";
				$newStyle = "";
			}

			$HTML .= "\t<div class=\"container\"><tr $newStyle>\n";
			$HTML .= "\t\t<td class=\"".$class." $linkprint\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."\">".$tmp['NOP']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['WP_NAMA']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['WP_ALAMAT']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['WP_KELURAHAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['OP_ALAMAT']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['OP_KECAMATAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$tmp['OP_KELURAHAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['OP_RT']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['OP_RW']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['OP_LUAS_BUMI']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['OP_LUAS_BANGUNAN']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($tmp['OP_NJOP_BUMI'],0,',','.')."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($tmp['OP_NJOP_BANGUNAN'],0,',','.')."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$tmp['SPPT_TAHUN_PAJAK']."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".($tgltempo[2]."-".$tgltempo[1]."-".$tgltempo[0])."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($tmp['SPPT_PBB_HARUS_DIBAYAR'],0,',','.')."</td> \n";
                        
                       if($isAdminPenagihan == 1){
                            $linkdate = "linkdate";

                            if($tmp['TGL_SP1'] == "")
                                $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP1\" align=\"center\">Input</td> \n";
                            else $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP1\" class=\"".$class."\" align=\"center\">".substr($tmp['TGL_SP1'],8,2)."-".substr($tmp['TGL_SP1'],5,2)."-".substr($tmp['TGL_SP1'],0,4)."</td> \n";
                            if($tmp['TGL_SP2'] == "")
                                $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP2\" align=\"center\">Input</td> \n";
                            else $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP2\" align=\"center\">".substr($tmp['TGL_SP2'],8,2)."-".substr($tmp['TGL_SP2'],5,2)."-".substr($tmp['TGL_SP2'],0,4)."</td> \n";
                            if($tmp['TGL_SP3'] == "")
                                $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP3\" align=\"center\">Input</td> \n";
                            else $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP3\" align=\"center\">".substr($tmp['TGL_SP3'],8,2)."-".substr($tmp['TGL_SP3'],5,2)."-".substr($tmp['TGL_SP3'],0,4)."</td> \n";

                            $linkdate = "linkketerangan";
                            if($tmp['KETERANGAN_SP'] == "")
                                $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP\" align=\"center\">Input</td> \n";
                            else $HTML .= "\t\t<td class=\"".$class." $linkdate\" id=\"".$tmp['NOP']."+".$tmp['SPPT_TAHUN_PAJAK']."+SP\" align=\"center\">".$tmp['KETERANGAN_SP']."</td> \n";
                       }
                        $HTML .= "\t</tr></div>\n";
		}
		$dat = $HTML;
		return true;
	}
        
        public function getKecamatan(){
            global $DBLink, $kodekota;

            $kecamatan = array();

            $query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID='$kodekota' ORDER BY CPC_TKC_KECAMATAN ASC";
            $buffer = mysqli_query($DBLink, $query);
            if(mysqli_num_rows($buffer) > 0){
                while($kec = mysqli_fetch_assoc($buffer)){
                    $tmp = array(
                        "id" => $kec["CPC_TKC_ID"],
                        "nama" => $kec["CPC_TKC_KECAMATAN"]
                    );
                    $kecamatan[] = $tmp;
                }
            }
            return $kecamatan;
        }

        public function getKelurahan($idkec){
            global $DBLink;

            $kelurahan = array();

            $query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID='$idkec'";
            $buffer = mysqli_query($DBLink, $query);
            if(mysqli_num_rows($buffer) > 0){
                while($kel = mysqli_fetch_assoc($buffer)){
                    $tmp = array(
                        "id" => $kel['CPC_TKL_ID'],
                        "nama" => $kel['CPC_TKL_KELURAHAN']
                    );
                    $kelurahan[] = $tmp;
                }
            }
            return $kelurahan;
        }

	public function headerContent($sts) {
		global $condition, $isAdminPenagihan, $isViewData, $lblkel;
                
                $slcTagihan = array();
                if(!isset($condition[1]))$condition[1]=8;
                for($ctr=0; $ctr<=8; $ctr++){
                    $slcTagihan[$ctr] = ($condition[1] == $ctr) ? "selected" : "";
                }

                $optKec = "<option value=\"\">--semua--</option>";
                $kec = $this->getKecamatan();
                for($ctr=0; $ctr<count($kec); $ctr++){
                    $selected = "";
                    if($condition[2] == $kec[$ctr]["id"]) $selected = "selected";
                    $optKec .= "<option value=\"".$kec[$ctr]["id"]."\" $selected >".ucfirst(strtolower($kec[$ctr]["nama"]))."</option>";
                }

                $optKel = "";
                if($condition[3] != ""){
                    $kel = $this->getKelurahan($condition[2]);
                    for($ctr=0; $ctr<count($kel); $ctr++){
                        $selected = "";
                        if($condition[3] == $kel[$ctr]["id"]) $selected = "selected";
                        $optKel .= "<option value=\"".$kel[$ctr]["id"]."\" $selected >".ucfirst(strtolower($kel[$ctr]["nama"]))."</option>";
                    }
                }else{
                    $optKel = "<option value=\"\">--semua--</option>";
                }

		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .=" <!-- <input type=\"button\" value=\"Cetak\" id=\"btn-print-{$sts}\" name=\"btn-print\" /> -->
                        &nbsp;<!-- Tahun --> <input type=\"hidden\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (".$sts.");\" id=\"src-tahun-{$sts}\" name=\"src-tahun-{$sts}\" size=\"5\" maxlength=\"4\" value=\"{$condition[4]}\"/>
                        &nbsp;NOP <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (".$sts.");\" id=\"src-nop-{$sts}\" name=\"src-nop-{$sts}\" size=\"20\" maxlength=\"18\" value=\"{$condition[5]}\"/>
                        &nbsp;Nama WP <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (".$sts.");\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"30\" value=\"{$condition[0]}\"/>
                        &nbsp;Daftar Tagihan <select  id=\"src-tagihan-{$sts}\" name=\"src-tagihan-{$sts}\">
                                        <option value=\"0\" ".$slcTagihan[0].">--semua--</option> 
                                        <option value=\"1\" ".$slcTagihan[1].">0 s/d <5jt</option>
                                        <option value=\"2\" ".$slcTagihan[2].">5jt s/d <10jt</option>
                                        <option value=\"3\" ".$slcTagihan[3].">10jt s/d <20jt</option>
                                        <option value=\"4\" ".$slcTagihan[4].">20jt s/d <30jt</option>
                                        <option value=\"5\" ".$slcTagihan[5].">30jt s/d <40jt</option>
                                        <option value=\"6\" ".$slcTagihan[6].">40jt s/d <50jt</option>
                                        <option value=\"7\" ".$slcTagihan[7].">50jt s/d <100jt</option>
                                        <option value=\"8\" ".$slcTagihan[8].">>=100jt</option>
                                      </select>
						
                        &nbsp;Kecamatan <select  id=\"src-kecamatan-{$sts}\" name=\"src-kecamatan-{$sts}\">$optKec</select>
                        &nbsp;".$lblkel." <select  id=\"src-kelurahan-{$sts}\" name=\"src-kelurahan-{$sts}\">$optKel</select>
                        <input type=\"button\" value=\"Tampilkan\" id=\"btn-src-{$sts}\" name=\"btn-src-{$sts}\" onclick=\"btnCari($sts);\" />\n
                        <!-- <input type=\"button\" value=\"Tampilkan Semua\" id=\"btn-clr-{$sts}\" name=\"btn-clr-{$sts}\" onclick=\"btnTampilSemua($sts);\" />\n -->
                        </form>\n";
		$HTML .= "<div style=\"overflow: scroll;\">
                          <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"2000\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\">Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Nama WP </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat WP</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">".$lblkel." WP</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\">Kecamatan OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\">".$lblkel." OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"50\">RT OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"50\">RW OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"60\">Luas Bumi</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"60\">Luas Bangunan</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"120\">Tot NJOP Bumi</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"120\">Tot NJOP Bangunan</td>\n";
		// $HTML .= "\t\t<td class=\"tdheader\" width=\"60\">Tahun Pajak</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Tanggal Jatuh Tempo</td>\n";
                if(($sts==2)||($sts==6)){
                    $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Ketetapan SP1</td>\n";
                }elseif($sts==7){
                    $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Ketetapan SP2</td>\n";
                }
                $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Pokok</td>\n";
//		$HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Status Cetak</td>\n";
        
                // if($isAdminPenagihan == 1){
                    if($sts == 1 || $sts == 2 || $sts == 3){
                        // $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Tgl Diterima SP</td>\n";
                    }else if($sts == 5){
                        $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Tgl Diterima STPD</td>\n";
                    }else if($sts==9){
						$HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Jenis SP</td>\n";
					}else{
                        $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Status SP</td>\n";
						$HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Keterangan</td>\n";
                    }
                    if($sts == 5) $HTML .= "\t\t<td class=\"tdheader\" width=\"300\">Keterangan</td>\n";
					if($sts == 9) $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Disetujui</td>\n";
                // }
		$HTML .= "\t</tr>\n";

        if (!$isViewData) {
            if($sts == 5) $HTML .= "<tr><td colspan=\"18\"><div id=\"loading\">Klik tombol <b>Cari</b> atau <b>Tampilkan Semua</b> untuk memulai menampilkan data !</div></td></tr> ";
            else $HTML .= "<tr><td colspan=\"17\"><div id=\"loading\">Klik tombol <b>Cari</b> atau <b>Tampilkan Semua</b> untuk memulai menampilkan data !</div></td></tr> ";
        }else if ($this->getDocument($sts,$dt)) {
            if($sts == 5) $HTML .= "<tr><td colspan=\"18\"><div id=\"loading\"></div></td></tr> ";
            else $HTML .= "<tr><td colspan=\"17\"><div id=\"loading\"></div></td></tr> ";
            $HTML .= $dt;
        } else {
            if($sts == 5) $HTML .= "<tr><td colspan=\"18\"><div id=\"loading\">Data Kosong !</div></td></tr> ";
            else $HTML .= "<tr><td colspan=\"17\"><div id=\"loading\">Data Kosong !</div></td></tr> ";
        }

		$HTML .= "</table></div>\n";
		return $HTML;
	}
        
        public function headerContentAllSP($sts) {
		global $condition, $isAdminPenagihan, $isViewData, $lblkel;

                $slcTagihan = array();
                for($ctr=0; $ctr<=8; $ctr++){
                    $slcTagihan[$ctr] = ($condition[1] == $ctr) ? "selected" : "";
                }

                $optKec = "<option value=\"\">--semua--</option>";
                $kec = $this->getKecamatan();
                for($ctr=0; $ctr<count($kec); $ctr++){
                    $selected = "";
                    if($condition[2] == $kec[$ctr]["id"]) $selected = "selected";
                    $optKec .= "<option value=\"".$kec[$ctr]["id"]."\" $selected >".ucfirst(strtolower($kec[$ctr]["nama"]))."</option>";
                }

                $optKel = "";
                if($condition[3] != ""){
                    $kel = $this->getKelurahan($condition[2]);
                    for($ctr=0; $ctr<count($kel); $ctr++){
                        $selected = "";
                        if($condition[3] == $kel[$ctr]["id"]) $selected = "selected";
                        $optKel .= "<option value=\"".$kel[$ctr]["id"]."\" $selected >".ucfirst(strtolower($kel[$ctr]["nama"]))."</option>";
                    }
                }else{
                    $optKel = "<option value=\"\">--semua--</option>";
                }

		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .=" <input type=\"button\" value=\"Cetak\" id=\"btn-print-{$sts}\" name=\"btn-print\" />
                        &nbsp;Tahun <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (".$sts.");\" id=\"src-tahun-{$sts}\" name=\"src-tahun-{$sts}\" size=\"5\" maxlength=\"4\" value=\"{$condition[4]}\"/>
                        &nbsp;NOP <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (".$sts.");\" id=\"src-nop-{$sts}\" name=\"src-nop-{$sts}\" size=\"20\" maxlength=\"18\" value=\"{$condition[5]}\"/>
                        &nbsp;Nama WP <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs (".$sts.");\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"30\" value=\"{$condition[0]}\"/>
                        &nbsp;Daftar Tagihan <select  id=\"src-tagihan-{$sts}\" name=\"src-tagihan-{$sts}\">
                                        <option value=\"0\" ".$slcTagihan[0].">--semua--</option>
                                        <option value=\"1\" ".$slcTagihan[1].">0 s/d <5jt</option>
                                        <option value=\"2\" ".$slcTagihan[2].">5jt s/d <10jt</option>
                                        <option value=\"3\" ".$slcTagihan[3].">10jt s/d <20jt</option>
                                        <option value=\"4\" ".$slcTagihan[4].">20jt s/d <30jt</option>
                                        <option value=\"5\" ".$slcTagihan[5].">30jt s/d <40jt</option>
                                        <option value=\"6\" ".$slcTagihan[6].">40jt s/d <50jt</option>
                                        <option value=\"7\" ".$slcTagihan[7].">50jt s/d <100jt</option>
                                        <option value=\"8\" ".$slcTagihan[8].">>=100jt</option>
                                      </select>
                        &nbsp;Kecamatan <select  id=\"src-kecamatan-{$sts}\" name=\"src-kecamatan-{$sts}\">$optKec</select>
                        &nbsp;".$lblkel." <select  id=\"src-kelurahan-{$sts}\" name=\"src-kelurahan-{$sts}\">$optKel</select>
                        <input type=\"button\" value=\"Cari\" id=\"btn-src-{$sts}\" name=\"btn-src-{$sts}\" onclick=\"btnCari($sts);\" />\n
                        <input type=\"button\" value=\"Tampilkan Semua\" id=\"btn-clr-{$sts}\" name=\"btn-clr-{$sts}\" onclick=\"btnTampilSemua($sts);\" />\n
                        </form>\n";
		$HTML .= "<div style=\"overflow: scroll;\">
                          <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"2500\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\">Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Nama WP </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat WP</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">".$lblkel." WP</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\">Kecamatan OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\">".$lblkel." OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"50\">RT OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"50\">RW OP</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"60\">Luas Bumi</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"60\">Luas Bangunan</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"120\">Tot NJOP Bumi</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"120\">Tot NJOP Bangunan</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"60\">Tahun Pajak</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Tanggal Jatuh Tempo</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Tagihan</td>\n";
        
                if($isAdminPenagihan == 1){
                    $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Tgl Diterima SP1</td>\n";
                    $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Tgl Diterima SP2</td>\n";
                    $HTML .= "\t\t<td class=\"tdheader\" width=\"100\">Tgl Diterima SP3</td>\n";
                    $HTML .= "\t\t<td class=\"tdheader\" width=\"300\">Keterangan</td>\n";
                }
		$HTML .= "\t</tr>\n";

		if (!$isViewData) {
                    $HTML .= "<tr><td colspan=\"20\">Klik tombol <b>Cari</b> atau <b>Tampilkan Semua</b> untuk memulai menampilkan data !</td></tr> ";
                }else if ($this->getDocumentAllSP($sts,$dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "<tr><td colspan=\"20\">Data Kosong !</td></tr> ";
		}

		$HTML .= "</table></div>\n";
		return $HTML;
	}
        
	function getContent() {
		$HTML = "";
                if($this->status == '4')
                    $HTML .= $this->headerContentAllSP($this->status);
                else $HTML .= $this->headerContent($this->status);
		return $HTML;
	}


	public function displayDataNotaris () {
		echo "<div class=\"ui-widget consol-main-content\">\n";
		echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
		echo $this->getContent();
		echo "\t</div>\n";
		echo "\t<div class=\"ui-widget-header consol-main-content-footer\" align=\"right\">  \n";
		echo $this->paging();
		echo "</div>\n";
	}

	function paging() {
		global $a,$m,$n,$s,$page,$np,$perpage,$defaultPage;
		
		$params = "a=".$a."&m=".$m;
		
		$html = "<div>";
		$row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $this->totalRows ? ($page) * $perpage : $this->totalRows;
		$html .= ($row+1)." - ".($rowlast). " dari ".$this->totalRows;

		if ($page != 1) {
			//$page--;
			$html .= "&nbsp;<a onclick=\"setPage(".$s.",'0')\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $this->totalRows ) {
			//$page++;
			$html .= "&nbsp;<a onclick=\"setPage(".$s.",'1')\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}
    function updateStatus($status){
            global $DBLinkLookUp, $sp1, $sp2, $sp3;

            $statusSP = "";
            switch($status){
                case 1 : $qrangetime = " AND DATEDIFF(CURDATE(), DATE(SPPT_TANGGAL_JATUH_TEMPO)) >= $sp1 AND (TGL_SP1 = '' OR TGL_SP1 IS NULL) ";
                         $statusSP = "SP1";
                         //echo "<script language=\"javascript\">refreshNotif();</script>";
                         break;
                case 2 : $qrangetime = " AND DATEDIFF(CURDATE(), DATE(TGL_SP1)) >= $sp2 AND (TGL_SP2 = '' OR TGL_SP2 IS NULL) ";
                         $statusSP = "SP2";
                         //echo "<script language=\"javascript\">refreshNotif();</script>";
                         break;
                case 3 : $qrangetime = " AND DATEDIFF(CURDATE(), DATE(TGL_SP2)) >= $sp3 AND (TGL_SP3 = '' OR TGL_SP3 IS NULL) ";
                         $statusSP = "SP3";
                         //echo "<script language=\"javascript\">refreshNotif();</script>";
                         break;
            }
            $query = "SELECT * FROM PBB_SPPT WHERE (PAYMENT_FLAG != 1 OR PAYMENT_FLAG IS NULL) AND SPPT_TAHUN_PAJAK >= '2007' $qrangetime";
            $buffer = mysqli_query($DBLinkLookUp, $query);

            if(mysqli_num_rows($buffer) > 0){
                while($tmp = mysqli_fetch_assoc($buffer)){
                    //update status sp
                    $sqlupdate = "UPDATE PBB_SPPT SET STATUS_SP ='$statusSP' WHERE NOP='".$tmp['NOP']."' AND SPPT_TAHUN_PAJAK='".$tmp['SPPT_TAHUN_PAJAK']."'";
                    $tmpupdate = mysqli_query($DBLinkLookUp, $sqlupdate);

                    //update status cetak
                    $sqlupdate = "UPDATE PBB_SPPT SET STATUS_CETAK = 'Belum Tercetak' WHERE NOP='".$tmp['NOP']."' AND SPPT_TAHUN_PAJAK='".$tmp['SPPT_TAHUN_PAJAK']."' AND STATUS_CETAK != 'Telah Tercetak'";
                    $tmpupdate = mysqli_query($DBLinkLookUp, $sqlupdate);
                }
            }
        }
}


?>

<script type="text/javascript">
    var kepala = "<?php echo $kepala; ?>";
    var nip = "<?php echo $nip; ?>";
    var jabatan = "<?php echo $jabatan; ?>";
    var kota = "<?php echo $kota; ?>";
    var status = <?php echo $s;?>;
    var appId = "<?php echo $a; ?>";
    var thn = "<?php echo $thn; ?>";
    var uname = "<?php echo $uname; ?>";
	
	function btnCari(sts){
		$("#loading").html("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>");
		$('.row-content').hide();
        setTabs(sts);
	}
	
	function btnTampilSemua(sts){
                $("#loading").html("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>");
		setTabs(sts,true);
	}
	
    function printToPDF(nop, thnpajak, sp) {
		// if(typeof(sp)==='undefined') sp = '';
		if(status == 6 || status == 7 || status == 8){
			status = status - 5;
		}else if(status==9){
			status = sp;
		}
        if(status == '4')
            window.open('view/PBB/penagihan_khusus_2016/svc-print-penagihan.php?nop='+nop+'&uname='+uname+'&thnpajak='+thnpajak+"&kepala="+kepala+"&nip="+nip+"&jabatan="+jabatan+"&kota="+kota+"&bank="+bank+"&denda="+denda+"&totalBulanPajak="+totalBulanPajak+"&tipeKalkulasiPajak="+tipeKalkulasiPajak+"&appId=<?php echo $a;?>", '_blank');
        else window.open('view/PBB/penagihan_khusus_2016/svc-print-penagihan.php?nop='+nop+'&uname='+uname+'&thnpajak='+thnpajak+"&kepala="+kepala+"&nip="+nip+"&jabatan="+jabatan+"&kota="+kota+"&bank="+bank+"&denda="+denda+"&totalBulanPajak="+totalBulanPajak+"&tipeKalkulasiPajak="+tipeKalkulasiPajak+"&appId=<?php echo $a;?>&SP="+status, '_blank');
    }
    function printSTPDPBBToPDF(nop, thnpajak, nourut) {
        window.open('view/PBB/penagihan_khusus_2016/svc-stpdpbb-pdf.php?nop='+nop+'&thnpajak='+thnpajak+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname+"&kepala="+kepala+"&nip="+nip+"&jabatan="+jabatan+"&kota="+kota+"&bank="+bank+"&denda="+denda+"&totalBulanPajak="+totalBulanPajak+"&tipeKalkulasiPajak="+tipeKalkulasiPajak+"&kd_dispenda="+kd_dispenda+"&kd_bidang="+kd_bidang+"&appId="+appId, '_blank');
    }
    function printSuratPernyataanToPDF(nop, thnpajak) {
        window.open('view/PBB/penagihan_khusus_2016/svc-suratpernyataan-pdf.php?nop='+nop+'&thnpajak='+thnpajak+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname+'&kepala='+kepala+'&nip='+nip+"&jabatan="+jabatan+'&kota='+kota+'&bank='+bank+'&denda='+denda+'&totalBulanPajak='+totalBulanPajak+'&tipeKalkulasiPajak='+tipeKalkulasiPajak+'&limitTahunPajak='+limitTahunPajak, '_blank');
    }
    function setNoUrut(){
        $.ajax({
           type: "POST",
           url: "./view/PBB/penagihan/nourut.php",
           success: function(nourut){
           }
        });
    }

    $(document).ready(function(){
		
		$("#select-all").click(function() {
		  if(this.checked) {
			  // Iterate each checkbox
			  $(':checkbox').each(function() {
				  this.checked = true;
			  });
		  }
		  else {
			$(':checkbox').each(function() {
				  this.checked = false;
			  });
		  }
		});
	
		$(".btnApprove").click(function(){
			
			var nop = $(".btnApprove").val();
			var statusPersetujuan = $(".btnApprove").attr('status');
			var page = $(".btnApprove").attr('page');
			
			if(confirm('apakah anda yakin untuk menyetujui NOP '+nop+' ini?')===false){
				return false;
			}
			
			$.ajax({
				   type: "POST",
				   url: "./view/PBB/penagihan_khusus_2016/svc-update-persetujuan.php",
				   data: "nop="+nop+'&statusPersetujuan='+statusPersetujuan+'&page='+page+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname,
				   dataType : "json", 
				   success: function(data){
						if(data.respon="true"){
							console.log(data);
							$("#"+nop).html('<span>Sudah disetujui</span>');
						} else {
							alert('Terjadi kesalahan server');
						}
				   }
			});

        });
		
        $(".linkto").click(function(){
			
            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
            var nop = v_wp[0];

            $("#nop_sp1").attr("value",nop);
            $.ajax({
                        type: "POST",
                        url: "./view/PBB/penagihan_khusus_2016/svc-update-sp1.php",
                        data: "nop="+nop+'&listTahun='+v_wp[1]+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname+'&thn='+thn,
                        dataType : "json", 
                        success: function(data){
                                    if(data.respon="true"){
                                            printToPDF(nop, v_wp[1]);
                                    } else {
                                            alert('Terjadi kesalahan server');
                                    }
                        }
            });
         });
		 
		 $(".linkcetakpsp"+status).click(function(){
            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
            printToPDF(v_wp[0],v_wp[1],v_wp[2]);
         });
		 
		 $(".linkcetakup"+status).click(function(){
			
            var wp = $(this).attr("id");
            var v_wp = wp.split("+");
			var nop = v_wp[0];
			var listTahun = v_wp[1];
			var sts = status;
			
			$.ajax({
				type: "POST",
				url: "./view/PBB/penagihan_khusus_2016/svc-update-sp23.php",
				data: "nop="+nop+'&listTahun='+listTahun+'&sts='+sts+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname,
				dataType : "json", 
				success: function(data){
					if(data.respon="true"){
						console.log(data);
						printToPDF(nop,listTahun);
					} else {
						alert('Terjadi kesalahan server');
					}
				}
			});
         });
		 
		 $("#closedyear").click(function(){
            $("#setyear2").css("display","none");
            $("#setyear1").css("display","none");
         });
		 
		 $(".linkketpsp1").click(function(){
			$("#setketSP1-1").css("display","block");
            $("#setketSP1-2").css("display","block");
			
			var wp = $(this).attr("id");
            var v_wp = wp.split("+");
			var v_option = v_wp[3];
			
			$("#nop_fu").attr("value",v_wp[0]);
			$("#ketsp"+v_option).attr('checked',true);
			$("#keterangan").attr("value",v_wp[4]);
			
         });
		 
		 $("#closesetSP1").click(function(){
            $("#setketSP1-2").css("display","none");
            $("#setketSP1-1").css("display","none");
         });
		 
		 $("#simpanketSP1").unbind('click').click(function(){
		 
			var statsp1 		= $('input[name="ketsp1"]:checked').val();
			var keterangan		= $('#keterangan').val();
			var nop				= $("#nop_fu").val();
			var sts 			= status;
			var radioBtn		= $('input[name=ketsp1]:checked').length;
			
            if(radioBtn <= 0)
			{
				label = "<label><font color='red'>Silahkan pilih keterangan </font></label>";
				document.getElementById("error1").innerHTML = label;
			} else
			if(keterangan == ''){
				label = "<label><font color='red'>Silahkan isi keterangan </font></label>";
				document.getElementById("error2").innerHTML = label;
			}
			else {
				$.ajax({
					type: "POST",
					url: "./view/PBB/penagihan_khusus_2016/svc-update-stat-sp.php",
					data: "nop="+nop+"&sts="+sts+"&keterangan="+keterangan+"&statsp1="+statsp1+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname,
					dataType:"json",
					success: function(data){
						console.log(data);
						if(data.respon==true){
							alert('Penyimpanan data berhasil.');
							$("#setketSP1-2").hide();
							$("#setketSP1-1").hide();
							setTabs(status);
						}else alert('Penyimpanan data gagal.');
					}
				});
			}
        });

        $(".linkstpd").bind("click",function(){
            var wp = $(this).attr("id");
            var v_wp = wp.split("+");

            printSTPDPBBToPDF(v_wp[0],v_wp[1]);
            printSuratPernyataanToPDF(v_wp[0],v_wp[1]);
            setNoUrut();
         });

         
        $("#simpantanggal").unbind('click').click(function(){
            if($("#tanggal").val() != ""){
                $.ajax({
                   type: "POST",
                   url: "./view/PBB/penagihan/svc-update-datesp.php",
                   data: "nop="+$("#nop_fu").val()+"&thnpajak="+$("#thnpajak_fu").val()+"&tgl="+$("#tanggal").val()+"&sp="+$("#sp_fu").val()+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname,
                   success: function(msg){
                       //refreshNotif();
                       if(msg=='1'){
                            alert('Penyimpanan data berhasil.');
                            $("#contsetdate2").hide();
                            $("#contsetdate1").hide();
                            setTabs(status);
                       }else alert('Penyimpanan data gagal. Dengan error :'+msg);
                   }
                 });
            }else{
                alert("Tanggal tidak boleh kosong, silahkan isi!");
            }
        });

        $(".linkdate").click(function(){
            $("#contsetdate1").css("display","block");
            $("#contsetdate2").css("display","block");

            var wp = $(this).attr("id");
            var v_wp = wp.split("+");

            $("#nop_fu").attr("value",v_wp[0]);
            $("#thnpajak_fu").attr("value",v_wp[1]);
            if(status != 5)
                $("#sp_fu").attr("value",v_wp[2]);
            else
                $("#sp_fu").attr("value","STPD");
        });
        $("#closeddate").click(function(){
            $("#contsetdate2").css("display","none");
            $("#contsetdate1").css("display","none");
        });
        $("#tanggal").datepicker({ dateFormat: 'yy-mm-dd' });
        
        $("#simpanketerangan").unbind('click').click(function(){
//        $("#simpanketerangan").live("click",function(){
            if($("#keterangan").val() != ""){
                $.ajax({
                   type: "POST",
                   url: "./view/PBB/penagihan/svc-update-keterangan.php",
                   data: "nop="+$("#nop_fuket").val()+"&thnpajak="+$("#thnpajak_fuket").val()+"&keterangan="+$("#keterangan").val()+"&sp="+$("#sp_fuket").val()+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname,
                   success: function(msg){
                       //refreshNotif();
                       if(msg=='1'){
                            alert('Penyimpanan data berhasil.');
                            $("#contsetdate4").hide();
                            $("#contsetdate3").hide();
                            setTabs(status);
                       }else alert('Penyimpanan data gagal. Dengan error :'+msg);
                   }
                 });
            }else{
                alert("Keterangan tidak boleh kosong, silahkan isi!");
            }
        });
        
        $(".linkketerangan").click(function(){
            $("#contsetdate3").css("display","block");
            $("#contsetdate4").css("display","block");

            var wp = $(this).attr("id");
            var v_wp = wp.split("+");

            $("#nop_fuket").attr("value",v_wp[0]);
            $("#thnpajak_fuket").attr("value",v_wp[1]);
            if(status != 5)
                $("#sp_fuket").attr("value",v_wp[2]);
            else
                $("#sp_fuket").attr("value","STPD");
        });
        $("#closedketerangan").click(function(){
            $("#contsetdate4").css("display","none");
            $("#contsetdate3").css("display","none");
        });
        
        $("#src-kecamatan-"+status).change(function(){
            $.ajax({
               type: "POST",
               url: "./view/PBB/penagihan/svc-get-kelurahan.php",
               data: "id="+$(this).val(),
               success: function(msg){
                   $("#src-kelurahan-"+status).html(msg);
               }
             });
        })

        $("#btn-print-"+status).click(function(){
            var tahun = $("#src-tahun-"+status).val();
            var nop = $("#src-nop-"+status).val();
            var kec = $("#src-kecamatan-"+status).val();
            var kel = $("#src-kelurahan-"+status).val();
            var nm = $("#src-approved-"+status).val();
            var tagihan = $("#src-tagihan-"+status).val();
            var sp1 = "<?php echo $sp1?>";
            var sp2 = "<?php echo $sp2?>";
            var sp3 = "<?php echo $sp3?>";
            var lblkel = "<?php echo $lblkel?>";

            window.open('view/PBB/penagihan/svc-listpenagihan-excel.php?dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname+'&nm='+nm+'&tagihan='+tagihan+'&kec='+kec+'&kel='+kel+'&status='+status+"&sp1="+sp1+"&sp2="+sp2+"&sp3="+sp3+"&tahun="+tahun+"&nop="+nop+"&lblkel="+lblkel, '_blank');
        })
    });
</script>


