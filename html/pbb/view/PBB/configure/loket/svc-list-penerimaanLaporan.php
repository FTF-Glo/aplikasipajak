<?php 
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'loket', '', dirname(__FILE__))).'/';
//require_once($sRootPath."inc/payment/c8583.php");
//require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
//echo "<script language=\"javascript\" src=\"view/PBB/loket/mod-tax-service-print.js\" type=\"text/javascript\"></script>\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}


class TaxService{
	function __construct ($userGroup,$user) {
		$this->userGroup = $userGroup;
		$this->user = $user;
		
	}
	
	function getTotalRows($query) {
		global $DBLink;
		$res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			echo $query ."<br>";
			echo mysqli_error($DBLink);
		}
		
		$row = mysqli_fetch_array($res);
		return $row['TOTALROWS'];
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
        
	function getDocument(&$dat) {
		global $DBLink,$json,$a,$m,$tab,$find,$page,$totalrows, $perpage,$arConfig, $arrType,$srcTglAwal,$srcTglAkhir,$srcNomor,$srcNama, $isAdminLoket;
		
		$srcTxt = $find;
		$where = " WHERE CPM_STATUS = 0 ";
		if ($srcNama != "") $where .= " AND BS.CPM_WP_NAME LIKE '%".$srcNama."%' ";
		if ($srcNomor != "") $where .= " AND (BS.CPM_ID LIKE '%".$srcNomor."%' OR NEW.CPM_NEW_NOP LIKE '%".$srcNomor."%'  OR BS.CPM_OP_NUMBER LIKE '%".$srcNomor."%' ) ";
		if ($srcTglAwal != "") $where .= " AND BS.CPM_DATE_RECEIVE >= '".convertDate($srcTglAwal)."' ";
		if ($srcTglAkhir != "") $where .= " AND BS.CPM_DATE_RECEIVE <= '".convertDate($srcTglAkhir)."' ";
		
		$query ="";
		$hal = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
                
               	$query = "SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, 
                            TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN FROM cppmod_pbb_services BS LEFT JOIN 
                            cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN LEFT JOIN 
                            cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN LEFT JOIN
                            cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                            $where ORDER BY CPM_DATE_RECEIVE DESC LIMIT ".$hal.",".$perpage; 
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_pbb_services BS LEFT JOIN 
                        cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN LEFT JOIN 
                        cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN LEFT JOIN
                        cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID $where ";
		
                $res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		$totalrows = $this->getTotalRows($qry);		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = $startLink = $endLink = "";
		$data = $d;
		$params = "a=".$a."&m=".$m."&f=".$arConfig['form_input']."&tab=".$tab; 
                                
                if(count($data->data) > 0){
                    for ($i=0;$i<count($data->data);$i++) {
                            $class = $i%2==0 ? "tdbody1":"tdbody2";
                            $HTML .= "\t<div class=\"container\"><tr>\n";
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">";
                            if($tab == '0' || $tab == '1')
                                $HTML .= "<input id=\"\" name=\"check-all".$tab."[]\" type=\"checkbox\" value=\"".$data->data[$i]->CPM_ID."\" />";
                            $HTML .= "</td>\n"; 
                            $HTML .= "\t\t<td class=\"".$class."\"align=\"center\"><a style='text-decoration: underline;' href=\"main.php?param=".base64_encode($params."&svcid=".$data->data[$i]->CPM_ID)."\">".$data->data[$i]->CPM_ID."</a></td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_WP_NAME."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPC_TKC_KECAMATAN."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPC_TKL_KELURAHAN."</td> \n";
                            if($data->data[$i]->CPM_TYPE == '1')
                                $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_NEW_NOP."</td> \n";
                            else $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_OP_NUMBER."</td> \n";
                            
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$arrType[$data->data[$i]->CPM_TYPE]."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".convertDate($data->data[$i]->CPM_DATE_RECEIVE)."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_RECEIVER."</td> \n";
                            
                            if ($tab == 1){
                                if($isAdminLoket){
                                    if($data->data[$i]->CPM_STATUS == '1' || ($data->data[$i]->CPM_STATUS == '3' && $data->data[$i]->CPM_TYPE == '7')){
                                        $HTML .= "\t\t<td class=\"".$class."\"><input type=\"button\" value=\"Hapus\" title=\"Hapus data loket\" onclick=\"kembalikanKeLoket('".$data->data[$i]->CPM_ID."');\"/></td> \n";
                                    }else $HTML .= "\t\t<td class=\"".$class."\"></td> \n";
                                }
                            }
                            $HTML .= "\t</tr></div>\n";
                    }
                    $dat = $HTML;
                    return true;                    
                }else{
                    return false;
                }
	}
	
