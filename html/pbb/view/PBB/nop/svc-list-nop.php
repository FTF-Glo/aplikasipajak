<?php
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'nop', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-dms-c.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

// require_once($sRootPath . "inc/PBB/dbWajibPajak.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

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

function getDocument(&$dat)
{
    global $DBLink, $json, $data, $a, $m, $tab, $page, $totalrows, $perpage, $arConfig, $srcNOP1, $srcNOP2, $srcNOP3, $srcNOP4, $srcNOP5, $srcNOP6, $srcNOP7, $srcAlamat, $srcNama, $dbWajibPajak;

    $rows = $data['DATA'];
    // echo "<pre>";
    // print_r($rows); exit;
    // $params = "a=".$a."&m=".$m."&f=".$arConfig['form_input']."&tab=".$tab; 
    $params = "a=$a&m=$m&f=" . $arConfig['id_view_spop'];
    $HTML = false;
    if ($rows != null && count($rows) > 0) {
        foreach ($rows as $row) {
            $HTML .= "<tr>";
            $HTML .= "<td width=10 align=center><input name=\"check-all-" . $tab . "[]\" class=\"check-all-" . $tab . "\" type=\"checkbox\" value=\"" . (isset($row['CPM_NOP']) ? $row['CPM_NOP'] : '') . "\" /></td>";
            $HTML .= "<td align=center><a href='main.php?param=" . base64_encode($params . '&doc_id=' . (isset($row['CPM_SPPT_DOC_ID']) ? $row['CPM_SPPT_DOC_ID'] : '') . '&doc_vers=' . (isset($row['CPM_SPPT_DOC_VERSION']) ? $row['CPM_SPPT_DOC_VERSION'] : '')) . "'>" . (isset($row['CPM_NOP']) ? $row['CPM_NOP'] : '') . "</a></td>";
            $HTML .= "<td>" . (isset($row['CPM_WP_NAMA']) ? $row['CPM_WP_NAMA'] : '') . "</td>";
            $HTML .= "<td>" . (isset($row['CPM_OP_ALAMAT']) ? $row['CPM_OP_ALAMAT'] : '') . "</td>";
            $HTML .= "<td align=center>" . (isset($row['CPM_OT_ZONA_NILAI']) ? $row['CPM_OT_ZONA_NILAI'] : '') . "</td>";
            $HTML .= "<td align=right>" . (isset($row['CPM_OP_LUAS_TANAH']) ? $row['CPM_OP_LUAS_TANAH'] : '') . "</td>";
            $HTML .= "<td align=right>" . (isset($row['CPM_OP_LUAS_BANGUNAN']) ? $row['CPM_OP_LUAS_BANGUNAN'] : '') . "</td>";
            $HTML .= "<td align=right>" . (isset($row['CPM_NJOP_TANAH']) ? $row['CPM_NJOP_TANAH'] : '') . "</td>";
            $HTML .= "<td align=right>" . (isset($row['CPM_NJOP_BANGUNAN']) ? $row['CPM_NJOP_BANGUNAN'] : '') . "</td>";
            $HTML .= "<td align=right>" . (isset($row['CPM_NJOP_TOTAL']) ? $row['CPM_NJOP_TOTAL'] : '') . "</td>";
            $HTML .= "<td>" . (isset($row['CPM_OP_KECAMATAN']) ? getKecamatanNama($row['CPM_OP_KECAMATAN']) : '') . "</td>";
            $HTML .= "<td>" . (isset($row['CPM_OP_KELURAHAN']) ? getKelurahanNama($row['CPM_OP_KELURAHAN']) : '') . "</td>";
            $HTML .= "<td align=center><button onclick=\"listTagihan('" . (isset($row['CPM_NOP']) ? $row['CPM_NOP'] : '') . "')\">Lihat Tagihan</button></td>";
            $HTML .= "</tr>";
        }
    }else{
        $HTML .= "<tr><td colspan=13 align=center>Data tidak diketemukan<br>atau belum ditetapkan</td></tr>";
    }
    $dat = $HTML;
}

