<?php
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'uploadberkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/user-central.php");
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

class TaxService {

    private $userGroup;
    private $user;

    public function __construct($userGroup, $user) {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }

    function getTotalRows($query) {
        global $DBLink;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_fetch_array($res);
        return $row['TOTALROWS'];
    }

    function mysql2json($mysql_result, $name) {
        $json = "{\n'$name': [\n";
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
            $json.="{\n";
            for ($y = 0; $y < count($field_names); $y++) {
                $json.="'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
                if ($y == count($field_names) - 1) {
                    $json.="\n";
                } else {
                    $json.=",\n";
                }
            }
            if ($x == $rows - 1) {
                $json.="\n}\n";
            } else {
                $json.="\n},\n";
            }
        }
        $json.="]\n}";
        return($json);
    }

    function getDocument(&$dat) {
        global $DBLink, $json, $a, $m, $tab, $find, $page, $totalrows, $perpage, $arConfig, $arrType, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNoPel, $srcNama, $isAdminLoket, $srcNoKTP;

        $srcTxt = $find;
        $where = " ";
        
        if ($srcNama != "")
            $where .= " AND A.CPM_BERKAS_NAMA_WP LIKE '%" . $srcNama . "%' ";
        if ($srcNomor != "")
            $where .= " AND A.CPM_BERKAS_NOP LIKE '%" . $srcNomor . "%' ";
        if ($srcNoPel != "")
            $where .= " AND A.CPM_BERKAS_NOPEL LIKE '%" . $srcNoPel . "%' ";
        if ($srcTglAwal != "")
            $where .= " AND A.CPM_BERKAS_TANGGAL >= '" . convertDate($srcTglAwal) . "' ";
        if ($srcTglAkhir != "")
            $where .= " AND A.CPM_BERKAS_TANGGAL <= '" . convertDate($srcTglAkhir) . "' ";
		 if ($srcNoKTP != "")
            $where .= " AND A.CPM_BERKAS_NPWP LIKE '%" . $srcNoKTP . "%' ";

        $query = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

	//var_dump($_SESSION['role']);
		
		if($_SESSION['role'] == 'rmBPHTBStaff' || $_SESSION['uname'] == 'ftfuser'){
			$query = "SELECT * FROM cppmod_ssb_berkas A JOIN cppmod_ssb_doc B ON A.CPM_SSB_DOC_ID=B.CPM_SSB_ID  WHERE B.CPM_SSB_AUTHOR IS NOT NULL $where ORDER BY CPM_BERKAS_TANGGAL DESC LIMIT " . $hal . "," . $perpage;

        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_berkas A JOIN cppmod_ssb_doc B ON A.CPM_SSB_DOC_ID=B.CPM_SSB_ID  WHERE B.CPM_SSB_AUTHOR IS NOT NULL $where ";
		}else{
			$query = "SELECT * FROM cppmod_ssb_berkas A JOIN cppmod_ssb_doc B ON A.CPM_SSB_DOC_ID=B.CPM_SSB_ID  WHERE B.CPM_SSB_AUTHOR='".$_SESSION['username']."' $where ORDER BY CPM_BERKAS_TANGGAL DESC LIMIT " . $hal . "," . $perpage;

        	$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_berkas A JOIN cppmod_ssb_doc B ON A.CPM_SSB_DOC_ID=B.CPM_SSB_ID  WHERE B.CPM_SSB_AUTHOR='".$_SESSION['username']."' $where ";
		}
		// echo $query;
	
        
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
			print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($DBLink) . "' );</script>";
            return false;
        }

        $totalrows = $this->getTotalRows($qry);
        //$d = $json->decode($this->mysql2json($res, "data"));
        $HTML = $startLink = $endLink = "";
        //$data = $d;
        $params = "a=" . $a . "&m=" . $m . "&f=" . $arConfig['form_input'] . "&tab=" . $tab;

        $no = ($page - 1) * $this->perpage;

            //for ($i = 0; $i < count($data->data); $i++) {
			while($data=mysqli_fetch_object($res)){
                //$class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
                $HTML .= "\t<div><tr>\n";
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">";
                if ($tab == '0' || $tab == '1')
                    $HTML .= "<input id=\"\" name=\"check-all" . $tab . "[]\" type=\"checkbox\" value=\"" . $data->CPM_BERKAS_ID . "\" />";
                $HTML .= "</td>\n";
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";

                $HTML .= "\t\t<td class=\"" . $class . "\"align=\"center\"><a style='text-decoration: underline;' href=\"main.php?param=" . base64_encode($params . "&svcid=" . $data->CPM_BERKAS_ID) . "\">" . $data->CPM_BERKAS_NOP . "</a></td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_BERKAS_NAMA_WP . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_BERKAS_NPWP . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_BERKAS_NOPEL . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . convertDate($data->CPM_BERKAS_TANGGAL) . "</td> \n";
                $status = $data->CPM_BERKAS_STATUS == 1 ? "Lengkap" : "Tidak Lengkap";
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">{$status}</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_BERKAS_PETUGAS . "</td> \n";

                if ($tab == 1) {
                    if ($isAdminLoket) {
                        if ($data->CPM_BERKAS_STATUS == '0') {
                            $HTML .= "\t\t<td class=\"" . $class . "\"><input type=\"button\" value=\"Hapus\" title=\"Hapus data berkas\" onclick=\"kembalikanKeLoket('" . $data->CPM_BERKAS_NOP . "');\"/></td> \n";
                        }
                        else
                            $HTML .= "\t\t<td class=\"" . $class . "\"></td> \n";
                    }
                }
			}
                $HTML .= "\t</tr></div>\n";
            $dat = $HTML;
            return true;
        
    }

    public function headerContent() {
        global $find, $a, $m, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama;

        $HTML = $this->headerContentPenerimaan();
        $this->getDocument($dt);

        if ($dt) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=8>Data Kosong !</td></tr>";
        }
        $HTML .= "</table>";
        return $HTML;
    }

    public function headerContentPenerimaan() {
        global $find, $a, $m, $arConfig, $appConfig, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNoPel, $srcNama, $srcNoKTP;

        $params = "a=" . $a . "&m=" . $m;
        $startLink = "<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params . "&f=" . $arConfig['form_input'] . "&jnsBerkas=1") . "\">";
        $endLink = "</a>";

        $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "
            <div style='overflow:auto;'>
                <div style='float:left;'>
                    <!-- $startLink<input type=\"button\" value=\"Tambah\" id=\"btn-add\" name=\"btn-add\"/>$endLink
                    <input type=\"button\" value=\"Hapus\" id=\"btn-delete\" name=\"btn-delete\"/> -->
                    <input type=\"button\" value=\"Cetak\" id=\"btn-print" . $tab . "\" name=\"btn-print\" />
                    <input type=\"button\" value=\"Cetak Disposisi\" id=\"btn-disposisi" . $tab . "\" name=\"btn-disposisi\" />
                </div>
                <div style='float:left'>
                    Pencarian  
                    <input type=\"text\" class=\"srcTgl\" name=\"srcTglAwal\" id=\"srcTglAwal-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAwal . "\" placeholder=\"Tgl Awal\"/> s/d
                    <input type=\"text\" class=\"srcTgl\" name=\"srcTglAkhir\" id=\"srcTglAkhir-" . $tab . "\" size=\"10\" maxlength=\"10\" value=\"" . $srcTglAkhir . "\" placeholder=\"Tgl Akhir\"/>                        
                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNomor-" . $tab . "\" name=\"srcNomor\" size=\"25\" value=\"" . $srcNomor . "\" placeholder=\"NOP\"/>
                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNoPel-" . $tab . "\" name=\"srcNoPel\" size=\"20\" value=\"" . $srcNoPel . "\" placeholder=\"Nomor Pelayanan\"/>
                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNama-" . $tab . "\" name=\"srcNama\" size=\"30\" value=\"" . $srcNama . "\" placeholder=\"Nama Wajib Pajak\"/>
                    <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNoKTP-" . $tab . "\" name=\"srcNoKTP\" size=\"25\" value=\"" . $srcNoKTP . "\" placeholder=\"No. KTP/NPWP Wajib Pajak\"/>
					<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\"/>\n
                </div>
            </div>
            </form>\n <br>";
			
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=tdheader><div>
                    <div class=\"all\"><input name=\"checkHapusAll" . $tab . "\" id=\"checkHapusAll" . $tab . "\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=tdheader>No.</td> \n";

        $HTML .= "\t\t<td class=tdheader>No Objek Pajak</td> \n";
        $HTML .= "\t\t<td class=tdheader>Nama WP</td> \n";
        $HTML .= "\t\t<td class=tdheader>NPWP</td> \n";
        $HTML .= "\t\t<td class=tdheader>Nomor Pelayanan</td> \n";
        $HTML .= "\t\t<td class=tdheader>Tanggal Terima</td>\n";
        $HTML .= "\t\t<td class=tdheader>Status</td>\n";
        $HTML .= "\t\t<td class=tdheader>Penerima</td>\n";
        $HTML .= "\t</tr>\n";

        return $HTML;
    }

    public function displayDataNotaris() {
        echo "<div class=\"ui-widget consol-main-content\">\n";
        echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
        echo $this->headerContent();
        echo "\t</div>\n";
        echo "\t<div class=\"ui-widget-header consol-main-content-footer\" align=\"right\">  \n";
        echo $this->paging();
        echo "</div>\n";
    }

    function paging() {
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
$uid = isset($q->uid)?$q->uid:'';

$srcTglAwal = @isset($_REQUEST['srcTglAwal']) ? $_REQUEST['srcTglAwal'] : '';
$srcTglAkhir = @isset($_REQUEST['srcTglAkhir']) ? $_REQUEST['srcTglAkhir'] : '';
$srcNomor = @isset($_REQUEST['srcNomor']) ? $_REQUEST['srcNomor'] : '';
$srcNoPel = @isset($_REQUEST['srcNoPel']) ? $_REQUEST['srcNoPel'] : '';
$srcNama = @isset($_REQUEST['srcNama']) ? $_REQUEST['srcNama'] : '';
$srcNoKTP = @isset($_REQUEST['srcNoKTP']) ? $_REQUEST['srcNoKTP'] : '';


$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);