        function getDocumentDalamProses(&$dat) {
		global $DBLink,$json,$a,$m,$tab,$find,$page,$totalrows, $perpage,$arConfig, $arrType,$srcTglAwal,$srcTglAkhir,$srcNomor,$srcNama, $isAdminLoket;
		
		$srcTxt = $find;
		$whereClause = array();
		$where = " ";
		
		if ($srcNama != "") $whereClause[] = " TBL.CPM_WP_NAME LIKE '%".$srcNama."%' ";
		if ($srcNomor != "") $whereClause[] = " (TBL.CPM_ID LIKE '%".$srcNomor."%' OR TBL.CPM_NEW_NOP LIKE '%".$srcNomor."%'  OR TBL.CPM_OP_NUMBER LIKE '%".$srcNomor."%' ) ";
		if ($srcTglAwal != "") $whereClause[] = " TBL.CPM_DATE_RECEIVE >= '".convertDate($srcTglAwal)."' ";
		if ($srcTglAkhir != "") $whereClause[] = " TBL.CPM_DATE_RECEIVE <= '".convertDate($srcTglAkhir)."' ";
		
                if($whereClause) $where = " WHERE ".join('AND', $whereClause);
		$query ="";
		$hal = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
                
               	$query = "SELECT TBL.*, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN FROM ( ". $this->getQueryDalamProses() ." ) TBL  
                        LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = TBL.CPM_OP_KECAMATAN  
                        LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = TBL.CPM_OP_KELURAHAN  
                            $where ORDER BY TBL.CPM_DATE_RECEIVE DESC LIMIT ".$hal.",".$perpage; 
                
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM (
                        ". $this->getQueryDalamProses() .") TBL $where ";
		
                $res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		$totalrows = $this->getTotalRows($qry);		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = $startLink = $endLink = "";
		$data = $d;
		$params = "a=".$a."&m=".$m."&f=".$arConfig['form_input']."&tab=".$tab; 
                
                $arrayStatus = array('1' => 'Staf', '2' => 'Verifikasi', '3' => 'Persetujuan');
                $arrayStatusOPBaru = array('0' => 'Staf', '1' => 'Verifikasi 1', '2' => 'Verifikasi 2', '3' => 'Verifikasi 3', '10' => 'Penetapan');
                if(count($data->data) > 0){
                    for ($i=0;$i<count($data->data);$i++) {
                            $statusDokumen = '';
                            if($data->data[$i]->CPM_TYPE == '1' || $data->data[$i]->CPM_TYPE == '2'){
                                $statusDokumen = $arrayStatusOPBaru[$data->data[$i]->CPM_TRAN_STATUS];
                            }else {
                                $statusDokumen = $arrayStatus[$data->data[$i]->CPM_STATUS];
                            }
                            $class = $i%2==0 ? "tdbody1":"tdbody2";
                            $HTML .= "\t<div class=\"container\"><tr>\n";
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">";
                            if($tab == '0' || $tab == '1')
                                $HTML .= "<input id=\"\" name=\"check-all".$tab."[]\" type=\"checkbox\" value=\"".$data->data[$i]->CPM_ID."\" />";
                            $HTML .= "</td>\n"; 
                            $HTML .= "\t\t<td class=\"".$class."\"align=\"center\"><a style='text-decoration: underline;' href=\"main.php?param=".base64_encode($params."&svcid=".$data->data[$i]->CPM_ID)."\">".$data->data[$i]->CPM_ID."</a></td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_WP_NAME."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPC_TKC_KECAMATAN."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPC_TKL_KELURAHAN."</td> \n";
                            if($data->data[$i]->CPM_TYPE == '1')
                                $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_NEW_NOP."</td> \n";
                            else $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_OP_NUMBER."</td> \n";
                            
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$arrType[$data->data[$i]->CPM_TYPE]."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".convertDate($data->data[$i]->CPM_DATE_RECEIVE)."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_RECEIVER."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$statusDokumen."</td> \n";
                            
                            if ($tab == 1){
                                if($isAdminLoket){
                                    if($data->data[$i]->CPM_STATUS == '1' || ($data->data[$i]->CPM_STATUS == '3' && $data->data[$i]->CPM_TYPE == '7')){
                                        $HTML .= "\t\t<td class=\"".$class."\"><input type=\"button\" value=\"Hapus\" title=\"Hapus data loket\" onclick=\"kembalikanKeLoket('".$data->data[$i]->CPM_ID."');\"/></td> \n";
                                    }else $HTML .= "\t\t<td class=\"".$class."\"></td> \n";
                                }
                            }
                            $HTML .= "\t</tr></div>\n";
                    }
                    $dat = $HTML;
                    return true;                    
                }else{
                    return false;
                }
	}
        
