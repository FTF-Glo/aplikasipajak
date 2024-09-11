<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';
//require_once($sRootPath."inc/payment/c8583.php");
//require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-dms-c.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>";
//echo "<script language=\"javascript\" src=\"view/PBB/loket/mod-tax-service-print.js\" type=\"text/javascript\"></script>";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
    exit(1);
}


class TaxService
{
    private $arrayStatus = array('1' => 'Staf', '2' => 'Verifikasi', '3' => 'Persetujuan', '5' => 'Ditolak Verifikasi', '6' => 'Ditolak Persetujuan');
    private $arrayStatusOPBaru = array('0' => 'Staf', '1' => 'Verifikasi 1', '2' => 'Verifikasi 2', '3' => 'Verifikasi 3', '10' => 'Penetapan', '5' => 'Ditolak Verifikasi', '6' => 'Ditolak Persetujuan');

    function __construct($userGroup, $user)
    {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }

    function getTotalRows($query)
    {
        global $DBLink;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_fetch_array($res);
        return $row['TOTALROWS'];
    }

    function mysql2json($mysql_result, $name)
    {
        $json = "{'$name': [";
        $field_names = array();
        $fields = mysqli_num_fields($mysql_result);
        for ($x = 0; $x < $fields; $x++) {
            $field_name = mysqli_fetch_field($mysql_result);
            if ($field_name) {
                $field_names[$x] = $field_name->name;
            }
        }
        $rows = mysqli_num_rows($mysql_result);
        for ($x = 0; $x < $rows; $x++) {
            $row = mysqli_fetch_array($mysql_result);
            $json .= "{";
            for ($y = 0; $y < count($field_names); $y++) {
                $json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
                if ($y == count($field_names) - 1) {
                    $json .= "";
                } else {
                    $json .= ",";
                }
            }
            if ($x == $rows - 1) {
                $json .= "}";
            } else {
                $json .= "},";
            }
        }
        $json .= "]}";
        return ($json);
    }

    function getDocument(&$dat)
    {
        global $DBLink, $json, $a, $m, $tab, $find, $page, $totalrows, $perpage, $arConfig, $arrType, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $isAdminLoket;

        $srcTxt = $find;
        $where = " WHERE CPM_STATUS = 0 ";
        if ($srcNama != "") $where .= " AND BS.CPM_WP_NAME LIKE '%" . $srcNama . "%' ";
        if ($srcNomor != "") $where .= " AND (BS.CPM_ID LIKE '%" . $srcNomor . "%' OR NEW.CPM_NEW_NOP LIKE '%" . $srcNomor . "%'  OR BS.CPM_OP_NUMBER LIKE '%" . $srcNomor . "%' ) ";
        if ($srcTglAwal != "") $where .= " AND BS.CPM_DATE_RECEIVE >= '" . convertDate($srcTglAwal) . "' ";
        if ($srcTglAkhir != "") $where .= " AND BS.CPM_DATE_RECEIVE <= '" . convertDate($srcTglAkhir) . "' ";

        $query = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $query = "SELECT 
                        BS.CPM_ID, 
                        BS.CPM_WP_NAME, 
                        BS.CPM_WP_ADDRESS, 
                        BS.CPM_OP_ADDRESS, 
                        BS.CPM_OP_NUMBER, 
                        NEW.CPM_NEW_NOP, 
                        BS.CPM_TYPE, 
                        BS.CPM_STATUS, 
                        BS.CPM_DATE_RECEIVE, 
                        BS.CPM_RECEIVER, 
                        TKEC.CPC_TKC_KECAMATAN, 
                        TKEL.CPC_TKL_KELURAHAN 
                    FROM cppmod_pbb_services BS 
                    LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                    LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                    LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                    $where 
                    ORDER BY CPM_DATE_RECEIVE DESC 
                    LIMIT " . $hal . "," . $perpage;
        $qry = "SELECT COUNT(*) AS TOTALROWS 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                $where ";

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        $totalrows = $this->getTotalRows($qry);
        $d =  $json->decode($this->mysql2json($res, "data"));
        $HTML = $startLink = $endLink = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m . "&f=" . $arConfig['form_input'] . "&tab=" . $tab;

        if (count($data->data) > 0) {
            for ($i = 0; $i < count($data->data); $i++) {
                $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
                $HTML .= "<div class=container><tr>";
                $HTML .= "<td class=$class align=center>";
                if ($tab == '0' || $tab == '1')
                    $HTML .= '<input name="check-all'.$tab.'[]" type="checkbox" value="'.$data->data[$i]->CPM_ID.'" />';
                $HTML .= "</td>";
                $HTML .= "<td class=$class align=center><a style='text-decoration:underline' href=\"main.php?param=" . base64_encode($params . "&jnsBerkas=" . $data->data[$i]->CPM_TYPE . "&svcid=" . $data->data[$i]->CPM_ID) . "\">" . $data->data[$i]->CPM_ID . "</a></td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_NAME . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_ADDRESS . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_ADDRESS . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPC_TKC_KECAMATAN . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPC_TKL_KELURAHAN . "</td>";
                if ($data->data[$i]->CPM_TYPE == '1')
                    $HTML .= "<td class=$class>" . $data->data[$i]->CPM_NEW_NOP . "</td>";
                else $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_NUMBER . "</td>";

                $HTML .= "<td class=$class align=center>" . $arrType[$data->data[$i]->CPM_TYPE] . "</td>";
                $HTML .= "<td class=$class align=center>" . convertDate($data->data[$i]->CPM_DATE_RECEIVE) . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_RECEIVER . "</td>";

                if ($tab == 1) {
                    if ($isAdminLoket) {
                        if ($data->data[$i]->CPM_STATUS == '1' || ($data->data[$i]->CPM_STATUS == '3' && $data->data[$i]->CPM_TYPE == '7')) {
                            $HTML .= "<td class=$class><input type=button value=\"Hapus\" title=\"Hapus data loket\" onclick=\"kembalikanKeLoket('" . $data->data[$i]->CPM_ID . "');\"/></td>";
                        } else $HTML .= "<td class=$class></td>";
                    }
                }
                $HTML .= "</tr></div>";
            }
            $dat = $HTML;
            return true;
        } else {
            return false;
        }
    }