function getData()
{
    global $DBLink, $page, $perpage, $selectby, $kecamatan, $desa, $tahun, $srcNOP1, $srcNOP2, $srcNOP3, $srcNOP4, $srcNOP5, $srcNOP6, $srcNOP7, $srcAlamat, $srcNama, $tab;

    $whr = " WHERE CPM_NOP='-'";

    $arrWhere = array();

    if($selectby==1){
        if ($kecamatan != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 1, 7) = '{$kecamatan}'");
        }
        if ($desa != "" && $desa != null && $desa != 'null') {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 1, 10) = '{$desa}'");
        }
        if ($tab != 0 && $tahun != "") {
            array_push($arrWhere, " CPM_SPPT_THN_PENETAPAN = '{$tahun}'");
        }

    }else{
        
        if ($srcNOP1 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 1, 2) = '{$srcNOP1}'");
        }
        if ($srcNOP2 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 3, 2) = '{$srcNOP2}'");
        }
        if ($srcNOP3 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 5, 3) = '{$srcNOP3}'");
        }
        if ($srcNOP4 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 8, 3) = '{$srcNOP4}'");
        }
        if ($srcNOP5 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 11, 3) = '{$srcNOP5}'");
        }
        if ($srcNOP6 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 14, 4) = '{$srcNOP6}'");
        }
        if ($srcNOP7 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 18, 1) = '{$srcNOP7}'");
        }
        if ($srcNama != "") {
            array_push($arrWhere, " CPM_WP_NAMA LIKE '%{$srcNama}%'");
        }
        if ($srcAlamat != "") {
            array_push($arrWhere, " CPM_OP_ALAMAT LIKE '%{$srcAlamat}%'");
        }
    }
    
    // added by d3Di = khusus Tab Register tanah   -----------------------
    if ($tab == 3) array_push($arrWhere, " SUBSTR(CPM_NOP, 18, 1) = '3'");
    //--------------------------------------------------------------------
    
    $where = implode(" AND ", $arrWhere);

    if ($where != '') {
        $whr = " WHERE $where";
    }

    $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

    switch ($tab) {
        case 0:
            $tableName = "cppmod_pbb_sppt";
            break;
        case 1:
            $tableName = "cppmod_pbb_sppt_susulan";
            break;
        case 2:
            $tableName = "cppmod_pbb_sppt_final";
            break;
        case 3:
            $tableName = "cppmod_pbb_sppt_final";
            break;
    }

    $query = "SELECT 
                CPM_SPPT_DOC_ID,
                CPM_SPPT_DOC_VERSION,
                CPM_NOP,
                CPM_WP_NAMA,
                CPM_OP_ALAMAT,
                CPM_OP_KELURAHAN,
                CPM_OP_KECAMATAN,
                CPM_OT_ZONA_NILAI,
                CPM_OP_LUAS_TANAH,
                CPM_OP_LUAS_BANGUNAN,
                CPM_NJOP_TANAH,
                CPM_NJOP_BANGUNAN
            FROM $tableName 
                $whr 
            LIMIT $hal,$perpage";
    // echo $query; exit;
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
    $qCount   = "SELECT COUNT(*) AS TOTALROWS FROM $tableName $whr";
    // echo $qCount;

    $resCount    = mysqli_query($DBLink, $qCount);
    $rowCount    = mysqli_fetch_assoc($resCount);

    $row = array();
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $return[$i]["CPM_SPPT_DOC_ID"]      = ($row["CPM_SPPT_DOC_ID"] != "") ? $row["CPM_SPPT_DOC_ID"] : '-';
        $return[$i]["CPM_SPPT_DOC_VERSION"] = ($row["CPM_SPPT_DOC_VERSION"] != "") ? $row["CPM_SPPT_DOC_VERSION"] : '-';
        $return[$i]["CPM_NOP"]               = ($row["CPM_NOP"] != "") ? $row["CPM_NOP"] : '-';
        $return[$i]["CPM_WP_NAMA"]          = ($row["CPM_WP_NAMA"] != "") ? $row["CPM_WP_NAMA"] : '-';
        $return[$i]["CPM_OP_ALAMAT"]         = ($row["CPM_OP_ALAMAT"] != "") ? $row["CPM_OP_ALAMAT"] : '-';
        $return[$i]["CPM_OT_ZONA_NILAI"]    = ($row["CPM_OT_ZONA_NILAI"] != "") ? $row["CPM_OT_ZONA_NILAI"] : '-';
        $return[$i]["CPM_OP_LUAS_TANAH"]    = ($row["CPM_OP_LUAS_TANAH"] != "") ? $row["CPM_OP_LUAS_TANAH"] : '0';
        $return[$i]["CPM_OP_LUAS_BANGUNAN"] = ($row["CPM_OP_LUAS_BANGUNAN"] != "") ? $row["CPM_OP_LUAS_BANGUNAN"] : '0';
        $return[$i]["CPM_NJOP_TANAH"]         = ($row["CPM_NJOP_TANAH"] != "") ? $row["CPM_NJOP_TANAH"] : '0';
        $return[$i]["CPM_NJOP_BANGUNAN"]     = ($row["CPM_NJOP_BANGUNAN"] != "") ? $row["CPM_NJOP_BANGUNAN"] : '0';
        $return[$i]["CPM_NJOP_TOTAL"]         = ($row["CPM_NJOP_TANAH"] + $row["CPM_NJOP_BANGUNAN"]);
        $return[$i]["CPM_OP_KELURAHAN"]      = ($row["CPM_OP_KELURAHAN"] != "") ? $row["CPM_OP_KELURAHAN"] : '';
        $return[$i]["CPM_OP_KECAMATAN"]      = ($row["CPM_OP_KECAMATAN"] != "") ? $row["CPM_OP_KECAMATAN"] : '';
        $i++;
    }
    $OKE['JML_DATA'] = $i;
    $OKE['JML_ROWS'] = $rowCount['TOTALROWS'];
    $OKE['DATA'] = $return;

    // echo '<pre>';
    // print_r($OKE);

    return $OKE;
}