	function getDocumentSelesai(&$dat) {
		global $DBLink,$json,$a,$m,$tab,$find,$page,$totalrows, $perpage,$arConfig, $arrType,$srcTglAwal,$srcTglAkhir,$srcNomor,$srcNama, $isAdminLoket;
		
		$srcTxt = $find;
		$whereClause = array();
		$where = " ";
		
		if ($srcNama != "") $whereClause[] = " CPM_WP_NAME LIKE '%".$srcNama."%' ";
		if ($srcNomor != "") $whereClause[] = " (CPM_ID LIKE '%".$srcNomor."%' OR CPM_NEW_NOP LIKE '%".$srcNomor."%'  OR CPM_OP_NUMBER LIKE '%".$srcNomor."%' ) ";
		if ($srcTglAwal != "") $whereClause[] = " CPM_DATE_RECEIVE >= '".convertDate($srcTglAwal)."' ";
		if ($srcTglAkhir != "") $whereClause[] = " CPM_DATE_RECEIVE <= '".convertDate($srcTglAkhir)."' ";
		
                if($whereClause) $where = " WHERE ".join('AND', $whereClause);
		$query ="";
		$hal = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
                
               	$query = "SELECT * FROM ( ". $this->getQuerySelesai() ." ) TBL 
                            $where ORDER BY CPM_DATE_RECEIVE DESC LIMIT ".$hal.",".$perpage; 
                
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM (
                        ". $this->getQuerySelesai() .") TBL $where ";
		
                $res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		$totalrows = $this->getTotalRows($qry);		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = $startLink = $endLink = "";
		$data = $d;
		$params = "a=".$a."&m=".$m."&f=".$arConfig['form_input']."&tab=".$tab; 
                
                if(count($data->data) > 0){
                    for ($i=0;$i<count($data->data);$i++) {
                            $class = $i%2==0 ? "tdbody1":"tdbody2";
                            $HTML .= "\t<div class=\"container\"><tr>\n";
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">";
                            if($tab == '0' || $tab == '1')
                                $HTML .= "<input id=\"\" name=\"check-all".$tab."[]\" type=\"checkbox\" value=\"".$data->data[$i]->CPM_ID."\" />";
                            $HTML .= "</td>\n"; 
                            $HTML .= "\t\t<td class=\"".$class."\"align=\"center\"><a style='text-decoration: underline;' href=\"main.php?param=".base64_encode($params."&svcid=".$data->data[$i]->CPM_ID)."\">".$data->data[$i]->CPM_ID."</a></td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_WP_NAME."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPC_TKC_KECAMATAN."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPC_TKL_KELURAHAN."</td> \n";
                            if($data->data[$i]->CPM_TYPE == '1')
                                $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_NEW_NOP."</td> \n";
                            else $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_OP_NUMBER."</td> \n";
                            
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$arrType[$data->data[$i]->CPM_TYPE]."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".convertDate($data->data[$i]->CPM_DATE_RECEIVE)."</td> \n";
                            $HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_RECEIVER."</td> \n";
                            
                            if ($tab == 1){
                                if($isAdminLoket){
                                    if($data->data[$i]->CPM_STATUS == '1' || ($data->data[$i]->CPM_STATUS == '3' && $data->data[$i]->CPM_TYPE == '7')){
                                        $HTML .= "\t\t<td class=\"".$class."\"><input type=\"button\" value=\"Hapus\" title=\"Hapus data loket\" onclick=\"kembalikanKeLoket('".$data->data[$i]->CPM_ID."');\"/></td> \n";
                                    }else $HTML .= "\t\t<td class=\"".$class."\"></td> \n";
                                }
                            }
                            $HTML .= "\t</tr></div>\n";
                    }
                    $dat = $HTML;
                    return true;                    
                }else{
                    return false;
                }
	}
        