    function getDocumentDalamProses(&$dat)
    {
        global $DBLink, $json, $a, $m, $tab, $find, $page, $totalrows, $perpage, $arConfig, $arrType, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $isAdminLoket, $srcStatus;

        $srcTxt = $find;
        $whereClause = array();
        $where = " ";

        if ($srcNama != "") $whereClause[] = " TBL.CPM_WP_NAME LIKE '%" . $srcNama . "%' ";
        if ($srcNomor != "") $whereClause[] = " (TBL.CPM_ID LIKE '%" . $srcNomor . "%' OR TBL.CPM_NEW_NOP LIKE '%" . $srcNomor . "%'  OR TBL.CPM_OP_NUMBER LIKE '%" . $srcNomor . "%' ) ";
        if ($srcTglAwal != "") $whereClause[] = " TBL.CPM_DATE_RECEIVE >= '" . convertDate($srcTglAwal) . "' ";
        if ($srcTglAkhir != "") $whereClause[] = " TBL.CPM_DATE_RECEIVE <= '" . convertDate($srcTglAkhir) . "' ";
        if ($srcStatus != "") $whereClause[] = " TBL.CPM_STATUS = '{$srcStatus}' ";

        if ($whereClause) $where = " WHERE " . join('AND', $whereClause);
        $query = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $query = "SELECT TBL.*, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN FROM ( " . $this->getQueryDalamProses() . " ) TBL  
				LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = TBL.CPM_OP_KECAMATAN  
				LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = TBL.CPM_OP_KELURAHAN  
				$where ORDER BY TBL.CPM_DATE_RECEIVE DESC LIMIT " . $hal . "," . $perpage;
                // print_r($query);exit;
    
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM (
				" . $this->getQueryDalamProses() . ") TBL $where ";

                

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        $totalrows = $this->getTotalRows($qry);
        
        $d =  $json->decode($this->mysql2json($res, "data"));
        $HTML = $startLink = $endLink = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m . "&f=" . $arConfig['form_input'] . "&tab=" . $tab;

        if (count($data->data) > 0) {
            for ($i = 0; $i < count($data->data); $i++) {
                $statusDokumen = '';
                if ($data->data[$i]->CPM_TYPE == '1' || $data->data[$i]->CPM_TYPE == '2') {
                    $statusDokumen = $this->arrayStatusOPBaru[$data->data[$i]->CPM_TRAN_STATUS];
                } else {
                    $statusDokumen = $this->arrayStatus[$data->data[$i]->CPM_STATUS];
                }
                $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
                $HTML .= "<div class=\"container\"><tr>";
                $HTML .= "<td class=$class align=center>";
                if ($tab == '0' || $tab == '1')
                    $HTML .= "<input id=\"\" name=\"check-all" . $tab . "[]\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_ID . "\" />";
                $HTML .= "</td>";
                $HTML .= "<td class=$class align=center><a style='text-decoration: underline;' href=\"main.php?param=" . base64_encode($params . "&svcid=" . $data->data[$i]->CPM_ID) . "\">" . $data->data[$i]->CPM_ID . "</a></td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_NAME . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_ADDRESS . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_ADDRESS . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPC_TKC_KECAMATAN . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPC_TKL_KELURAHAN . "</td>";
                if ($data->data[$i]->CPM_TYPE == '1')
                    $HTML .= "<td class=$class>" . $data->data[$i]->CPM_NEW_NOP . "</td>";
                else $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_NUMBER . "</td>";

                $HTML .= "<td class=$class align=center>" . $arrType[$data->data[$i]->CPM_TYPE] . "</td>";
                $HTML .= "<td class=$class align=center>" . convertDate($data->data[$i]->CPM_DATE_RECEIVE) . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_RECEIVER . "</td>";
                $HTML .= "<td class=$class>" . $statusDokumen . "</td>";

                if ($tab == 1) {
                    if ($isAdminLoket) {
                        if ($data->data[$i]->CPM_STATUS == '1' || ($data->data[$i]->CPM_STATUS == '3' && $data->data[$i]->CPM_TYPE == '7')) {
                            $HTML .= "<td class=$class><input type=\"button\" value=\"Hapus\" title=\"Hapus data loket\" onclick=\"kembalikanKeLoket('" . $data->data[$i]->CPM_ID . "');\"/></td>";
                        } else $HTML .= "<td class=$class></td>";
                    }
                }
                $HTML .= "</tr></div>";
            }
            $dat = $HTML;
            return true;
        } else {
            return false;
        }
    }

    function getDocumentSelesai(&$dat)
    {
        global $DBLink, $json, $a, $m, $tab, $find, $page, $totalrows, $perpage, $arConfig, $arrType, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $isAdminLoket, $srcTahun;

        

        $srcTxt = $find;
        $whereClause = array(); 
        $where = " ";

        if ($srcNama != "") $whereClause[] = " CPM_WP_NAME LIKE '%" . $srcNama . "%' ";
        if ($srcNomor != "") $whereClause[] = " (CPM_ID LIKE '%" . $srcNomor . "%' OR CPM_NEW_NOP LIKE '%" . $srcNomor . "%'  OR CPM_OP_NUMBER LIKE '%" . $srcNomor . "%' ) ";
        if ($srcTglAwal != "") $whereClause[] = " CPM_DATE_RECEIVE >= '" . convertDate($srcTglAwal) . "' ";
        if ($srcTglAkhir != "") $whereClause[] = " CPM_DATE_RECEIVE <= '" . convertDate($srcTglAkhir) . "' ";
        if ($srcTahun != "") $whereClause[] = " CPM_SPPT_YEAR = '" . $srcTahun . "' ";

        if ($whereClause) $where = " WHERE " . join('AND', $whereClause);
        $query = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $query = "SELECT * FROM ( " . $this->getQuerySelesai() . " ) TBL 
                            $where ORDER BY CPM_DATE_RECEIVE DESC LIMIT " . $hal . "," . $perpage;

        $qry = "SELECT COUNT(*) AS TOTALROWS FROM (
                        " . $this->getQuerySelesai() . ") TBL $where ";

        $res = mysqli_query($DBLink, $query); 

        if ($res === false) {
            return false;
        }
        //echo $query;
        $totalrows = $this->getTotalRows($qry);
        $d =  $json->decode($this->mysql2json($res, "data"));
        $HTML = $startLink = $endLink = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m . "&f=" . $arConfig['form_input'] . "&tab=" . $tab;

