<?php
$sRootPath = "/var/www/html/bphtb/";
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$servername = GW_DBHOST;
$database = GW_DBNAME;
$username = GW_DBUSER;
$password = GW_DBPWD;
$DBLinkLookUp = mysqli_connect($servername, $username, $password, $database);
global $DBLinkLookUp;         

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
        global $DBLink, $json, $json, $a, $m, $noktp, $nop, $opsi, $User, $DBLinkLookUp;

        $conf = $User->GetModuleConfig($m);
        $form_update = $conf['form_update'];
        $dbLimit = $this->getConfigValue('TENGGAT_WAKTU');
    

    $query = "SELECT * FROM ssb WHERE
                    (wp_noktp like '" . mysqli_real_escape_string($DBLinkLookUp, $noktp) . "%' $opsi op_nomor like '" . mysqli_real_escape_string($DBLinkLookUp, $nop) . "%')
                    AND bphtb_dibayar = 0
                    ORDER BY EXPIRED_DATE DESC 
                    LIMIT " . $this->page . "," . $this->perpage;
        //echo $query;
        $res = mysqli_query($DBLinkLookUp, $query);
    //var_dump($DBLink);
        if ($res === false) {
      print_r("Pengambilan data Gagal");
      echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($DBLinkLookUp) . "' );</script>";
            return false;
        }
        $qry = "SELECT * FROM ssb WHERE
                    (wp_noktp like '%" . mysqli_real_escape_string($DBLinkLookUp, $noktp) . "%' $opsi op_nomor like '%" . mysqli_real_escape_string($DBLinkLookUp, $nop) . "%')
                    AND bphtb_dibayar = 0
                    ORDER BY EXPIRED_DATE DESC ";
        $resCount = mysqli_query($DBLinkLookUp, $qry);
        $this->totalRows = mysqli_num_rows($resCount);

        $HTML = "";
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        $ss = true;
        $i = 0;
        $arrStatus = array("Notaris", "Verifkasi", "Persetujuan", "Ditolak", "Final");
        while ($data = mysqli_fetch_object($res)) {
      //var_dump($data);
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "\t<div class=\"container\"><tr>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . ($i + 1) . ".</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->wp_noktp . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->wp_nama . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->op_nomor . "</td> \n";
            $HTML .= "\t\t<td align='right' class=\"" . $class . "\">" . number_format($data->bphtb_dibayar) . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->payment_paid . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->expired_date . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->author . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->payment_code . "</td> \n";
            $par1 = $params . "&f=f454" . "&idssb=" . $data->id_switching. "&paytype=1"; #PENTING!!! parameter f diisi sesuai id dari fungsi function/BPHTB/func-update-bphtb.php
            
            $HTML .= "\t\t<td class=\"" . $class . "\">
            <input type=button class='btn-sm btn btn-blue' value='Update' onclick=\"javascript:window.location.href='main.php?param=" . base64_encode($par1 . "&pros=view") . "'\">
          </td> \n";

            $HTML .= "\t</tr></div>\n";
            $i++;
        }

        #ardi total row
        $dat = $HTML;
        return true;
    }

    public function headerContentAll($sts) {
        global $noktp, $nop, $opsi;
        $a = $_REQUEST['a'];
        $srcTxt = @isset($_REQUEST['src-approved']) ? $_REQUEST['src-approved'] : "";
    
    if($opsi=="and"){$selectedand="selected";}else{$selectedand="";}
    if($opsi=="or"){$selectedor="selected";}else{$selectedor="";}
    
        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "'}");
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian berdasarkan <b>No. KTP</b> : <input type=\"text\" name=\"src-noktp\" size=\"40\" value='" . $noktp . "'/> 
        <select name='opsi'><option ".$selectedand." value='and'>And</option><option ".$selectedor." value='or'>Or</option></select>
        <b>NOP</b> : <input type=\"text\" name=\"src-nop\" size=\"40\" value='" . $nop . "'/> 
        <input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. KTP</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nama WP</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> NOP BPHTB</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nilai Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Bayar</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Expire</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> User Input</td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> Kode Bayar</td> \n";
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
        echo "<div id=\"notaris-main-content\" style=\"margin-left: 19px;\">\n";
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



$pages = $modBPN->getConfigValue("ITEM_PER_PAGE");
$modBPN->setDataPerPage($pages);
$modBPN->setDefaultPage($page);
echo $modBPN->showData();
?> 