        public function headerContent() {
            global $find,$a,$m,$tab,$srcTglAwal,$srcTglAkhir,$srcNomor,$srcNama;
			
            $HTML = "";
            if($tab == 1) $HTML = $this->headerContentDalamProses();
            elseif($tab == 2) $HTML = $this->headerContentSelesai();
            else $HTML = $this->headerContentPenerimaan();
            
            if($tab == 1) $this->getDocumentDalamProses($dt);
            elseif($tab == 2) $this->getDocumentSelesai($dt);
            else $this->getDocument($dt);
            
            if ($dt) {
                    $HTML .= $dt;
            } else {
                    $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
            }
            $HTML .= "</table>\n";
            return $HTML;
	}
	
	public function headerContentPenerimaan() {
            global $find,$a,$m,$arConfig,$appConfig,$tab,$srcTglAwal,$srcTglAkhir,$srcNomor,$srcNama;
			
            $params = "a=".$a."&m=".$m;
            $startLink = "<a style='text-decoration: none;' href=\"main.php?param=".base64_encode($params."&f=".$arConfig['form_input']."&jnsBerkas=1")."\">";
            $endLink = "</a>";
			
			$HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
            $HTML .= "
            <div style='overflow:auto;'>
                <div style='float:left;'>
                    $startLink<input type=\"button\" value=\"Tambah\" id=\"btn-add\" name=\"btn-add\"/>$endLink
                    <input type=\"button\" value=\"Hapus\" id=\"btn-delete\" name=\"btn-delete\"/>
                    <input type=\"button\" value=\"Cetak\" id=\"btn-print".$tab."\" name=\"btn-print\" />
                    <input type=\"button\" value=\"Cetak Disposisi\" id=\"btn-disposisi".$tab."\" name=\"btn-disposisi\" />
                </div>
                <div style='float:right'>
                    Pencarian  
                    <input type=\"text\" class=\"srcTgl\" name=\"srcTglAwal\" id=\"srcTglAwal-".$tab."\" size=\"10\" maxlength=\"10\" value=\"".$srcTglAwal."\" placeholder=\"Tgl Awal\"/> s/d
                    <input type=\"text\" class=\"srcTgl\" name=\"srcTglAkhir\" id=\"srcTglAkhir-".$tab."\" size=\"10\" maxlength=\"10\" value=\"".$srcTglAkhir."\" placeholder=\"Tgl Akhir\"/>                        
                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(".$tab.");\" id=\"srcNomor-".$tab."\" name=\"srcNomor\" size=\"30\" value=\"".$srcNomor."\" placeholder=\"Nomor Berkas / NOP\"/>
                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(".$tab.");\" id=\"srcNama-".$tab."\" name=\"srcNama\" size=\"30\" value=\"".$srcNama."\" placeholder=\"Nama\"/>
                    <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(".$tab.")\"/>\n
                </div>
            </div>
            </form>\n";

            $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
            $HTML .= "\t<tr>\n";
            $HTML .= "\t\t<td class=\"tdheader\"><div class=\"container\">
                    <div class=\"all\"><input name=\"checkHapusAll".$tab."\" id=\"checkHapusAll".$tab."\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Nomor </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Nama WP </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Kecamatan </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> ".$appConfig['LABEL_KELURAHAN']." </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> NOP </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Jenis Berkas </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Terima </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Penerima </td>\n";
            $HTML .= "\t</tr>\n";
			
            return $HTML;
	}
	
