<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sRootPath = '/var/www/html/bphtb/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/mod-display.php");
// require_once("svc-bpn-lookup.php");

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/verifikasiLpgn/mod-verifikasi-lapangan.css\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname='".$data->uname."';</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/verifikasiLpgn/mod-verifikasi-lapangan.js\" type=\"text/javascript\"></script>\n";

class BPHTBService extends modBPHTBApprover {
  var $owner;
  
  function getDocument($sts,&$dat) {
    global $DBLink,$json;
    $srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
    $srcTxt2 = @isset($_REQUEST['src-noktp'])?$_REQUEST['src-noktp']:"";

    $where = "";
    if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%')";
    // if ($srcTxt != "") $where .= " AND ( LIKE '".$srcTxt."%' OR op_nomor LIKE '".$srcTxt."%')";
    $iErrCode=0;

    $a = $_REQUEST['a'];
    $DbName = $this->getConfigValue($a,'BPHTBDBNAME');
    $DbHost = $this->getConfigValue($a,'BPHTBHOSTPORT');
    $DbPwd = $this->getConfigValue($a,'BPHTBPASSWORD');
    $DbTable = $this->getConfigValue($a,'BPHTBTABLE');
    $DbUser = $this->getConfigValue($a,'BPHTBUSERNAME');
    $tw = $this->getConfigValue($a,'TENGGAT_WAKTU');
    
    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }
        
    
    
    $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B 
              WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
              AND B.CPM_TRAN_STATUS=4 
              AND B.CPM_TRAN_FLAG=0 
              AND b.CPM_TRAN_FERIF_LAPANGAN = 92
              $where
              ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage; 
    $res = mysqli_query($DBLink, $query);
    if ( $res === false ){
       print_r("Pengambilan data Gagal");
       echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($DBLink) . "' );</script>";
       return false; 
    }
    
    $d =  $json->decode($this->mysql2json($res,"data"));  
    $HTML = "";
    $data = $d;
    $params = 'a=aBPHTB&m=mvrfLapangan';

    for ($i=0;$i<count($data->data);$i++) {
      $par1 = $params . "&f=fvrflapangan1&idssb=" . $data->data[$i]->CPM_SSB_ID.'&sts=4';
		  $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";

      // if ($data->data[$i]->CPM_TRAN_READ == "")
      // $class = "tdbodyNew";
      $HTML .= "\t<tr>\n";

      $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">".$data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
      $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">".$data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
      $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_WP_NAMA . "</td> \n";
      $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td>\n";
      $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);
      if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
          $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
      }
      $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . number_format(intval($ccc), 0, ".", ",") . "</td>\n";
      $HTML .= "\t\t<td class=\"" . $class . "\"><span title=\"" . $data->data[$i]->CPM_TRAN_INFO . "\" class=\"span-title\">" .
          $this->splitWord($data->data[$i]->CPM_TRAN_INFO, 5) . "</span></td>\n";
      $HTML .= "\t</tr>\n";

    }

    $dat = $HTML;
    return true;
  }
  
  public function headerContentAll($sts) {
    $a = $_REQUEST['a'];
    $srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
    $srcTxt = @isset($_REQUEST['src-noktp'])?$_REQUEST['src-noktp']:"";
    
    $j = base64_encode("{'sts':'".$sts."','app':'".$a."','src':'".$srcTxt."'}");
    $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
    $HTML .= "Masukan Pencarian Berdasarkan Nama WP
              <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"40\"/> \n
              No. KTP
              <input type=\"text\" id=\"src-noktp\" name=\"src-noktp\" size=\"20\"/> \n
              <input type=\"submit\" value=\"Cari\" id=\"src-cari\" name=\"src-cari\" />\n</form>\n";
    $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
    $HTML .= "\t<tr>\n";
    $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> No. KTP</td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> Alamat Objek Pajak </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> BPHTB yang harus dibayar </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> Alasan Penolakan </td> \n";
    $HTML .= "\t</tr>\n";

    if ($this->getDocument($sts,$dt)) {
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

$page = @isset($_REQUEST['p'])?$_REQUEST['p']:1;

$modBPN = new  BPHTBService(1,$data->uname);

$pages =  $modBPN->getConfigValue("aBPHTB","ITEM_PER_PAGE");
$modBPN->setDataPerPage($pages);

$modBPN->setDefaultPage($page);


echo $modBPN->showData();

?> 