$modNotaris = new TaxService(1, $uname);
$perpage = $appConfig['ITEM_PER_PAGE'];
$defaultPage = 1;

$isAdminLoket = false;
if ($tab == 1) {
    $userRole = $User->GetUserRole($uid, $a);
    $arrUserRole = explode(',', $arConfig['role_id_admin_berkas']);
    if (in_array($userRole, $arrUserRole))
        $isAdminLoket = true;
}

//set new page
if (isset($_SESSION['stPelayanan'])) {
    if ($_SESSION['stPelayanan'] != $tab) {
        $_SESSION['stPelayanan'] = $tab;
        $find = "";
        $page = 1;
        $np = 1;
        $srcTglAwal = '';
        $srcTglAkhir = '';
        $srcNomor = '';
        $srcNoPel = '';
        $srcNama = '';
		$srcNoKTP = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stPelayanan'] = $tab;
}

$modNotaris->displayDataNotaris();

function convertDate($date, $delimiter = '-') {
    if ($date == null || $date == '')
        return '';

    $tmp = explode($delimiter, $date);
    return $tmp[2] . $delimiter . $tmp[1] . $delimiter . $tmp[0];
}
?>
<script type="text/javascript">
    $(document).ready(function() {
        var axx = '<?php echo base64_decode($a) ?>';

        $(".srcTgl").datepicker({dateFormat: 'dd-mm-yy'});
        $("input:submit, input:button").button();
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
                    url: "./view/BPHTB/berkas/svc-pbb-penerimaan.php",
                    data: "task=delete&arrSvcId=" + arrSvcId.toString(),
                    success: function(msg) {
						alert("Data sudah dihapus dari daftar kelengkapan berkas.");
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
                    url: "./view/BPHTB/berkas/svc-pbb-penerimaan.php",
                    data: "task=send&arrSvcId=" + arrSvcId.toString(),
                    success: function(msg) {
						alert("Data sudah dihapus dari daftar kelengkapan berkas.");
                        $("#tabsContent").tabs('load', 0);
                    }
                });
            }
        });

        $("#btn-print0").click(function() {
            printDataToPDF2('check-all0[]');
//            x = 0;
//            $("input:checkbox[name='check-all0\\[\\]']").each(function() {
//                if ($(this).is(":checked")) {
//                    printToPDF2($(this).val());
//                    x++;
//                }
//            });
//            if (x == 0) {
//                alert("Belum ada data yang dipilih!");
//            }
        });

        $("#btn-disposisi0").click(function() {
            printDataToPDF('check-all0[]');
//            x = 0;
//            $("input:checkbox[name='check-all0\\[\\]']").each(function() {
//                if ($(this).is(":checked")) {
//                    printToPDF($(this).val());
//                    x++;
//                }
//            });
//            if (x == 0) {
//                alert("Belum ada data yang dipilih!");
//            }
        });

        $("#btn-print1").click(function() {

            x = 0;

            $("input:checkbox[name='check-all1\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    printToPDF2($(this).val());
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
                    printToPDF($(this).val());
                    x++;
                }
            });
            if (x == 0) {
                alert("Belum ada data yang dipilih!");
            }
        });


    });

    function printCommand(appID, id) {
        var params = {appID: appID, svcId: id};
        console.log("print ...");
        params = Base64.encode(Ext.encode(params));
        Ext.Ajax.request({
            url: 'svr/service/svc-service-print.php',
            timeout: 100000,
            success: printCommandSuccess,
            failure: printException,
            params: {req: params}
        });
        showMask();
    }

    function getCheckedValue(buttonGroup) {
        var retArr = new Array();

        var lastElement = 0;
        if (buttonGroup[0]) { // if the button group is an array (one check box is not an array)
            for (var i = 0; i < buttonGroup.length; i++) {
                if (buttonGroup[i].checked) {
                    retArr.length = lastElement;
                    var arrObj = new Object();
                    arrObj.id = buttonGroup[i].value;
                    arrObj.axx = "<?php echo $a; ?>";
                    arrObj.uname = "";
                    retArr[lastElement] = arrObj;
                    lastElement++;
                }
            }
        } else { // There is only one check box (it's not an array)
            if (buttonGroup.checked) { // if the one check box is checked
                retArr.length = lastElement;
                var arrObj = new Object();
                arrObj.id = buttonGroup[i].value;                
                arrObj.axx = "<?php echo $a; ?>";
                retArr[lastElement] = arrObj; // return zero as the only array value
            }
        }
        return retArr;
    }

    function printDataToPDF(name) {
        var dt = getCheckedValue(document.getElementsByName(name));
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
            printToPDF(s)
        }
        //console.log(s);
        
    }


    function printToPDF(id) {
        var params = {svcId: id, appId: '<?php echo $a; ?>', uname: '<?php echo $uname; ?>'};
        console.log("print ...");
        params = Base64.encode(Ext.encode(params));
        window.open('./function/BPHTB/berkas/svc-print-disposisi.php?q=' + params, '_newtab');
    }

    function printDataToPDF2(name) {
        var dt = getCheckedValue(document.getElementsByName(name));
        var s = "";
        if (dt != "") {
            s = Ext.util.JSON.encode(dt);
            printToPDF2(s)
        }
        //console.log(s);
        
    }


    function printToPDF2(id) {
        var params = {svcId: id, appId: '<?php echo $a; ?>', uname: '<?php echo $uname; ?>'};
        console.log("print ...");
        params = Base64.encode(Ext.encode(params));
        window.open('./function/BPHTB/berkas/svc-print-buktipenerimaan.php?q=' + params, '_newtab');
    }

    function kembalikanKeLoket(id) {

        var konfHapus = confirm("Yakin data akan dihapus?");

        if (konfHapus) {
            $.ajax({
                type: "POST",
                url: "./view/BPHTB/berkas/svc-pbb-penerimaan.php",
                data: "task=delete&arrSvcId=" + id,
                success: function(msg) {
					alert("Data sudah dihapus dari daftar kelengkapan berkas.");
                    $("#tabsContent").tabs('load', 1);
                }
            });
        }
    }
</script>