	public function headerContentDalamProses() {
            global $find,$a,$m,$arConfig,$tab,$srcTglAwal,$srcTglAkhir,$srcNomor,$srcNama, $isAdminLoket;
            $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
            $HTML .= "
            <div style='overflow:auto;'>
                <div style='float:left;'>
                    <input type=\"button\" value=\"Cetak\" id=\"btn-print".$tab."\" name=\"btn-print\" />
					<input type=\"button\" value=\"Cetak Disposisi\" id=\"btn-disposisi".$tab."\" name=\"btn-disposisi\" />
                </div>
                <div style='float:right'>
                    Pencarian  
                    <input type=\"text\" class=\"srcTgl\" name=\"srcTglAwal\" id=\"srcTglAwal-".$tab."\" size=\"10\" maxlength=\"10\" value=\"".$srcTglAwal."\" placeholder=\"Tgl Awal\"/> s/d
                    <input type=\"text\" class=\"srcTgl\" name=\"srcTglAkhir\" id=\"srcTglAkhir-".$tab."\" size=\"10\" maxlength=\"10\" value=\"".$srcTglAkhir."\" placeholder=\"Tgl Akhir\"/>                        
                    <input type=\"text\" id=\"srcNomor-".$tab."\" name=\"srcNomor\" size=\"30\" value=\"".$srcNomor."\" placeholder=\"Nomor Berkas / NOP\"/>
                    <input type=\"text\" id=\"srcNama-".$tab."\" name=\"srcNama\" size=\"30\" value=\"".$srcNama."\" placeholder=\"Nama\"/>
                    <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(".$tab.")\"/>\n
                </div>
            </div>
            </form>\n";

            $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
            $HTML .= "\t<tr>\n";
            $HTML .= "\t\t<td class=\"tdheader\"><div class=\"container\">
                    <div class=\"all\"><input name=\"checkHapusAll".$tab."\" id=\"checkHapusAll".$tab."\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Nomor </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Nama WP </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Kecamatan </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Kelurahan </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> NOP </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Jenis Berkas </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Terima </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Penerima </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Status </td>\n";
            if ($tab == 1){
                if($isAdminLoket) $HTML .= "\t\t<td class=\"tdheader\"> Aksi </td>\n";
            }
            $HTML .= "\t</tr>\n";
			
            return $HTML;
	}
	
