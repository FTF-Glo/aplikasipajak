<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'updateBPHTB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/bpn/mod-bpn.css\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname='" . $data->uname . "';</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/bpn/mod-bpn.js\" type=\"text/javascript\"></script>\n";

class modBPN extends modBPHTBApprover {

    var $owner;
    public $perpage = 20;
    public $page = 1;

    function setDefaultPage($page) {
        $this->page = ($page - 1) * $this->perpage;
    }

    function setDataPerPage($perpage) {
        $this->perpage = $perpage;
    }

    function getAUTHOR($nop) {
        global $data, $DBLink;

        $query = "SELECT CPM_SSB_AUTHOR FROM cppmod_ssb_doc WHERE CPM_OP_NOMOR = '" . $nop . "'";

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
			print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($DBLink) . "' );</script>";
            return "Tidak Ditemukan";
        }
        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res, "data"));
        for ($i = 0; $i < count($data->data); $i++) {
            return $data->CPM_SSB_AUTHOR;
        }
        return "Tidak Ditemukan";
    }

    function getFromGateway($id) {
        global $DBLink;
        $query = "SELECT CPM_TRAN_STATUS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=6 
		  AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID ='{$id}'";
        $resStatus = mysqli_query($DBLink, $query);
        if ($resStatus === false) {
			print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($DBLink) . "' );</script>";
            return "Tidak Ditemukan";
        }
        $sts = false;
        $rowStatus = mysqli_fetch_array($resStatus);
        //if ($rowStatus != "") $app = $rowStatus['CPM_TRAN_STATUS'];
        if ($rowStatus != "")
            $sts = true;
        return $sts;
    }

    function getConfigValue($key) {
        global $DBLink;
        $id = $_REQUEST['a'];
        $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($DBLink) . "' );</script>";
            echo mysqli_error($DBLink);
        }
        while ($row = mysqli_fetch_assoc($res)) {
            return $row['CTR_AC_VALUE'];
        }
    }

    function getDocument($sts, &$dat) {
        global $DBLink, $json, $json, $a, $m, $noktp, $nop, $opsi, $User, $nm_wp, $findnotaris;

        $conf = $User->GetModuleConfig($m);
        $form_update = $conf['form_update'];
        $dbLimit = $this->getConfigValue('TENGGAT_WAKTU');
        $where ='';
        if ($nm_wp!='') {
            $where .= "AND A.CPM_WP_NAMA like '%{$nm_wp}%'";
        }
        if ($findnotaris!='') {
            $where .= "AND A.CPM_SSB_AUTHOR like '%{$findnotaris}%'";
        }
        $query = "SELECT A.*,B.*, DATE_ADD(A.CPM_SSB_CREATED,INTERVAL {$dbLimit} day) as EXPIRE_DATE, B.CPM_TRAN_STATUS
                    FROM cppmod_ssb_doc A inner join cppmod_ssb_tranmain B 
                    on A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
                    WHERE
                    B.CPM_TRAN_FLAG='0' AND
                    (A.CPM_WP_NOKTP like '" . mysqli_real_escape_string($DBLink, $noktp) . "%' $opsi A.CPM_OP_NOMOR like '" . mysqli_real_escape_string($DBLink, $nop) . "%')
                    {$where}
                    AND B.CPM_TRAN_STATUS != 4
                    GROUP BY A.CPM_SSB_ID
                    ORDER BY B.CPM_TRAN_DATE DESC 
                    LIMIT " . $this->page . "," . $this->perpage;
        // echo $query;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
			print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($DBLink) . "' );</script>";
            return false;
        }
        $qry = "SELECT count(A.CPM_SSB_ID)
                FROM cppmod_ssb_doc A inner join cppmod_ssb_tranmain B 
                on A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
                WHERE 
                (A.CPM_WP_NOKTP like '" . mysqli_real_escape_string($DBLink, $noktp) . "%' 
                $opsi 
                A.CPM_OP_NOMOR like '" . mysqli_real_escape_string($DBLink, $nop) . "%')
                GROUP BY A.CPM_SSB_ID";

        $resCount = mysqli_query($DBLink, $qry);
        $this->totalRows = mysqli_num_rows($resCount);

        $HTML = "";
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        $ss = true;
        $i = 0;
        $arrStatus = array("Notaris", "Verifkasi", "Persetujuan", "Ditolak", "Final");
        while ($data = mysqli_fetch_object($res)) {
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "\t<div class=\"container\"><tr>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . ($i + 1) . ".</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_WP_NOKTP . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_WP_NAMA . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_OP_NOMOR . "</td> \n";
            $HTML .= "\t\t<td align='right' class=\"" . $class . "\">" . number_format($data->CPM_OP_NPOP) . "</td> \n";
            $HTML .= "\t\t<td align='right' class=\"" . $class . "\">" . number_format($data->CPM_OP_NPOPTKP) . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_TRAN_DATE . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_TRAN_CLAIM_DATETIME . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->EXPIRE_DATE . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_SSB_AUTHOR . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->CPM_SSB_CREATED . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $arrStatus[$data->CPM_TRAN_STATUS - 1] . "</td> \n";
            $par1 = $params . "&f=" . $form_update . "&idssb=" . $data->CPM_SSB_ID. "&paytype=" . $data->CPM_PAYMENT_TIPE; #PENTING!!! parameter f diisi sesuai id dari fungsi function/BPHTB/func-update-bphtb.php
            $HTML .= "\t\t<td class=\"" . $class . "\">
						<input type=button class='btn btn-success btn-sm' value='Update' onclick=\"javascript:window.location.href='main.php?param=" . base64_encode($par1 . "&pros=view") . "'\">
						<!-- <input type=button value='Hapus' onclick=\"javascript:if(confirm('Apakah anda yakin untuk menghapus data ini?')==true){window.location.href='main.php?param=" . base64_encode($par1 . "&pros=del&status=" . $data->CPM_TRAN_STATUS) . "'}\"> -->
					</td> \n";

            $HTML .= "\t</tr></div>\n";
            $i++;
        }

        #ardi total row
        $dat = $HTML;
        return true;
    }

    public function headerContentAll($sts) {
        global $noktp, $nop, $nm_wp, $opsi, $findnotaris;
        $a = $_REQUEST['a'];
        $srcTxt = @isset($_REQUEST['src-approved']) ? $_REQUEST['src-approved'] : "";
		
		if($opsi=="and"){$selectedand="selected";}else{$selectedand="";}
		if($opsi=="or"){$selectedor="selected";}else{$selectedor="";}
		
        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "'}");
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
                    <style>
                        .form-filtering {
                            background-color: #fff;
                            padding: 20px 20px;
                            
                            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    
                        }
					</style>

					<div  class=\"p-2\">
						<div class=\"row\"> 
							<div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\"> 
								<button class=\"btn btn-info\" type=\"button\" style=\"border-radius:8px\" data-toggle=\"collapse\" data-target=\"#collapsFilter\" aria-expanded=\"false\" aria-controls=\"collapsFilter\">
									Filter Data
								</button>
							</div>
						</div>
					</div>
					<div class=\"col-12\"> 
						<div class=\"collapse\" id=\"collapsFilter\">
							<div class=\"form-filtering\">
								<form>
									<div class=\"row\">

										<div class=\" form-group col-md-3\"> 
											<label>No KTP</label>
                                            <input type=\"text\" class=\" form-control\" name=\"src-noktp\" value='" . $noktp . "'/> 
                                            <input type=\"hidden\" class=\" form-control\" name=\"opsi\" value='and'/> 
										</div>
										<div class=\" form-group col-md-3\"> 
											<label>NOP</label>
                                            <input type=\"text\" class=\"form-control\" name=\"src-nop\" value='" . $nop . "'/> 
										</div>
										<div class=\" form-group col-md-3\"> 
											<label>Nama WP</label>
                                            <input type=\"text\" class=\"form-control\" name=\"src-nama-wp\" value='" . $nm_wp . "'/> 
										</div>
										<div class=\" form-group col-md-3\"> 
											<label>User</label>
                                            <input type=\"text\" class=\"form-control\" name=\"src-nama-notaris\" value='" . $findnotaris . "'/>  
										</div>

										<div class=\" form-group col-md-12\">    
                                            <input type=\"submit\"  class=\"btn btn-info\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />											
										</div>

									</div>
								</form>
							</div>
						</div>
					</div>
        </form>";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. KTP</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nama WP</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> NOP BPHTB</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nilai Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> NOPTKP</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Lapor</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Setuju</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Expire</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> User Input</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Input</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Status</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Update</td> \n";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    function getContent() {
        $HTML = "";
        $HTML = $this->headerContentAll($this->status);

        return $HTML;
    }

    function showData() {
        echo "<div id=\"notaris-main-content\">\n";
        echo "\t<div id=\"notaris-main-content-inner\">\n";
        echo $this->getContent();
        echo "\t</div>\n";
        echo "\t<div id=\"notaris-main-content-footer\" align=\"right\">  \n";
        echo $this->paging();
        echo "</div>\n";
    }

}

$modBPN = new modBPN(1, $data->uname);


$page = 1;
if (@isset($_REQUEST['param'])) {
    $q = $_REQUEST['param'];
    $q = base64_decode($q);
    $param = explode("&", $q);
    $p = explode("=", end($param));
    $page = ($p[0] == 'p') ? $p[1] : 1;
    $modBPN->defaultPage = $page;
}

$opsi = @isset($_REQUEST['opsi']) ? $_REQUEST['opsi'] : 'and';
$noktp = @isset($_REQUEST['src-noktp']) ? $_REQUEST['src-noktp'] : '';
$nop = @isset($_REQUEST['src-nop']) ? $_REQUEST['src-nop'] : '';
$nm_wp = @isset($_REQUEST['src-nama-wp']) ? $_REQUEST['src-nama-wp'] : '';
$findnotaris = @isset($_REQUEST['src-nama-notaris']) ? $_REQUEST['src-nama-notaris'] : '';



$pages = $modBPN->getConfigValue("ITEM_PER_PAGE");
$modBPN->setDataPerPage($pages);
$modBPN->setDefaultPage($page);
echo $modBPN->showData();
?> 