function getKecamatanNama($kode)
{
    global $DBLink;
    $query = "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '" . $kode . "';";
    $res   = mysqli_query($DBLink, $query);
    $row   = mysqli_fetch_array($res);
    return $row['CPC_TKC_KECAMATAN'];
}
function getKelurahanNama($kode)
{
    global $DBLink;
    $query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kode . "';";
    $res   = mysqli_query($DBLink, $query);
    $row   = mysqli_fetch_array($res);
    return $row['CPC_TKL_KELURAHAN'];
}

function headerContent()
{
    global $find, $a, $m, $tab, $srcNOP1, $srcNOP2, $srcNOP3, $srcNOP4, $srcNOP5, $srcNOP6, $srcNOP7, $srcAlamat, $srcNama;

    $HTML = "";
    $HTML .= headerContentWP();

    getDocument($dt);

    if ($dt) {
        $HTML .= $dt;
    } else {
        $HTML .= "<tr><td colspan=13 align=center>Klik Cari untuk menampilkan data.</td></tr>";
    }
    $HTML .= "</table>";
    return $HTML;
}

function headerContentWP()
{
    global $find, $a, $m, $arConfig, $appConfig, $tab, $srcNOP1, $srcNOP2, $srcNOP3, $srcNOP4, $srcNOP5, $srcNOP6, $srcNOP7, $srcAlamat, $srcNama;
    $forminput = null;

    if (isset($arConfig['form_input'])) {
        $forminput = $arConfig['form_input'];
    }

    $params = "a=" . $a . "&m=" . $m;
    $startLink = "<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params . "&f=" . $forminput . "&jnsBerkas=1") . "\">";
    $endLink = "</a>";

    $HTML = "<div class=\"row\"><div class=\"col-md-12\"><form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
    $HTML .= "
    <div class=\"row\" style=\"margin-top: 5px;\">
        <div class=\"col-md-1\">
            <div class=\"form-group\">
                <label style=\"margin-top:10px;margin-left:10px\">Pencarian</label>
            </div>
        </div>
        <div class=\"col-md-2\">
            <div class=\"form-group\"  style=\"margin-left:10px\">
                <input type=\"radio\" name=\"sel{$tab}\" id=\"zwx1{$tab}\" value=\"1\" onclick=\"selectTabzwx{$tab}(1)\"> <label for=\"zwx1{$tab}\">BY Desa</label><br>
                <input type=\"radio\" name=\"sel{$tab}\" id=\"zwx2{$tab}\" value=\"2\" onclick=\"selectTabzwx{$tab}(2)\"> <label for=\"zwx2{$tab}\">BY NOP</label><br>
            </div>
        </div>
        <div class=\"col-md-9\">
            <div class=\"row\" id=\"bydesazwx{$tab}\">
                <div class=\"col-md-5\">
                    <div class=\"form-group\">
                        <label>Kecamatan: </label>
                        <select name=\"kecamatannop{$tab}\" id=\"kecamatannop{$tab}\" class=\"form-control\"></select>
                    </div>
                </div>
                <div class=\"col-md-5\">
                    <div class=\"form-group\">
                        <label>Desa:</label>
                        <select name=\"desanop{$tab}\" id=\"desanop{$tab}\" class=\"form-control\"></select>
                    </div>
                </div>
            </div>
            <div id=\"bynopzwx{$tab}\" style=\"margin:10px 10px 14px 0;display:none\">
                <div class=\"row\">
                    <div class=\"col-md-6\">
                        <div class=\"form-group\">
                            <div class=\"col-md-1\" style=\"padding: 0\">
                                <input type=\"text\" class=\"form-control nop-input-1\" style=\"padding: 6px;\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNOP-" . $tab . "-1\" name=\"srcNOP-1\" size=\"20\" value=\"" . $srcNOP1 . "\" placeholder=\"PR\">
                            </div>
                            <div class=\"col-md-1\" style=\"padding: 0\">
                                <input type=\"text\" class=\"form-control nop-input-2\" style=\"padding: 6px;\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNOP-" . $tab . "-2\" name=\"srcNOP-2\" size=\"20\" value=\"" . $srcNOP2 . "\" placeholder=\"DTII\" maxlength=\"2\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" class=\"form-control nop-input-3\" style=\"padding: 6px;\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNOP-" . $tab . "-3\" name=\"srcNOP-3\" size=\"20\" value=\"" . $srcNOP3 . "\" placeholder=\"KEC\" maxlength=\"3\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" class=\"form-control nop-input-4\" style=\"padding: 6px;\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNOP-" . $tab . "-4\" name=\"srcNOP-4\" size=\"20\" value=\"" . $srcNOP4 . "\" placeholder=\"KEL\" maxlength=\"3\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" class=\"form-control nop-input-5\" style=\"padding: 6px;\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNOP-" . $tab . "-5\" name=\"srcNOP-5\" size=\"20\" value=\"" . $srcNOP5 . "\" placeholder=\"BLOK\" maxlength=\"3\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" class=\"form-control nop-input-6\" style=\"padding: 6px;\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNOP-" . $tab . "-6\" name=\"srcNOP-6\" size=\"20\" value=\"" . $srcNOP6 . "\" placeholder=\"NO.URUT\" maxlength=\"4\">
                            </div>
                            <div class=\"col-md-2\" style=\"padding: 0\">
                                <input type=\"text\" class=\"form-control nop-input-7\" style=\"padding: 6px;\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNOP-" . $tab . "-7\" name=\"srcNOP-7\" size=\"20\" value=\"" . $srcNOP7 . "\" placeholder=\"KODE\" maxlength=\"1\">
                            </div>
                        </div>
                    </div>
                    <div class=\"col-md-3\">
                        <div class=\"form-group\">
                            <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNama-" . $tab . "\" name=\"srcNama\" size=\"30\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/>
                        </div>
                    </div>
                    <div class=\"col-md-3\">
                        <div class=\"form-group\">
                            <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcAlamat-" . $tab . "\" name=\"srcAlamat\" size=\"30\" value=\"" . $srcAlamat . "\" placeholder=\"Alamat\"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class=\"row\">
        ".(
            ($tab==0) ? '<div class="col-md-6">&nbsp;</div>' : '
                <div class="col-md-1">
                    <div class="form-group">
                        <label style="margin-top:5px;margin-left:10px">Tahun</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="tahunpajaknop'.$tab.'" class="form-control" id="tahunpajaknop'.$tab.'">
                        <option value="">Semua</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                        <option value="2021">2021</option>
                    </select>
                </div>
                <div class="col-md-3">&nbsp;</div>
            '
        )."
        <div class=\"col-md-6 mb5\" style=\"text-align:right;padding-right:25px;margin-top:5px\">
            <button type=\"button\" class=\"btn btn-primary btn-orange\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button>
            <button type=\"button\" class=\"btn btn-primary btn-blue\" id=\"btn-excel\" name=\"btn-excel\" onclick=\"toExcel(" . $tab . ")\">Cetak Excel</button>
            <button type=\"button\" class=\"btn btn-primary bg-maka\" name=\"btn-print\"/ onClick=\"printpreviewdata(" . $tab . ")\">Cetak SPOP & LSPOP</button>
            <button type=\"button\" class=\"btn btn-primary btn-orange\" name=\"btn-print\"/ onClick=\"printpreviewdataspop(" . $tab . ")\">Cetak SPOP</button>
            <button type=\"button\" class=\"btn btn-primary btn-blue\" name=\"btn-print\"/ onClick=\"printpreviewdatalspop(" . $tab . ")\">Cetak LSPOP</button>
        </div>
    </div>
    <script>
        $(\".nop-input-1\").on(\"keyup\", function(){
            var len = $(this).val().length;
            let nopLengkap = $(this).val();
            
            if(!$(\".nop-input-2\").val()) $(\".nop-input-2\").val(nopLengkap.substr(2, 2));
            if(!$(\".nop-input-3\").val()) $(\".nop-input-3\").val(nopLengkap.substr(4, 3));
            if(!$(\".nop-input-4\").val()) $(\".nop-input-4\").val(nopLengkap.substr(7, 3));
            if(!$(\".nop-input-5\").val()) $(\".nop-input-5\").val(nopLengkap.substr(10, 3));
            if(!$(\".nop-input-6\").val()) $(\".nop-input-6\").val(nopLengkap.substr(13, 4));
            if(!$(\".nop-input-7\").val()) $(\".nop-input-7\").val(nopLengkap.substr(17, 1));
            if(len > 2) $(this).val(nopLengkap.substr(0, 2));
            if(len == 2) {
                $(\".nop-input-2\").focus();
            }
        });

        $(\".nop-input-2\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 2) {
                $(\".nop-input-3\").focus();
            }
        });

        $(\".nop-input-3\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 3) {
                $(\".nop-input-4\").focus();
            }
        });

        $(\".nop-input-4\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 3) {
                $(\".nop-input-5\").focus();
            }
        });

        $(\".nop-input-5\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 3) {
                $(\".nop-input-6\").focus();
            }
        });

        $(\".nop-input-6\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 4) {
                $(\".nop-input-7\").focus();
            }
        });

        $(\".nop-input-7\").on(\"keyup\", function(){
            var len = $(this).val().length;

            if(len == 1) {
                setTabs(" . $tab . ");
            }
        });
    </script>
    </form></div></div>";

    $HTML .= '<div class=row><div class="col-md-12"><div class="table-responsive" style="margin-top:15px"><table class="table table-bordered table-striped" style="width:100%;max-width:1200px;min-width:1200px">';
    $HTML .= '<tr>';
    $HTML .= "<td rowspan=2 width=10 class=tdheader><input name=\"all-check-button-$tab\" id=\"all-check-button-$tab\" type=\"checkbox\"/></td>";
    $HTML .= "<td rowspan=2 class=tdheader>NOP</td>";
    $HTML .= "<td rowspan=2 class=tdheader>Nama WP</td>";
    $HTML .= "<td rowspan=2 class=tdheader>Alamat</td>";
    $HTML .= "<td rowspan=2 class=tdheader>Kode ZNT</td>";
    $HTML .= "<td colspan=2 class=tdheader>Luas</td>";
    $HTML .= "<td colspan=3 class=tdheader>NJOP</td>";
    $HTML .= "<td rowspan=2 class=tdheader>Kecamatan OP</td>";
    $HTML .= "<td rowspan=2 class=tdheader>Desa&nbsp;OP</td>";
    $HTML .= "<td rowspan=2 class=tdheader>List Tagihan</td>";
    $HTML .= '</tr>';
    $HTML .= '<tr>';
    $HTML .= "<td class=tdheader width=65>Tanah</td>";
    $HTML .= "<td class=tdheader width=45>Bangunan</td>";
    $HTML .= "<td class=tdheader width=45>Tanah</td>";
    $HTML .= "<td class=tdheader width=45>Bangunan</td>";
    $HTML .= "<td class=tdheader width=45>Total</td>";
    $HTML .= "</tr>";

    return $HTML;
}

function displayDataWP()
{
    echo "<div class=\"ui-widget consol-main-content\">\n";
    echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
    echo headerContent();
    echo "\t</div>\n";
    echo "\t<div class=\"ui-widget-header consol-main-content-footer\" align=\"right\">  \n";
    echo paging();
    echo "</div>\n";
}

function paging()
{
    global $a, $m, $n, $tab, $page, $np, $perpage, $defaultPage, $totalrows;

    $params = "a=" . $a . "&m=" . $m;
    $html = "<div>";
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
    $html .= "</div>";
    return $html;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";

$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab = $q->tab;
$uname = $q->u;
$uid = null;
if (isset($q->uid)) {
    $uid = $q->uid;
}

$selectby   = @isset($_REQUEST['selectby']) ? $_REQUEST['selectby'] : '1';
$kecamatan  = @isset($_REQUEST['kec'])      ? $_REQUEST['kec'] : '';
$desa       = @isset($_REQUEST['desa'])     ? $_REQUEST['desa'] : '';
$tahun      = @isset($_REQUEST['tahun'])    ? $_REQUEST['tahun'] : '';
$srcAlamat  = @isset($_REQUEST['srcAlamat'])? $_REQUEST['srcAlamat'] : '';
$srcNama    = @isset($_REQUEST['srcNama'])  ? $_REQUEST['srcNama'] : '';
$srcNOP1    = @isset($_REQUEST['srcNOP1'])  ? $_REQUEST['srcNOP1'] : '';
$srcNOP2    = @isset($_REQUEST['srcNOP2'])  ? $_REQUEST['srcNOP2'] : '';
$srcNOP3    = @isset($_REQUEST['srcNOP3'])  ? $_REQUEST['srcNOP3'] : '';
$srcNOP4    = @isset($_REQUEST['srcNOP4'])  ? $_REQUEST['srcNOP4'] : '';
$srcNOP5    = @isset($_REQUEST['srcNOP5'])  ? $_REQUEST['srcNOP5'] : '';
$srcNOP6    = @isset($_REQUEST['srcNOP6'])  ? $_REQUEST['srcNOP6'] : '';
$srcNOP7    = @isset($_REQUEST['srcNOP7'])  ? $_REQUEST['srcNOP7'] : '';


$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp($a);
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
// $dbWajibPajak = new DbWajibPajak($dbSpec);

$defaultPage     = 1;
$perpage         = $appConfig['ITEM_PER_PAGE'];
$totalrows = 1;
// echo '<pre>';
// print_r($data);

//set new page
if (isset($_SESSION['stWP'])) {
    if ($_SESSION['stWP'] != $tab) {
        $_SESSION['stWP'] = $tab;
        $find = "";
        $page = 1;
        $np = 1;
        $srcAlamat  = '';
        $srcNama  = '';
        $srcNOP1  = '';
        $srcNOP2  = '';
        $srcNOP3  = '';
        $srcNOP4  = '';
        $srcNOP5  = '';
        $srcNOP6  = '';
        $srcNOP7  = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stWP'] = $tab;
}

if($selectby==1){
    $data = getData();
    $totalrows = isset($data['JML_ROWS']) ? $data['JML_ROWS'] : 0;
}else{
    //echo 'srcNOP ='.$srcNOP;
    if(
        $srcNOP1 != '' || 
        $srcNOP2 != '' || 
        $srcNOP3 != '' || 
        $srcNOP4 != '' || 
        $srcNOP5 != '' || 
        $srcNOP6 != '' || 
        $srcNOP7 != '' || 
        $srcAlamat != '' || 
        $srcNama != ''
    ){
        $data = getData();
        $totalrows = isset($data['JML_ROWS']) ? $data['JML_ROWS'] : 0;
    }
}

?>

<script type="text/javascript">
    var tab = '<?=$tab?>';
        selectzwxby = '<?=$selectby?>';
    var kecamatanx = '<?=$kecamatan?>';
    var desax = '<?=$desa?>';

    $(document).ready(function() {
        showKecamatan<?=$tab?>();
        setselection();
        $("#all-check-button-" + tab).click(function() {
            $(".check-all-" + tab).each(function() {
                this.checked = $("#all-check-button-" + tab).is(":checked");
            });
        });
        $("select#kecamatannop"+tab).on('change', function() {
            showKelurahan();
        })
    });

    // add by d3Di radio Button
    function selectTabzwx<?=$tab?>(val) {
        selectzwxby = val;
        if(val==2){
            $("#bydesazwx"+tab).hide();
            $("#bynopzwx"+tab).show();
        }else{
            $("#bydesazwx"+tab).show();
            $("#bynopzwx"+tab).hide();
        }
    }

    // add by d3Di set Seleksi
    function setselection() {
        $("#tahunpajaknop"+tab).val(<?=(($tahun)?$tahun:'')?>);
        if(selectzwxby==1){
            $("#zwx1"+tab).click();
        }else{
            $("#zwx2"+tab).click();
        }
    }

    /// add by d3Di
    function showKecamatan<?=$tab?>() {
        var request = $.ajax({
            url: "view/PBB/monitoring/svc-kecamatan.php",
            type: "POST",
            data: {
                id: "1801"
            },
            dataType: "json",
            success: function(data) {
                var c = data.msg.length;
                var options = '';
                options += '<option value="">Pilih Semua</option>';
                for (var i = 0; i < c; i++) {
                    options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                }
                $("select#kecamatannop<?=$tab?>").html(options);
                if(kecamatanx!=''){
                    $("select#kecamatannop<?=$tab?>").val(kecamatanx);
                    showKelurahan();
                }
            }
        });
    }

    /// add by d3Di
    function showKelurahan() {
        var id = $('select#kecamatannop<?=$tab?>').val()
        var request = $.ajax({
            url: "view/PBB/monitoring/svc-kecamatan.php",
            type: "POST",
            data: {
                id: id,
                kel: 1
            },
            dataType: "json",
            beforeSend: function(d) {
                $("select#desanop<?=$tab?>").html("");
            },
            success: function(data) {
                // alert(data);
                if (data == null) {
                    $("select#desanop<?=$tab?>").html("");
                    return false;
                }
                var c = data.msg.length;
                // alert(c);
                var options = '';
                if (parseInt(c) > 0) {
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        $("select#desanop<?=$tab?>").html(options);
                    }

                    if(desax!=''){
                        $("select#desanop<?=$tab?>").val(desax);
                    }
                    
                } else {
                    $("select#desanop<?=$tab?>").html('<option value="">Pilih Semua</option>');
                    // opti
                }
            },
            error: function(msg) {
                alert(msg);
                $("select#desanop<?=$tab?>").html("");
            }
        });
    }

    function printpreviewdata(tab) {
        x = 0;
        $("input:checkbox[name='check-all-" + tab + "\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });
        nop = "";
        idx = 0;
        if (x == 0) alert("Pilih data yang akan dicetak!");
        else {
            $("input:checkbox[name='check-all-" + tab + "\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    if (idx > 0) nop = nop + ",";
                    nop = nop + "'" + $(this).val() + "'";
                    idx++;
                }
            });
            //alert(nop);
            printToPDF(nop, tab);
        }
    }

    function printToPDF(nop, tab) {
        var params = {
            NOP: nop,
            tab: tab,
            appID: '<?php echo $a; ?>'
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/nop/print-spop.php?req=' + params, '_newtab');
    }

    function printpreviewdatalspop(tab) {
        x = 0;
        $("input:checkbox[name='check-all-" + tab + "\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });
        nop = "";
        idx = 0;
        if (x == 0) alert("Pilih data yang akan dicetak!");
        else {
            $("input:checkbox[name='check-all-" + tab + "\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    if (idx > 0) nop = nop + ",";
                    nop = nop + "'" + $(this).val() + "'";
                    idx++;
                }
            });
            //alert(nop);
            printToPDFlspop(nop, tab);
        }
    }

    function printToPDFlspop(nop, tab) {
        var params = {
            NOP: nop,
            tab: tab,
            appID: '<?php echo $a; ?>'
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/nop/print-lspop.php?req=' + params, '_newtab');
    }

    function printpreviewdataspop(tab) {
        x = 0;
        $("input:checkbox[name='check-all-" + tab + "\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });
        nop = "";
        idx = 0;
        if (x == 0) alert("Pilih data yang akan dicetak!");
        else {
            $("input:checkbox[name='check-all-" + tab + "\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    if (idx > 0) nop = nop + ",";
                    nop = nop + "'" + $(this).val() + "'";
                    idx++;
                }
            });
            //alert(nop);
            printToPDFspop(nop, tab);
        }
    }

    function printToPDFspop(nop, tab) {
        var params = {
            NOP: nop,
            appID: '<?php echo $a; ?>',
            tab: tab
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/nop/print-spop-1.php?req=' + params, '_newtab');
    }

    function toExcel(tab) {
        var kecamatan = $("#kecamatannop" + tab).val();
        var desa = $("#desanop" + tab).val();
        var tahun = (tab==0) ? '' : $("#tahunpajaknop" + tab).val();
        var srcAlamat = $("#srcAlamat-" + tab).val();
        var srcNama = $("#srcNama-" + tab).val();
        var srcNOP1 = $("#srcNOP-" + tab + "-1").val();
        var srcNOP2 = $("#srcNOP-" + tab + "-2").val();
        var srcNOP3 = $("#srcNOP-" + tab + "-3").val();
        var srcNOP4 = $("#srcNOP-" + tab + "-4").val();
        var srcNOP5 = $("#srcNOP-" + tab + "-5").val();
        var srcNOP6 = $("#srcNOP-" + tab + "-6").val();
        var srcNOP7 = $("#srcNOP-" + tab + "-7").val();

        window.open("function/PBB/nop/svc-toexcel-daftar-nop.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'$tab', 'uid':'$uid'}"); ?>" + "&srcNOP1=" + srcNOP1 + "&srcNOP2=" + srcNOP2 + "&srcNOP3=" + srcNOP3 + "&srcNOP4=" + srcNOP4 + "&srcNOP5=" + srcNOP5 + "&srcNOP6=" + srcNOP6 + "&srcNOP7=" + srcNOP7 + "&srcNama=" + srcNama + "&srcAlamat=" + srcAlamat+ "&tahun=" + tahun+ "&desa=" + desa+ "&kec=" + kecamatan+ "&selectby=" + selectzwxby);
    }
</script>
<?php
displayDataWP();
?>