        if (count($data->data) > 0) {
            for ($i = 0; $i < count($data->data); $i++) {
                $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
                $HTML .= "<div class=\"container\"><tr>";
                $HTML .= "<td class=$class align=center>";
                if ($tab == '0' || $tab == '1' || $tab == '2')
                    $HTML .= "<input id=\"\" name=\"check-all" . $tab . "[]\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_ID . "\" />";
                $HTML .= "</td>";
                $HTML .= "<td class=$class align=center><a style='text-decoration: underline;' href=\"main.php?param=" . base64_encode($params . "&svcid=" . $data->data[$i]->CPM_ID) . "\">" . $data->data[$i]->CPM_ID . "</a></td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_NAME . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_ADDRESS . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_ADDRESS . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPC_TKC_KECAMATAN . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPC_TKL_KELURAHAN . "</td>";
                if ($data->data[$i]->CPM_TYPE == '1')
                    $HTML .= "<td class=$class>" . $data->data[$i]->CPM_NEW_NOP . "</td>";
                else $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_NUMBER . "</td>";

                $HTML .= "<td class=$class align=center>" . $arrType[$data->data[$i]->CPM_TYPE] . "</td>";
                $HTML .= "<td class=$class align=center>" . convertDate($data->data[$i]->CPM_DATE_RECEIVE) . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_RECEIVER . "</td>";

                if ($tab == 1) {
                    if ($isAdminLoket) {
                        if ($data->data[$i]->CPM_STATUS == '1' || ($data->data[$i]->CPM_STATUS == '3' && $data->data[$i]->CPM_TYPE == '7')) {
                            $HTML .= "<td class=$class><input type=\"button\" value=\"Hapus\" title=\"Hapus data loket\" onclick=\"kembalikanKeLoket('" . $data->data[$i]->CPM_ID . "');\"/></td>";
                        } else $HTML .= "<td class=$class></td>";
                    }
                }
                $HTML .= "</tr></div>";
            }
            $dat = $HTML;
            return true;
        } else {
            return false;
        }
    }

    function getDocumentLaporanHarian(&$dat)
    {
        global $DBLink, $json, $a, $m, $tab, $find, $page, $totalrows, $perpage, $arConfig, $arrType, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $isAdminLoket, $srcJnsBerkas;

        $srcTxt = $find;
        $whereClause = array();
        $where = " ";

        // if ($srcNama != "") $whereClause[] = " CPM_WP_NAME LIKE '%".$srcNama."%' ";
        // if ($srcNomor != "") $whereClause[] = " (CPM_ID LIKE '%".$srcNomor."%' OR CPM_NEW_NOP LIKE '%".$srcNomor."%'  OR CPM_OP_NUMBER LIKE '%".$srcNomor."%' ) ";
        if ($srcJnsBerkas != '') $whereClause[] = " CPM_TYPE = '" . $srcJnsBerkas . "' ";
        if ($srcTglAwal != "") $whereClause[] = " CPM_DATE_RECEIVE >= '" . convertDate($srcTglAwal) . "' ";
        if ($srcTglAkhir != "") $whereClause[] = " CPM_DATE_RECEIVE <= '" . convertDate($srcTglAkhir) . "' ";

        if ($whereClause) $where = " WHERE " . join('AND', $whereClause);

        $query = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

        $query = "SELECT
								A.*, B.CPC_TKC_KECAMATAN, C.CPC_TKL_KELURAHAN, IFNULL(D.CPM_WP_NO_HP, A.CPM_WP_HANDPHONE) AS CPM_WP_HANDPHONE
							FROM
								cppmod_pbb_services A
							JOIN cppmod_tax_kecamatan B ON A.CPM_OP_KECAMATAN=B.CPC_TKC_ID
							JOIN cppmod_tax_kelurahan C ON A.CPM_OP_KELURAHAN=C.CPC_TKL_ID 
                            LEFT JOIN cppmod_pbb_wajib_pajak D ON A.CPM_WP_NO_KTP = D.CPM_WP_ID
                            $where ORDER BY CPM_TYPE,CPM_ID ASC LIMIT " . $hal . "," . $perpage;
                            

        $qry = "SELECT COUNT(*) AS TOTALROWS FROM
                        cppmod_pbb_services A
						JOIN cppmod_tax_kecamatan B ON A.CPM_OP_KECAMATAN=B.CPC_TKC_ID
						JOIN cppmod_tax_kelurahan C ON A.CPM_OP_KELURAHAN=C.CPC_TKL_ID 
                        $where ";
        $res = mysqli_query($DBLink, $query);

        if ($res === false) {
            return false;
        }

        $totalrows = $this->getTotalRows($qry);
        $d =  $json->decode($this->mysql2json($res, "data"));
        $HTML = $startLink = $endLink = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m . "&f=" . $arConfig['form_input'] . "&tab=" . $tab;

        if (count($data->data) > 0) {
            for ($i = 0; $i < count($data->data); $i++) {
                $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
                $HTML .= "<div class=\"container\"><tr>";
                $HTML .= "<td class=$class align=center><a style='text-decoration: underline;' href=\"main.php?param=" . base64_encode($params . "&svcid=" . $data->data[$i]->CPM_ID) . "\">" . $data->data[$i]->CPM_ID . "</a></td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_NAME . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_HANDPHONE . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_ADDRESS . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_ADDRESS . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPC_TKC_KECAMATAN . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPC_TKL_KELURAHAN . "</td>";
                if ($data->data[$i]->CPM_TYPE == '1')
                    $HTML .= "<td class=$class>" . (isset($data->data[$i]->CPM_NEW_NOP) ? $data->data[$i]->CPM_NEW_NOP : '') . "</td>";
                else $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_NUMBER . "</td>";

                $HTML .= "<td class=$class align=center>" . $arrType[$data->data[$i]->CPM_TYPE] . "</td>";
                $HTML .= "<td class=$class align=center>" . convertDate($data->data[$i]->CPM_DATE_RECEIVE) . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_RECEIVER . "</td>";

                if ($tab == 1) {
                    if ($isAdminLoket) {
                        if ($data->data[$i]->CPM_STATUS == '1' || ($data->data[$i]->CPM_STATUS == '3' && $data->data[$i]->CPM_TYPE == '7')) {
                            $HTML .= "<td class=$class><input type=\"button\" value=\"Hapus\" title=\"Hapus data loket\" onclick=\"kembalikanKeLoket('" . $data->data[$i]->CPM_ID . "');\"/></td>";
                        } else $HTML .= "<td class=$class></td>";
                    }
                }
                $HTML .= "</tr></div>";
            }
            $dat = $HTML;
            return true;
        } else {
            return false;
        }
    }

    public function headerContent()
    {
        global $find, $a, $m, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $srcJnsBerkas;
          
        $HTML = "";
        if ($tab == 1) $HTML = $this->headerContentDalamProses();
        elseif ($tab == 2) $HTML = $this->headerContentSelesai();
        elseif ($tab == 3) $HTML = $this->headerContentLaporanHarian();
        else $HTML = $this->headerContentPenerimaan();

        if ($tab == 1) $this->getDocumentDalamProses($dt);
        elseif ($tab == 2) $this->getDocumentSelesai($dt);
        elseif ($tab == 3) $this->getDocumentLaporanHarian($dt);
        else $this->getDocument($dt);

        if ($dt) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=11 align=center>Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>";
        return $HTML;
    }

    public function headerContentPenerimaan()
    {
        global $find, $a, $m, $arConfig, $appConfig, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $uname, $userRole;
		
        $params = "a=" . $a . "&m=" . $m;
        $startLink = "";
        $endLink = "</a>";

        $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= " 
        <p style=\"margin-bottom:20px; display:flex; align-items:center;justify-content:end;\">
            <button class=\"btn btn-primary\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapsAksi\" style=\"border-radius:8px; margin-right:10px\"   aria-expanded=\"false\" aria-controls=\"collapsAksi\">
               Aksi
            </button>
            <button class=\"btn btn-primary\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapseFilter\"  style=\"border-radius:8px;\"  aria-expanded=\"false\" aria-controls=\"collapseFilter\">
               Filter
            </button>
        </p>
        <div class=\"collapse\" id=\"collapsAksi\">
            <div class=\"card card-body\">
                <div class='col-md-12' style=\"margin-left:-18px;\">";
                    if (preg_match("/$uname/i", $appConfig['ROLE_LOKET'])) {
                        if ($userRole != 'rm7') {
                            $HTML .= "<button value=\"Kirim\" class=\"btn btn-primary btn-orange\" id=\"btn-send\" name=\"btn-send\">Kirim</button>";
                        }
                    }
                    // var_dump($userRole);exit;
                    if($userRole != 'rmKecamatan' && $userRole != 'rm3' && $userRole != 'rm7' && $userRole != 'rmPenetapan'){
                        $butKec = "<button value=\"Cetak Disposisi\" class=\"btn btn-primary btn-blue\" id=\"btn-disposisi" . $tab . "\" name=\"btn-disposisi\">Cetak Disposisi</button>";
                    }
                    if ($userRole != 'rm7' && $userRole != 'rm3') {
                        $butTamb="<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params . "&f=" . $arConfig['form_input'] . "&jnsBerkas=1") . "\">
                                    <button type=\"button\" value=\"Tambah\" class=\"btn btn-primary btn-blue\" id=\"btn-add\" name=\"btn-add\">Tambah</button>
                                </a>";
                        $butHapus = "<button value=\"Hapus\" class=\"btn btn-primary bg-maka\" id=\"btn-delete\" name=\"btn-delete\">Hapus</button>";
                        $butCetak = "<button value=\"Cetak\" class=\"btn btn-primary btn-orange\" id=\"btn-print" . $tab . "\" name=\"btn-print\">Cetak</button>";
                    }
            
                    $HTML .= "  
                        {$butTamb}
                        {$butHapus}
                        {$butCetak}
                        {$butKec}
                        <button value=\"Cetak Berita Acara OP Baru\" class=\"btn btn-primary bg-maka\" id=\"btn-berita-acara-op-baru\" name=\"btn-berita-acara-op-baru\">Cetak Berita Acara OP Baru</button>
                </div>
            </div>
        </div>
            <div class='row'>
                    <div class=\"collapse\" id=\"collapseFilter\">
                        <div class=\"card card-body\">
                            <div class='col-md-12' style=\"margin-top: 10px;\">
                                <div class=\"row\">";
                                        // <div class=\"col-md-1\" style=\"margin-top:5px\">Pencarian</div>
                                        // <div class=\"col-md-2\">
                                        //     <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAwal\" id=\"srcTglAwal-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAwal . "\" placeholder=\"Tgl Awal\"/>
                                        // </div>
                                        // <div class=\"col-md-1\" style=\"margin-top: 5px; text-align: center;\">s/d</div>
                                        // <div class=\"col-md-2\">
                                        //     <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAkhir\" id=\"srcTglAkhir-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAkhir . "\" placeholder=\"Tgl Akhir\"/>
                                        // </div>
                                        // <div class=\"col-md-2\">
                                        //     <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNomor-" . $tab . "\" name=\"srcNomor\" size=\"30\" value=\"" . $srcNomor . "\" placeholder=\"Nomor Berkas / NOP\"/>
                                        // </div>
                                        // <div class=\"col-md-2\">
                                        //     <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNama-" . $tab . "\" name=\"srcNama\" size=\"30\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/>
                                        // </div>
                                        // <div class=\"col-md-2\" style=\"margin-top: 5px;\">
                                        //     <button type=\"button\" class=\"btn btn-primary btn-blue\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button>
                                        // </div>

                                        $HTML .= "  <div class=\"form-group col-md-3\" >
                                            <label>Nomor Berkas / NOP</label>
                                            <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNomor-" . $tab . "\" name=\"srcNomor\" value=\"" . $srcNomor . "\" placeholder=\"Nomor Berkas / NOP\"/>
                                        </div>
                                            
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Nama</label>
                                            <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNama-" . $tab . "\" name=\"srcNama\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/>
                                        </div>
                          
                                        <div class=\"form-group col-md-3\">
                                            <label>Tanggal Awal </label>
                                            <div style=\"display: flex; align-items: center;\">
                                                <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAwal\" id=\"srcTglAwal-" . $tab . "\"  value=\"" . $srcTglAwal . "\" placeholder=\"Tgl Awal\" style=\"flex-grow: 1; margin-right: 10px;\"/>
                                                <button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1').val('');\" class=\"btn btn-secondary\">x</button>
                                            </div>
                                        </div>
                                        <div class=\"form-group col-md-3\">
                                            <label>Tanggal Akhir</label>
                                            <div style=\"display: flex; align-items: center;\">
                                            <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAkhir\" id=\"srcTglAkhir-" . $tab . "\" maxlength=\"10\" value=\"" . $srcTglAkhir . "\" placeholder=\"Tgl Akhir\" style=\"flex-grow: 1; margin-right: 10px;\"/>
                                                <button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2').val('');\" class=\"btn btn-secondary\">x</button>
                                            </div>
                                        </div>
                                        <div class=\" form-group col-md-12\">
                                            <button type=\"button\" class=\"btn btn-success btn-blue\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                
            </div>
        </form>";

        $HTML .= "<div class=\"row\" style=\"margin-top: 10px;\">
                    <div class=\"col-md-12\">
                        <div class=\"table-responsive\">
                            <table class=\"table table-bordered\">
                                <tr>";
        $HTML .= "<td class=tdheader>
                        <div class=\"all\">
                            <input name=\"checkHapusAll" . $tab . "\" id=\"checkHapusAll" . $tab . "\" type=\"checkbox\" value=\"\"/>
                        </div>
                    </td>";
        $HTML .= "<td class=tdheader>Nomor</td>";
        $HTML .= "<td class=tdheader>Nama</td>";
        $HTML .= "<td class=tdheader>Alamat Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>" . $appConfig['LABEL_KELURAHAN'] . "</td>";
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>Jenis Berkas</td>";
        $HTML .= "<td class=tdheader>Tanggal Terima</td>";
        $HTML .= "<td class=tdheader>Penerima</td>";
        $HTML .= "</tr>";

        return $HTML;
    }

    public function headerContentDalamProses()
    {
        global $find, $a, $m, $arConfig, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $isAdminLoket, $srcStatus, $userRole;

        $srcTglAwal = ($srcTglAwal!="")?$srcTglAwal:date("01-m-Y");
        $srcTglAkhir = ($srcTglAkhir!="")?$srcTglAkhir:date("t-m-Y");

        

        $optStatus = '<option value="" selected>Tampilkan Semua Status</option>';
        
        
        foreach ($this->arrayStatus as $key => $val) {
            $optStatus .= '<option value="' . $key . '" ' . ($srcStatus == $key ? 'selected' : '') . '>' . $val . '</option>';
        }
        
		
		if($userRole != 'rmKecamatan' && $userRole != 'rm7' && $userRole != 'rm3'){
			$butKec = "<button value=\"Cetak Disposisi\" class=\"btn btn-primary btn-blue mb5\" id=\"btn-disposisi" . $tab . "\" name=\"btn-disposisi\">Cetak Disposisi</button>";
		}
        
        if($userRole != 'rm7' && $userRole != 'rm3'){
            $butCetak = "<button value=\"Cetak\" class=\"btn btn-primary btn-orange mb5\" id=\"btn-print" . $tab . "\" name=\"btn-print\">Cetak</button>";
        }

       
        $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "
        <p style=\"margin-bottom:20px; display:flex; align-items:center;justify-content:end;\">
            <button class=\"btn btn-primary\" style=\"border-radius:8px;\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapseProses\" aria-expanded=\"false\" aria-controls=\"collapseProses\">
               Filter Data
            </button>
        </p>";
            // <div class='row'>
            //     <div class='col-md-4'>
			// 		{$butCetak}
            //         {$butKec}
            //     </div>
            // </div>

        $HTML .=   "<div class=\"collapse\" id=\"collapseProses\">
                <div class=\"card card-body\">
                    <div class='row' style='margin-top:5px'>
                        <div class='col-md-12'>
                            <div class='row'>";
                            //  <div class='col-md-1' style='margin-top: 5px'>Pencarian</div>
                            //     <div class='col-md-4'>
                            //         <div class='row'>
                            //             <div class='col-md-5'>
                            //                 <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAwal\" id=\"srcTglAwal-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAwal . "\" placeholder=\"Tgl Awal\"/>
                            //             </div>
                            //             <div class='col-md-1' style='margin-top:5px;text-align:center;padding:0px'>s/d</div>
                            //             <div class='col-md-5'>
                            //                 <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAkhir\" id=\"srcTglAkhir-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAkhir . "\" placeholder=\"Tgl Akhir\"/>
                            //             </div>
                            //         </div>
                            //     </div>

                            //     <div class='col-md-2'>
                            //         <input type=\"text\" id=\"srcNomor-" . $tab . "\" class=\"form-control\" name=\"srcNomor\" size=\"30\" value=\"" . $srcNomor . "\" placeholder=\"Nomor Berkas / NOP\"/>
                            //     </div>
                            //     <div class='col-md-2'>
                            //         <input type=\"text\" id=\"srcNama-" . $tab . "\" class=\"form-control\" name=\"srcNama\" size=\"30\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/>
                            //     </div>
                            
                            //     <!- ARD+ : menambah src untuk status -->
                            //     <div class='col-md-2'>
                            //         <select name=\"srcStatus\" class=\"form-control\" id=\"srcStatus-" . $tab . "\" >$optStatus</select>
                            //     </div>
                            //     <div class=\"col-md-1\" style=\"text-align:right\">
                            //         <button type=\"button\" value=\"Cari\" id=\"btn-src\" class=\"btn btn-primary btn-blue\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button>
                            //     </div>

                                $HTML .="       <div class=\"form-group col-md-4\" >
                                            <label>Nomor Berkas / NOP</label>
                                            <input type=\"text\" id=\"srcNomor-" . $tab . "\" class=\"form-control\" name=\"srcNomor\" value=\"" . $srcNomor . "\" placeholder=\"Nomor Berkas / NOP\"/>
                                        </div>
                                            
                                        <div class=\" form-group col-md-4\"> 
                                            <label>Nama</label>
                                            <input type=\"text\" id=\"srcNama-" . $tab . "\" class=\"form-control\" name=\"srcNama\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/>
                                        </div>

                                        <div class=\" form-group col-md-4\"> 
                                            <label>Status</label>
                                                <select name=\"srcStatus\" class=\"form-control\" id=\"srcStatus-" . $tab . "\" >$optStatus</select>    
                                        </div>
                          
                                        <div class=\"form-group col-md-4\">
                                            <label>Tanggal Awal </label>
                                            <div style=\"display: flex; align-items: center;\">
                                                <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAwal\" id=\"srcTglAwal-" . $tab . "\" maxlength=\"10\" value=\"" . $srcTglAwal . "\" placeholder=\"Tgl Awal\" style=\"flex-grow: 1; margin-right: 10px;\"/>
                                              
                                                <div class='col-md-1' style='margin-top:5px;text-align:center;padding:0px'>s/d</div>
                                            </div>
                                        </div>
                                        <div class=\"form-group col-md-4\">
                                            <label>Tanggal Akhir</label>
                                            <div style=\"display: flex; align-items: center;\">
                                            <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAkhir\" id=\"srcTglAkhir-" . $tab . "\" maxlength=\"10\" value=\"" . $srcTglAkhir . "\" placeholder=\"Tgl Akhir\" style=\"flex-grow: 1; margin-right: 10px;\"/>     
                                            </div>
                                        </div>
                                        <div class=\" form-group col-md-12\">
                                            <button type=\"button\" value=\"Cari\" id=\"btn-src\" class=\"btn btn-primary btn-blue\" style=\"margin-top:-8px\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button>
                                            {$butCetak}
                                            {$butKec}
                                        </div>
                           </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </form>";

        $HTML .= "<div class=\"row\" style=\"margin-top: 10px;\">
                    <div class=\"col-md-12\">
                        <div class=\"table-responsive\">
                            <table class=\"table table-bordered table-striped\">
                                <tr>
                                    <td class=tdheader>
                                        <div class=\"all\">
                                            <input name=\"checkHapusAll" . $tab . "\" id=\"checkHapusAll" . $tab . "\" type=\"checkbox\" value=\"\"/>
                                        </div>
                                    </td>";
        $HTML .= "<td class=tdheader>Nomor</td>"; 
        $HTML .= "<td class=tdheader>Nama</td>";
        $HTML .= "<td class=tdheader>Alamat Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>Kelurahan</td>";
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>Jenis Berkas</td>";
        $HTML .= "<td class=tdheader>Tanggal Terima</td>";
        $HTML .= "<td class=tdheader>Penerima</td>";
        $HTML .= "<td class=tdheader>Status</td>";
        if ($tab == 1) {
            if ($isAdminLoket) $HTML .= "<td class=tdheader>Aksi</td>";
        }
        $HTML .= "</tr>";

        return $HTML;
    }
    

    public function headerContentSelesai()
    {
        global $find, $a, $m, $arConfig, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $appConfig, $srcTahun;
        $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $optionTahun = '';
        $i = $appConfig['tahun_tagihan'];
        
        $optionTahun .= '<option value="">Tahun</option>';
        
        
        for ($i; $i >= 2013; $i--) {
            $optionTahun .= '<option value="' . $i . '" ' . (($srcTahun == $i) ? 'selected' : '') . ' >' . $i . '</option>';
        }
        $HTML .= "
        <p style=\"margin-bottom:20px; display:flex; align-items:center;justify-content:end;\">
        <button class=\"btn btn-primary\" style=\"border-radius:8px;\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapseSelesai\" aria-expanded=\"false\" aria-controls=\"collapseSelesai\">
           Filter Data
        </button>
    </p>
        <div class=\"collapse\" id=\"collapseSelesai\">
            <div class=\"card card-body\">
                <div class='row' style='margin-top: 5px;'>
                    <div class='col-md-12'>";
                //    <div class='row'>
                //             <div class='col-md-1'>Pencariannn</div>
                //             <div class=\"col-md-2\">
                //                 <select class=\"form-control\" name=\"srcTahun\" id=\"srcTahun-" . $tab . "\">
                //                     " . $optionTahun . "
                //                 </select>
                //             </div>
                //             <div class=\"col-md-2\">
                //                 <input type=\"text\" class=\"form-control\" class=\"srcTgl\" name=\"srcTglAwal\" id=\"srcTglAwal-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAwal . "\" placeholder=\"Tgl Awal\"/>
                //             </div>
                //             <div class=\"col-md-1\" style=\"margin-top: 5px; text-align: center;\">s/d</div>
                //             <div class=\"col-md-2\">
                //                 <input type=\"text\" class=\"form-control\" class=\"srcTgl\" name=\"srcTglAkhir\" id=\"srcTglAkhir-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAkhir . "\" placeholder=\"Tgl Akhir\"/>
                //             </div>
                //         </div>
                //         <div class=\"row\" style=\"margin-top: 5px;\">
                //             <div class='col-md-1'>&nbsp;</div>
                //             <div class=\"col-md-3\">
                //                 <input type=\"text\" class=\"form-control\" id=\"srcNomor-" . $tab . "\" name=\"srcNomor\" size=\"30\" value=\"" . $srcNomor . "\" placeholder=\"Nomor Berkas / NOP\"/>
                //             </div>
                //             <div class=\"col-md-3\">
                //                 <input type=\"text\" class=\"form-control\" id=\"srcNama-" . $tab . "\" name=\"srcNama\" size=\"30\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/>
                //             </div>
                //             <div class=\"col-md-5\" style=\"margin-top: 5px;\">
                //                 <button type=\"button\" class=\"btn btn-primary btn-orange\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button> 
                //                 <button type=\"button\" class=\"btn btn-primary btn-blue\" value=\"Ekspor ke xls\" name=\"export\" onClick=\"exportToExcel(" . $tab . ")\">Ekspor ke xls</button> 
                //                 <button type=\"button\" class=\"btn btn-primary bg-maka\" value=\"Cetak Berita Acara\" id=\"btn-berita-acara\" name=\"btn-berita-acara\">Cetak Berita Acara</button>
                //             </div>
                //         </div>

                        $HTML .="          <div class=\" form-group col-md-4\"> 
                                                <label>Tahun</label>
                                                <select class=\"form-control\" name=\"srcTahun\" id=\"srcTahun-" . $tab . "\">
                                                    " . $optionTahun . "
                                                </select>
                                            </div>
                            
                                            <div class=\"form-group col-md-4\">
                                                <label>Tanggal Awal </label>
                                                <div style=\"display: flex; align-items: center;\">
                                                    <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAwal\" id=\"srcTglAwal-" . $tab . "\" maxlength=\"10\" value=\"" . $srcTglAwal . "\" placeholder=\"Tgl Awal\" style=\"flex-grow: 1; margin-right: 10px;\" />                                               
                                                    <div class='col-md-1' style='margin-top:5px;text-align:center;padding:0px'>s/d</div>
                                                </div>
                                            </div>
                                            <div class=\"form-group col-md-4\">
                                                <label>Tanggal Akhir</label>
                                                <div style=\"display: flex; align-items: center;\">
                                                <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAkhir\" id=\"srcTglAkhir-" . $tab . "\" maxlength=\"10\" value=\"" . $srcTglAkhir . "\" placeholder=\"Tgl Akhir\" style=\"flex-grow: 1; margin-right: 10px;\"/>     
                                                </div>
                                            </div>

                                            <div class=\"form-group col-md-4\" >
                                                <label>Nomor Berkas / NOP</label>
                                                <input type=\"text\" class=\"form-control\" id=\"srcNomor-" . $tab . "\" name=\"srcNomor\" value=\"" . $srcNomor . "\" placeholder=\"Nomor Berkas / NOP\"/>
                                            </div>
                                                
                                            <div class=\" form-group col-md-4\"> 
                                                <label>Nama</label>
                                                <input type=\"text\" class=\"form-control\" id=\"srcNama-" . $tab . "\" name=\"srcNama\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/>
                                            </div>


                                            <div class=\" form-group col-md-12\">
                                                <button type=\"button\" value=\"Cari\" id=\"btn-src\" class=\"btn btn-primary btn-blue\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button>
                                                <button type=\"button\" class=\"btn btn-primary btn-blue\" value=\"Ekspor ke xls\" name=\"export\" onClick=\"exportToExcel(" . $tab . ")\">Ekspor ke xls</button> 
                                                <button type=\"button\" class=\"btn btn-primary bg-maka\" value=\"Cetak Berita Acara\" id=\"btn-berita-acara\" name=\"btn-berita-acara\">Cetak Berita Acara</button>
                                               
                                            </div>

                    </div>
                </div>
            </div>
        </div>
        </form>";

        $HTML .= "<div class=\"row\" style=\"margin-top: 10px;\">
                    <div class=\"col-md-12\">
                        <div class=\"table-responsive\">
                            <table class=\"table table-bordered\">
                                <tr>";
        $HTML .= "<td class=tdheader></td>";
        $HTML .= "<td class=tdheader>Nomor</td>";
        $HTML .= "<td class=tdheader>Nama</td>";
        $HTML .= "<td class=tdheader>Alamat Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>Kelurahan</td>";
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>Jenis Berkas</td>";
        $HTML .= "<td class=tdheader>Tanggal Terima</td>";
        $HTML .= "<td class=tdheader>Penerima</td>";
        $HTML .= "</tr>";

        return $HTML;
    }

    public function headerContentLaporanHarian()
    {
        global $find, $a, $m, $arConfig, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama, $isAdminLoket, $srcJnsBerkas;
        $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "
            <div class='row' style='margin-top: 5px;'>
                <div class='col-md-12'>
                    <div class='row'>
                        <div class='col-md-8' style='float:left'>
                            <div class='row' style='margin-bottom: 5px;'>
                                <div class='col-md-2' style='margin-top: 5px;'>Jenis Berkas</div>
                                <div class='col-md-3'>
                                    <select name=jnsBerkas class=\"form-control\" id=jnsBerkas>
                                        <option value=''>Semua</option>
                                        <option value=1 ". ($srcJnsBerkas == "1" ? 'selected' : '') .">OP Baru</option>
                                        <option value=2 ". ($srcJnsBerkas == "2" ? 'selected' : '') .">Pemecahan</option>
                                        <option value=3 ". ($srcJnsBerkas == "3" ? 'selected' : '') .">Penggabungan</option>
                                        <option value=4 ". ($srcJnsBerkas == "4" ? 'selected' : '') .">Mutasi</option>
                                        <option value=5 ". ($srcJnsBerkas == "5" ? 'selected' : '') .">Perubahan Data</option>
                                        <option value=7 ". ($srcJnsBerkas == "7" ? 'selected' : '') .">Salinan</option>
                                        <option value=8 ". ($srcJnsBerkas == "8" ? 'selected' : '') .">Penghapusan</option>
                                        <option value=9 ". ($srcJnsBerkas == "9" ? 'selected' : '') .">Pengurangan</option>
                                        <option value=10 ". ($srcJnsBerkas == "10" ? 'selected' : '') .">Keberatan</option>
                                        <option value=11 ". ($srcJnsBerkas == "11" ? 'selected' : '') .">Cetak SKNJOP</option>
                                        <option value=12". ($srcJnsBerkas == "12" ? 'selected' : '') .">Pengurangan Denda</option>
                                    </select>
                                </div>
                                <div class='col-md-3'>
                                    <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAwal\" id=\"srcTglAwal-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAwal . "\" placeholder=\"Tgl Awal\"/>
                                </div>
                                <div class=\"col-md-1\" style='margin-top: 5px;'>s/d</div>
                                <div class='col-md-3'>
                                    <input type=\"text\" class=\"srcTgl form-control\" name=\"srcTglAkhir\" id=\"srcTglAkhir-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAkhir . "\" placeholder=\"Tgl Akhir\"/>
                                </div>              
                                <!-- <input type=\"text\" id=\"srcNomor-" . $tab . "\" name=\"srcNomor\" size=\"30\" value=\"" . $srcNomor . "\" placeholder=\"Nomor Berkas / NOP\"/>
                                <input type=\"text\" class=\" form-control\" id=\"srcNama-" . $tab . "\" name=\"srcNama\" size=\"30\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/> -->
                            </div>
                        </div>
                    </div>
                    <div class=\"row\" style=\"margin-bottom: 20px;margin-top:20px\">
                        <div class='col-md-4' style='float:left'>
                            <div class=\"row\" >
                                <div class='col-md-12'>
                                    <button type=\"button\" class=\"btn btn-primary btn-orange\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button> 
                                    <button type=\"button\" class=\"btn btn-primary btn-blue\" value=\"Ekspor ke xls\" id=\"btn-to-xls" . $tab . "\" name=\"btn-to-xls\" onClick=\"exportToExcel(" . $tab . ")\">Ekspor ke xls</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>";

        $HTML .= "<div class=\"row\">
                    <div class=\"col-md-12\">
                        <div class=\"table-responsive\">
                            <table class=\"table table-bordered\">
                                <tr>";
        $HTML .= "<td class=tdheader>Nomor</td>";
        $HTML .= "<td class=tdheader>Nama</td>";
        $HTML .= "<td class=tdheader>Telp</td>";
        $HTML .= "<td class=tdheader>Alamat Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>Kelurahan</td>";
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>Jenis Berkas</td>";
        $HTML .= "<td class=tdheader>Tanggal Terima</td>";
        $HTML .= "<td class=tdheader>Penerima</td>";
        $HTML .= "</tr>";

        return $HTML;
    }

    public function displayDataNotaris()
    {
        echo "<div class=\"row\">
                <div class=\"col-md-12\">";
        echo $this->headerContent();
        echo "  </div>
                <div class=\"col-md-12\">";
        echo "      <div style=\"float: right\">";
        echo $this->paging();
        echo "      </div>
                </div>
            </div>";
    }

    function paging()
    {
        global $a, $m, $n, $tab, $page, $np, $perpage, $defaultPage, $totalrows;

        $params = "a=" . $a . "&m=" . $m;

        $html = "<div style=\"font-weight:bold\">";

        if($tab=='0'){
            $html .= 'Jumlah row : '.$totalrows;
        }else{
            $row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
            $rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
            $html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;
    
            if ($page != 1) {
                //$page--;
                $html .= "&nbsp;<a onclick=\"setPage(" . $tab . ",'0')\"><span id=\"navigator-left\"></span></a>";
            }
            if ($rowlast < $totalrows) {
                //$page++;
                $html .= "&nbsp;<a onclick=\"setPage(" . $tab . ",'1')\"><span id=\"navigator-right\"></span></a>";
            }
        }

        $html .= "</div>";
        return $html;
    }

    function getQueryDalamProses()
    {
        return "
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, '' AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, '0' AS CPM_TRAN_STATUS FROM cppmod_pbb_services BS 
                WHERE BS.CPM_STATUS IN (1, 2, 3, 5, 6) AND (BS.CPM_TYPE != '1' AND BS.CPM_TYPE != '12' AND BS.CPM_TYPE != '2')
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN ,TRANMAIN.CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                JOIN cppmod_pbb_tranmain TRANMAIN ON TRANMAIN.CPM_TRAN_SPPT_DOC_ID=SPPT.CPM_SPPT_DOC_ID
                WHERE BS.CPM_STATUS IN (1, 2, 3, 5, 6) and (BS.CPM_TYPE = '1') AND TRANMAIN.CPM_TRAN_FLAG='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, '0' AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                WHERE BS.CPM_STATUS IN (1, 2, 3, 5, 6) and (BS.CPM_TYPE = '1')
                AND NEW.CPM_NEW_NOP is NULL
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN,10 AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                WHERE BS.CPM_STATUS IN (4) and (BS.CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN,10 AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                WHERE BS.CPM_STATUS IN (4) and (BS.CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, TRANMAIN.CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                JOIN cppmod_pbb_sppt SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                JOIN cppmod_pbb_tranmain TRANMAIN ON TRANMAIN.CPM_TRAN_SPPT_DOC_ID=SPPT.CPM_SPPT_DOC_ID
                WHERE BS.CPM_STATUS IN (1, 2, 3, 5, 6) and (BS.CPM_TYPE = '2') AND TRANMAIN.CPM_TRAN_FLAG='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, '0' AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                WHERE CPM_STATUS IN (1, 2, 3, 5, 6) and (CPM_TYPE = '2')
                AND NEW.CPM_SP_NOP is NULL
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, 10 AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, 10 AS CPM_TRAN_STATUS
                FROM cppmod_pbb_services BS 
                JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN='0'
                    ";
    }
    

    function getQuerySelesai()
    {
        return "
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, 
                TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR FROM cppmod_pbb_services BS LEFT JOIN 
                cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN LEFT JOIN 
                cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN LEFT JOIN
                cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID
                WHERE CPM_STATUS IN (4) AND (CPM_TYPE != '1' AND CPM_TYPE != '2')
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_NEW_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '1') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
                UNION ALL
                SELECT BS.CPM_ID, BS.CPM_WP_NAME, CPM_WP_ADDRESS, CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE, BS.CPM_STATUS, BS.CPM_DATE_RECEIVE, BS.CPM_RECEIVER, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN,BS.CPM_SPPT_YEAR 
                FROM cppmod_pbb_services BS 
                LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN 
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN 
                LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_SID = BS.CPM_ID 
                JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP=NEW.CPM_SP_NOP
                WHERE CPM_STATUS IN (4) and (CPM_TYPE = '2') AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
                    ";
    }
}