	public function headerContentSelesai() {
            global $find,$a,$m,$arConfig,$tab,$srcTglAwal,$srcTglAkhir,$srcNomor,$srcNama;
            $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
            $HTML .= "
            <div style='overflow:auto;'>
                <div style='float:right'>
                    Pencarian  
                    <input type=\"text\" class=\"srcTgl\" name=\"srcTglAwal\" id=\"srcTglAwal-".$tab."\" size=\"10\" maxlength=\"10\" value=\"".$srcTglAwal."\" placeholder=\"Tgl Awal\"/> s/d
                    <input type=\"text\" class=\"srcTgl\" name=\"srcTglAkhir\" id=\"srcTglAkhir-".$tab."\" size=\"10\" maxlength=\"10\" value=\"".$srcTglAkhir."\" placeholder=\"Tgl Akhir\"/>                        
                    <input type=\"text\" id=\"srcNomor-".$tab."\" name=\"srcNomor\" size=\"30\" value=\"".$srcNomor."\" placeholder=\"Nomor Berkas / NOP\"/>
                    <input type=\"text\" id=\"srcNama-".$tab."\" name=\"srcNama\" size=\"30\" value=\"".$srcNama."\" placeholder=\"Nama\"/>
                    <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(".$tab.")\"/>\n
                </div>
            </div>
            </form>\n";

            $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
            $HTML .= "\t<tr>\n";
            $HTML .= "\t\t<td class=\"tdheader\"><div class=\"container\">
                    </div></td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Nomor </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Nama WP </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Kecamatan </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Kelurahan </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> NOP </td> \n";
            $HTML .= "\t\t<td class=\"tdheader\"> Jenis Berkas </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Terima </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Penerima </td>\n";
            $HTML .= "\t</tr>\n";
			
            return $HTML;
	}
	
	public function displayDataNotaris () {
		echo "<div class=\"ui-widget consol-main-content\">\n";
		echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
		echo $this->headerContent();
		echo "\t</div>\n";
		echo "\t<div class=\"ui-widget-header consol-main-content-footer\" align=\"right\">  \n";
		echo $this->paging();
		echo "</div>\n";
	}
	