$q      = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page   = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$find   = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$np     = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab    = $q->tab;
$uname  = $q->u;
$uid    = isset($q->uid) ? $q->uid : '';

$srcTglAwal     = @isset($_REQUEST['srcTglAwal']) ? $_REQUEST['srcTglAwal'] : '01-'.date('m-Y');
$srcTglAkhir    = @isset($_REQUEST['srcTglAkhir']) ? $_REQUEST['srcTglAkhir'] : date('d-m-Y'); 
$srcNomor       = @isset($_REQUEST['srcNomor']) ? $_REQUEST['srcNomor'] : '';
$srcNama        = @isset($_REQUEST['srcNama']) ? $_REQUEST['srcNama'] : '';
$srcJnsBerkas   = @isset($_REQUEST['srcJnsBerkas']) ? $_REQUEST['srcJnsBerkas'] : '';
$srcTahun       = @isset($_REQUEST['srcTahun']) ? $_REQUEST['srcTahun'] : '';
$srcStatus      = @isset($_REQUEST['srcStatus']) ? $_REQUEST['srcStatus'] : '';
$srch           = @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : '';

// print_r($srch); exit;

// print_r($_REQUEST);
$arrType = array(
    1 => "OP Baru",
    2 => "Pemecahan",
    3 => "Penggabungan",
    4 => "Mutasi",
    5 => "Perubahan Data",
    6 => "Pembatalan",
    7 => "Duplikat",
    8 => "Penghapusan",
    9 => "Pengurangan",
    10 => "Keberatan",
    11 => "Cetak SKNJOP",
    12 => "Pengurangan Denda"
);



$User           = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig       = $User->GetModuleConfig($m);
$appConfig      = $User->GetAppConfig($a);
$modNotaris     = new  TaxService(1, $uname);
$perpage        = ($tab=='0') ? 999999 : $appConfig['ITEM_PER_PAGE'];
$defaultPage    = 1;


$isAdminLoket = false;
if ($tab == 1) {
    $userRole = $User->GetUserRole($uid, $a);
    $arrUserRole = null;
    if (isset($arConfig['role_id_admin_loket'])) {
        $arrUserRole = explode(',', $arConfig['role_id_admin_loket']);
    }

    if ($userRole != null && $arrUserRole != null && in_array($userRole, $arrUserRole)) $isAdminLoket = true;
}

if($tab == 0){
	$userRole = $User->GetUserRole($uid, $a);
}


//set new page
if (isset($_SESSION['stPelayanan'])) {
    if ($_SESSION['stPelayanan'] != $tab) {
        $_SESSION['stPelayanan'] = $tab;
        $find = "";
        $page = 1;
        $np = 1;
        $srcTglAwal  = '';
        $srcTglAkhir  = '';
        $srcNomor  = '';
        $srcNama  = '';
        $srcJnsBerkas  = '';
        $srcStatus  = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stPelayanan'] = $tab;
}

$modNotaris->displayDataNotaris();

function convertDate($date, $delimiter = '-')
{
    if ($date == null || $date == '') return '';

    $tmp = explode($delimiter, $date);
    return $tmp[2] . $delimiter . $tmp[1] . $delimiter . $tmp[0];
}

?>
<script type="text/javascript">
    $(".srcTgl").datepicker({
        dateFormat: 'dd-mm-yy'
    });
    $(document).ready(function() {
        var axx = '<?php echo base64_decode($a) ?>';
        var uname = '<?php echo base64_decode($uname) ?>';

        //        $( "input:submit, input:button").button();
        $("input:checkbox[name='checkHapusAll0']").change(function() {
            if ($(this).is(":checked")) {
                $("input:checkbox[name='check-all0\\[\\]']").each(function() {
                    $(this).attr("checked", true);
                });
            } else {
                $("input:checkbox[name='check-all0\\[\\]']").each(function() {
                    $(this).attr("checked", false);
                });
            }
        })

        $("input:checkbox[name='checkHapusAll1']").change(function() {
            if ($(this).is(":checked")) {
                $("input:checkbox[name='check-all1\\[\\]']").each(function() {
                    $(this).attr("checked", true);
                });
            } else {
                $("input:checkbox[name='check-all1\\[\\]']").each(function() {
                    $(this).attr("checked", false);
                });
            }
        })
        $("input:checkbox[name='checkHapusAll2']").change(function() {
            if ($(this).is(":checked")) {
                $("input:checkbox[name='check-all2\\[\\]']").each(function() {
                    $(this).attr("checked", true);
                });
            } else {
                $("input:checkbox[name='check-all2\\[\\]']").each(function() {
                    $(this).attr("checked", false);
                });
            }
        })

        $("#btn-delete").click(function() {
            var arrSvcId = new Array();
            var i = 0;
            var konfHapus = confirm("Yakin data akan dihapus?"); 

            if (konfHapus) {
                $("input:checkbox[name='check-all0\\[\\]']").each(function() {
                    if ($(this).is(":checked")) {
                        arrSvcId[i] = $(this).val();
                        i++;
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "./view/PBB/loket/svc-pbb-penerimaan.php",
                    data: "task=delete&arrSvcId=" + arrSvcId.toString(),
                    success: function(msg) {
                        $("#tabsContent").tabs('load', 0);
                    }
                });
            }
        });

        $("#btn-send").click(function() {

            var arrSvcId = new Array();
            var i = 0;
            var konfHapus = confirm("Yakin data akan dikirim?");

            if (konfHapus) {
                $("input:checkbox[name='check-all0\\[\\]']").each(function() {
                    if ($(this).is(":checked")) {
                        arrSvcId[i] = $(this).val();
                        i++;
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "./view/PBB/loket/svc-pbb-penerimaan.php",
                    data: "task=send&arrSvcId=" + arrSvcId.toString(),
                    success: function(msg) {
                        $("#tabsContent").tabs('load', 0);
                    }
                });
            }
        });

        $("#btn-print0").click(function() {

            x = 0;

            $("input:checkbox[name='check-all0\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    printDataToPDF2($(this).val());
                    x++;
                }
            });
            if (x == 0) {
                alert("Belum ada data yang dipilih!");
            }
        });

        $("#btn-disposisi0").click(function() {

            x = 0;

            $("input:checkbox[name='check-all0\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    printDataToPDF($(this).val());
                    x++;
                }
            });
            if (x == 0) {
                alert("Belum ada data yang dipilih!");
            }
        });
        $("#btn-berita-acara-op-baru").click(function() {
            x = 0;

            $("input:checkbox[name='check-all0\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    printBeritaAcara($(this).val());
                    x++;
                }
            });
            if (x == 0) {
                alert("Belum ada data yang dipilih!");
            }
        });
        $("#btn-berita-acara").click(function() {
            x = 0;

            $("input:checkbox[name='check-all2\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    printBeritaAcara($(this).val());
                    x++;
                }
            });
            if (x == 0) {
                alert("Belum ada data yang dipilih!");
            }
        });
        $("#btn-print1").click(function() {

            x = 0;

            $("input:checkbox[name='check-all1\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    printDataToPDF2($(this).val());
                    x++;
                }
            });
            if (x == 0) {
                alert("Belum ada data yang dipilih!");
            }
        });

        $("#btn-disposisi1").click(function() {

            x = 0;

            $("input:checkbox[name='check-all1\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    printDataToPDF($(this).val());
                    x++;
                }
            });
            if (x == 0) {
                alert("Belum ada data yang dipilih!");
            }
        });


    });

    function printCommand(appID, id) {
        var params = {
            appID: appID,
            svcId: id
        };
        console.log("print ...");
        params = Base64.encode(Ext.encode(params));
        Ext.Ajax.request({
            url: 'svr/service/svc-service-print.php',
            timeout: 100000,
            success: printCommandSuccess,
            failure: printException,
            params: {
                req: params
            }
        });
        showMask();
    }

    function printDataToPDF(d) {
        var dt = getCheckedValues(document.getElementsByName('check-all<?php echo $tab; ?>[]'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        //console.log(s);
        printToPDF(s)
    }

    function printBeritaAcara(d) {
        var uname = '<?php echo $uname ?>';
        var dt = getCheckedValues(document.getElementsByName('check-all<?php echo $tab; ?>[]'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        //console.log(s);
        printToPDFBeritaAcara(s, uname);
    }

    function printToPDFBeritaAcara(json, uname) {
        //var params = {svcId:id, appId:'<?php echo $a; ?>', uname:'<?php echo $uname; ?>'};
        if (json) {
            //console.log("print ...");
            //params = Base64.encode(Ext.encode(params));
            window.open('./function/PBB/loket/svc-print-berita-acara-2.php?q=' + Base64.encode(json) + "&uid=" + uname, '_newtab');
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }

    function printToPDF(json) {
        //var params = {svcId:id, appId:'<?php echo $a; ?>', uname:'<?php echo $uname; ?>'};
        if (json) {
            //console.log("print ...");
            //params = Base64.encode(Ext.encode(params));
            window.open('./function/PBB/loket/svc-print-disposisi.php?q=' + Base64.encode(json), '_newtab');
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }

    function printDataToPDF2(d) {
        var dt = getCheckedValues(document.getElementsByName('check-all<?php echo $tab; ?>[]'), d);
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
        }
        //console.log(s);
        printToPDF2(s)

    }


    function printToPDF2(json) {
        //var params = {svcId:id, appId:'<?php echo $a; ?>', uname:'<?php echo $uname; ?>'};
        //console.log("print ...");
        if (json) {
            //params = Base64.encode(Ext.encode(params));
            window.open('./function/PBB/loket/svc-print-buktipenerimaan.php?q=' + Base64.encode(json), '_newtab');
        } else {
            alert("Silahkan pilih data yang akan di print!");
        }
    }

    function getCheckedValues(buttonGroup, draf) {
        // Go through all the check boxes. return an array of all the ones
        // that are selected (their position numbers). if no boxes were checked,
        // returned array will be empty (length will be zero)
        var retArr = new Array();

        var lastElement = 0;
        if (buttonGroup[0]) { // if the button group is an array (one check box is not an array)
            for (var i = 0; i < buttonGroup.length; i++) {
                if (buttonGroup[i].checked) {
                    retArr.length = lastElement;
                    var arrObj = new Object();
                    arrObj.svcId = buttonGroup[i].value;
                    //arrObj.draf = draf;
                    arrObj.appId = '<?php echo $a; ?>';
                    arrObj.uname = '<?php echo $uname; ?>';
                    retArr[lastElement] = arrObj;
                    lastElement++;
                }
            }
        } else { // There is only one check box (it's not an array)
            if (buttonGroup.checked) { // if the one check box is checked
                retArr.length = lastElement;
                var arrObj = new Object();
                arrObj.svcId = buttonGroup[i].value;
                //arrObj.draf = draf;
                arrObj.appId = '<?php echo $a; ?>';
                retArr[lastElement] = arrObj; // return zero as the only array value
            }
        }
        return retArr;
    }

    function exportToExcel(sts) {
        var srcTglAwal = $("#srcTglAwal-" + sts).val();
        var srcTglAkhir = $("#srcTglAkhir-" + sts).val();
        var srcNomor = $("#srcNomor-" + sts).val();
        var srcNama = $("#srcNama-" + sts).val();
        var srcJnsBerkas = $("#jnsBerkas").val();

        if (sts == '3') window.open("function/PBB/loket/svc-toexcel-lapharian.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); ?>&srcTglAwal=" + srcTglAwal + "&srcTglAkhir=" + srcTglAkhir + "&srcJnsBerkas=" + srcJnsBerkas);
        else if (sts == '2') {
            var srcTahun = $("#srcTahun-" + sts).val();
            window.open("function/PBB/loket/svc-toexcel-rekapselesai.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); ?>&srcTglAwal=" + srcTglAwal + "&srcTglAkhir=" + srcTglAkhir + "&srcNomor=" + srcNomor + "&srcNama=" + srcNama + "&srcTahun=" + srcTahun);
        }

    }

    function kembalikanKeLoket(id) {

        var konfHapus = confirm("Yakin data akan dihapus?");

        if (konfHapus) {
            $.ajax({
                type: "POST",
                url: "./view/PBB/loket/svc-pbb-penerimaan.php",
                data: "task=delete&arrSvcId=" + id,
                success: function(msg) {
                    $("#tabsContent").tabs('load', 1);
                }
            });
        }
    }
</script>