	function paging() {
		global $a,$m,$n,$tab,$page,$np,$perpage,$defaultPage,$totalrows;
		
		$params = "a=".$a."&m=".$m;
		
		$html = "<div>";
		$row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
		$html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

		if ($page != 1) {
			//$page--;
			$html .= "&nbsp;<a onclick=\"setPage(".$tab.",'0')\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $totalrows ) {
			//$page++;
			$html .= "&nbsp;<a onclick=\"setPage(".$tab.",'1')\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}
        
        function getQueryDalamProses(){
            return "
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, '' AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, '0' AS CPM_TRAN_STATUS FROM cppmod_pbb_services BS 
                WHERE BS.CPM_STATUS IN (1, 2, 3, 5, 6) AND (BS.CPM_TYPE != '1' AND BS.CPM_TYPE != '2')
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN ,TRANMAIN.CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                JOIN cppmod_pbb_tranmain TRANMAIN ON TRANMAIN.CPM_TRAN_SPPT_DOC_ID=SPPT.CPM_SPPT_DOC_ID
                WHERE BS.CPM_STATUS IN (1, 2, 3, 5, 6) and (BS.CPM_TYPE = '1') AND TRANMAIN.CPM_TRAN_FLAG='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, '0' AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                WHERE BS.CPM_STATUS IN (1, 2, 3, 5, 6) and (BS.CPM_TYPE = '1')
                AND NEW.CPM_NEW_NOP is NULL
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN,10 AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                WHERE BS.CPM_STATUS IN (4) and (BS.CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN,10 AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                WHERE BS.CPM_STATUS IN (4) and (BS.CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, TRANMAIN.CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                JOIN cppmod_pbb_sppt SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                JOIN cppmod_pbb_tranmain TRANMAIN ON TRANMAIN.CPM_TRAN_SPPT_DOC_ID=SPPT.CPM_SPPT_DOC_ID
                WHERE BS.CPM_STATUS IN (1, 2, 3, 5, 6) and (BS.CPM_TYPE = '2') AND TRANMAIN.CPM_TRAN_FLAG='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, '0' AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                WHERE CPM_STATUS IN (1, 2, 3, 5, 6) and (CPM_TYPE = '2')
                AND NEW.CPM_SP_NOP is NULL
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, 10 AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, 10 AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN='0'
                    ";
            
        }
        
        function getQuerySelesai(){
            return "
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, 
                TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN FROM cppmod_pbb_services BS LEFT JOIN 
                cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN LEFT JOIN 
                cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN LEFT JOIN
                cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID
                WHERE CPM_STATUS IN (4) AND (CPM_TYPE != '1' AND CPM_TYPE != '2')
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
                    ";
            
        }
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";

$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab = $q->tab;
$uname = $q->u;
$uid = $q->uid;

$srcTglAwal  = @isset($_REQUEST['srcTglAwal']) ? $_REQUEST['srcTglAwal'] :'';
$srcTglAkhir  = @isset($_REQUEST['srcTglAkhir']) ? $_REQUEST['srcTglAkhir'] :'';
$srcNomor  = @isset($_REQUEST['srcNomor']) ? $_REQUEST['srcNomor'] :'';
$srcNama  = @isset($_REQUEST['srcNama']) ? $_REQUEST['srcNama'] :'';
 
$arrType = array(1 => "OP Baru", 
                2 => "Pemecahan", 
                3 => "Penggabungan",
                4 => "Mutasi",
                5 => "Perubahan Data",
                6 => "Pembatalan",
                7 => "Duplikat",
                8 => "Penghapusan",
                9 => "Pengurangan",
                10 => "Keberatan"
            );

			

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
$modNotaris = new  TaxService(1,$uname);
$perpage = $appConfig['ITEM_PER_PAGE'];
$defaultPage = 1;


$isAdminLoket = false;
if($tab == 1){
    $userRole = $User->GetUserRole($uid, $a);
    $arrUserRole = explode(',',$arConfig['role_id_admin_loket']);
    if (in_array($userRole,$arrUserRole)) $isAdminLoket = true;
}

//set new page
if(isset($_SESSION['stPelayanan'])){
    if($_SESSION['stPelayanan'] != $tab){
        $_SESSION['stPelayanan'] = $tab;
        $find = "";
        $page = 1;
        $np = 1;
        $srcTglAwal  = '';
        $srcTglAkhir  = '';
        $srcNomor  = '';
        $srcNama  = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
}else{
    $_SESSION['stPelayanan'] = $tab;
}

$modNotaris->displayDataNotaris();

function convertDate($date, $delimiter='-'){
	if($date == null || $date == '') return '';
	
	$tmp = explode($delimiter, $date);
	return $tmp[2].$delimiter.$tmp[1].$delimiter.$tmp[0];
}

?>
<script type="text/javascript">
    $(document).ready(function(){
        var axx = '<?php echo base64_decode($a)?>';
            
        $( ".srcTgl" ).datepicker({dateFormat:'dd-mm-yy'});
        $( "input:submit, input:button").button();
        $("input:checkbox[name='checkHapusAll0']").change(function(){
            if($(this).is(":checked")){
                $("input:checkbox[name='check-all0\\[\\]']").each(function(){
                    $(this).attr("checked",true);
                });
            }else{
                $("input:checkbox[name='check-all0\\[\\]']").each(function(){
                    $(this).attr("checked",false);
                });            
            }
        })
        
        $("input:checkbox[name='checkHapusAll1']").change(function(){
            if($(this).is(":checked")){
                $("input:checkbox[name='check-all1\\[\\]']").each(function(){
                    $(this).attr("checked",true);
                });
            }else{
                $("input:checkbox[name='check-all1\\[\\]']").each(function(){
                    $(this).attr("checked",false);
                });            
            }
        })
         
        $("#btn-delete").click(function(){
            var arrSvcId = new Array();
            var i = 0;
            var konfHapus = confirm("Yakin data akan dihapus?");
            
            if(konfHapus){
                $("input:checkbox[name='check-all0\\[\\]']").each(function(){
                    if($(this).is(":checked")){
                        arrSvcId[i] = $(this).val();
                        i++;
                    }
                });                        
                $.ajax({
                   type: "POST",
                   url: "./view/PBB/loket/svc-pbb-penerimaan.php",
                   data: "task=delete&arrSvcId="+arrSvcId.toString(),
                   success: function(msg){
                        $( "#tabsContent" ).tabs('load', 0);
                   }
                 });                        
            }
         });
		 
        $("#btn-send").click(function(){
		 
            var arrSvcId = new Array();
            var i = 0;
            var konfHapus = confirm("Yakin data akan dikirim?");
            
            if(konfHapus){
                $("input:checkbox[name='check-all0\\[\\]']").each(function(){
                    if($(this).is(":checked")){
                        arrSvcId[i] = $(this).val();
                        i++;
                    }
                });                        
                $.ajax({
                   type: "POST",
                   url: "./view/PBB/loket/svc-pbb-penerimaan.php",
                   data: "task=send&arrSvcId="+arrSvcId.toString(),
                   success: function(msg){
                        $( "#tabsContent" ).tabs('load', 0);
                   }
                 });                        
            }
         });
		
        $("#btn-print0").click(function(){

                x=0;

                $("input:checkbox[name='check-all0\\[\\]']").each(function(){
                    if($(this).is(":checked")){
                            printToPDF2($(this).val());
                            x++;
                    }
                });
                if(x==0){
                    alert ("Belum ada data yang dipilih!");
                }
        });
		
        $("#btn-disposisi0").click(function(){

            x=0;

            $("input:checkbox[name='check-all0\\[\\]']").each(function(){
                    if($(this).is(":checked")){
                            printToPDF($(this).val());
                            x++;
                    }
            });
            if(x==0){
                    alert ("Belum ada data yang dipilih!");
            }
        });
		
        $("#btn-print1").click(function(){

                x=0;

                $("input:checkbox[name='check-all1\\[\\]']").each(function(){
                    if($(this).is(":checked")){
                            printToPDF2($(this).val());
                            x++;
                    }
                });
                if(x==0){
                    alert ("Belum ada data yang dipilih!");
                }
        });
		
        $("#btn-disposisi1").click(function(){

            x=0;

            $("input:checkbox[name='check-all1\\[\\]']").each(function(){
                    if($(this).is(":checked")){
                            printToPDF($(this).val());
                            x++;
                    }
            });
            if(x==0){
                    alert ("Belum ada data yang dipilih!");
            }
        });
		
        
    });
	
	function printCommand(appID, id) {
		var params = {appID:appID, svcId:id};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		  Ext.Ajax.request({
			   url: 'svr/service/svc-service-print.php',
			   timeout:100000,
			   success: printCommandSuccess,
			   failure: printException,
			   params: { req:params}
			});
		showMask();
	}
	
	function printDataToPDF (d) {
		var dt = getCheckedValue(document.getElementsByName('check-all'),d);
		var s = "";
		if (dt!="") {
			s = Ext.util.JSON.encode(dt);
		}
		//console.log(s);
		printToPDF(s)
	}

	
	function printToPDF(id) {
		var params = {svcId:id, appId:'<?php echo $a; ?>', uname:'<?php echo $uname;?>'};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
			window.open('./function/PBB/loket/svc-print-disposisi.php?q='+params, '_newtab');
	}
	
	function printDataToPDF2 (d) {
		var dt = getCheckedValue(document.getElementsByName('check-all'),d);
		var s = "";
		if (dt!="") {
			s = Ext.util.JSON.encode(dt);
		}
		//console.log(s);
		printToPDF2(s)
	}

	
	function printToPDF2(id) {
		var params = {svcId:id, appId:'<?php echo $a; ?>', uname:'<?php echo $uname;?>'};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
			window.open('./function/PBB/loket/svc-print-buktipenerimaan.php?q='+params, '_newtab');
	}
        
        function kembalikanKeLoket(id) {
            
            var konfHapus = confirm("Yakin data akan dihapus?");
            
            if(konfHapus){
                $.ajax({
                   type: "POST",
                   url: "./view/PBB/loket/svc-pbb-penerimaan.php",
                   data: "task=delete&arrSvcId="+id,
                   success: function(msg){
                        $( "#tabsContent" ).tabs('load', 1);
                   }
                 });                        
            }
         }
</script>