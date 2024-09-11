<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dispenda' . DIRECTORY_SEPARATOR . 'penilaian', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "phpqrcode/src/Milon/Barcode/DNS2D.php");
require_once($sRootPath . "phpqrcode/src/Milon/Barcode/QRcode.php");

use \Milon\Barcode\DNS2D;

class displayDocument
{

  public $right;
  public $iddoc;
  public $jsondata;
  private $uname;
  public $maxtime = '00:10:00';
  private $approved = false;
  private $claim;
  private $rejected;
  private $stsrejected = false;
  private $submit = false;
  private $newversion = false;
  private $dispenda1 = false;
  private $dispenda2 = false;
  private $dispenda3 = false;
  private $dispenda4 = false;
  private $dispenda5 = false;

  function __construct($right, $id)
  {
    $this->right = $right;
    $this->iddoc = $id;
  }

  function setUName($name)
  {
    $this->uname = $name;
  }

  function mysql2json($mysql_result, $name)
  {
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
      $json .= "{\n";
      for ($y = 0; $y < count($field_names); $y++) {
        $json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
        if ($y == count($field_names) - 1) {
          $json .= "\n";
        } else {
          $json .= ",\n";
        }
      }
      if ($x == $rows - 1) {
        $json .= "\n}\n";
      } else {
        $json .= "\n},\n";
      }
    }
    $json .= "]\n}";
    return ($json);
  }

  function setNewVersion()
  {
    $this->newversion = true;
  }

  function setStaffDispenda()
  {
    $this->dispenda1 = true;
  }

  function setDirDispenda($sts = true)
  {
    $this->dispenda2 = $sts;
  }
  function setDirDispenda2($sts = true)
  {
    $this->dispenda3 = $sts;
  }

  function setApproved($sts = true)
  {
    $this->approved = $sts;
  }

  function setRejected($rej = false, $rf = 0)
  {
    $this->rejected = $rej;
    $this->rejFrom = $rf;
  }

  function getConfigValue($id, $key)
  {
    global $DBLink;
    $id = $_REQUEST['a'];
    $qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      echo $qry . "<br>";
      echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
      return $row['CTR_AC_VALUE'];
    }
  }

  function getMinCreated($noktp, $nop)
  {
    global $DBLink;
    $a = $_REQUEST['a'];
    $day = $this->getConfigValue($a, "BATAS_HARI_NPOPTKP");
    $qry = "select min(CPM_SSB_CREATED) as mx from cppmod_ssb_doc  where 
		CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) ";

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      print_r(mysqli_error($DBLink));
      return false;
    }

    if (mysqli_num_rows($res)) {
      $num_rows = mysqli_num_rows($res);
      while ($row = mysqli_fetch_assoc($res)) {
        if ($row["mx"]) {

          return $row["mx"];
        }
      }
    }
  }

  function getBPHTBPayment($lb, $nb, $lt, $nt, $h, $p, $jh, $NPOPTKP, $phw, $aphbt, $denda)
  {
    //$a = $_REQUEST['a'];
    /*$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_STANDAR');
		
		$typeR = $jh;
		
		if (($typeR==4) || ($typeR==6)){
			$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_WARIS');
		} else {
			
		}*/

    /*if($this->getNOKTP($noktp,$nop,$tgl)) {	
			$NPOPTKP = 0;
		}*/

    $hitungaphb = $this->getConfigValue("1", 'HITUNG_APHB');
    $configAPHB = $this->getConfigValue("1", 'CONFIG_APHB');
    $configPengenaan = $this->getConfigValue("1", 'CONFIG_PENGENAAN');
    $a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
    $b = strval($h);
    $npop = 0;


    if ($jh == '15') {
      $npop = $b;
    } else {
      if ($b <= $a) $npop = $a;
      else $npop = $b;
    }
    $npkp = $npop - strval($NPOPTKP);
    $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
    $hbphtb = ($npop - strval($NPOPTKP)) * 0.05;
    $aphb = 0;
    $hbphtb_pengenaan = 0;
    $hbphtb_aphb = 0;
    // var_dump($jh);die;
    if (($jh == 4) || ($jh == 5) || ($jh == 31)) {
      if ($configPengenaan == '1') {
        $hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
        $jmlByr = $hbphtb - ($hbphtb_pengenaan);
      } else {
        $hbphtb_pengenaan = 0;
        $jmlByr = $hbphtb;
      }
    } else if ($jh == 7) {
      if ($configAPHB == '1') {
        $p = explode("/", $aphbt);
        $aphb = $p[0] / $p[1];
        $hbphtb_pengenaan = 0;
        if ($hitungaphb == '1') {
          // rumus asli
          // $hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05 * $aphb;
          $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05 / 2;


          // $hbphtb_aphb = (($npop * $aphb) - strval($NPOPTKP)) * 0.05;
          // echo  $aphb;
          // die;
        } else if ($hitungaphb == '2') {
          // echo "snis";
          // die;
          $hbphtb_aphb = (($npop - strval($NPOPTKP)) * 0.05) - (($npop - strval($NPOPTKP)) * 0.05 * $aphb);
        } else if ($hitungaphb == '3') {
          // echo "ssni";
          // die;
          $hbphtb_aphb = ($npop * $aphb) - (strval($NPOPTKP) * 0.05);
        } else if ($hitungaphb == '0') {
          // echo "snssi";
          // die;
          $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
        }
      } else {
        $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
      }
      // echo $hbphtb_aphb;
      // die;
      // var_dump($hbphtb_aphb);

      $jmlByr = $hbphtb_aphb;
    }


    $total_temp = $jmlByr;
    $tp = strval($aphb);

    // kondisi hamparan
    // if ($tp != 0) {
    // 	$jmlByr = $jmlByr - ($total_temp * ($tp * 0.01));
    // }



    if ($denda > 0) {
      // var_dump('sini');
      // // echo $tp * 0.01;
      // die;
      $jmlByr = $jmlByr + $denda;
    } else {
      // var_dump('sinis');
      // // echo $tp * 0.01;
      // die;
      $jmlByr = $jmlByr;
    }
    if ($jmlByr < 0) $jmlByr = 0;
    // echo ($jmlByr);
    // die;
    return $jmlByr;
  }

  function getBPHTBPayment_all($no)
  {
    global $data;
    $hitungaphb = $this->getConfigValue("1", 'HITUNG_APHB');
    $configAPHB = $this->getConfigValue("1", 'CONFIG_APHB');
    $configPengenaan = $this->getConfigValue("1", 'CONFIG_PENGENAAN');
    $lb = $data->CPM_OP_LUAS_BANGUN;
    $nb = $data->CPM_OP_NJOP_BANGUN;
    $lt = $data->CPM_OP_LUAS_TANAH;
    $nt = $data->CPM_OP_NJOP_TANAH;
    $h  = $data->CPM_OP_HARGA;
    $p  = $data->CPM_PAYMENT_TIPE_PENGURANGAN;
    $jh = $data->CPM_OP_JENIS_HAK;
    $NPOPTKP = $data->CPM_OP_NPOPTKP;
    $phw = $data->CPM_PENGENAAN;
    $denda = $data->CPM_DENDA;
    $aphbt = $data->CPM_APHB;

    $a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
    $b = strval($h);
    $npop = 0;
    // var_dump($jh);exit;
    if ($jh == '15' || $jh == '8') {
      $npop = $b;
    } else {
      $npop = $b;
      if ($b <= $a) $npop = $a;
      else $npop = $b;
    }
    $npkp = $npop - strval($NPOPTKP);
    if ($npkp <= 0) {
      $npkp = 0;
    }
    $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
    $hbphtb = ($npop - strval($NPOPTKP)) * 0.05;
    $aphb = 0;
    $hbphtb_pengenaan = 0;
    $hbphtb_aphb = 0;
    if (($jh == 4) || ($jh == 5) || ($jh == 31)) {
      if ($configPengenaan == '1') {
        $hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
        $jmlByr = $hbphtb - ($hbphtb_pengenaan);
      } else {
        $hbphtb_pengenaan = 0;
        $jmlByr = $hbphtb;
      }
    } else if ($jh == 7) {
      if ($configAPHB == '1') {
        $p = explode("/", $aphbt);
        $aphb = $p[0] / $p[1];
        $hbphtb_pengenaan = 0;
        if ($hitungaphb == '1') {
          // $hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05 * $aphb;
          // $hbphtb_aphb = (($npop*$aphb) - $NPOPKP) *0.05;
          $hbphtb_aphb = (($npop * $aphb) - strval($NPOPTKP)) * 0.05;
          if ($hbphtb_aphb < 0) {
            $hbphtb_aphb = 0;
          }
        } else if ($hitungaphb == '2') {
          $hbphtb_aphb = (($npop - strval($NPOPTKP)) * 0.05) - (($npop - strval($NPOPTKP)) * 0.05 * $aphb);
        } else if ($hitungaphb == '3') {
          $hbphtb = $npop * $aphb;
          $hbphtb_aphb = ($hbphtb - strval($NPOPTKP)) * 0.05;
        } else if ($hitungaphb == '0') {
          $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
        }
      } else {
        $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
      }
      $jmlByr = $hbphtb_aphb;
    }

    $tp = strval($p);
    if ($tp != 0) $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

    if ($denda > 0) {
      $jmlByr = $jmlByr + $denda;
    } else {
      $jmlByr = $jmlByr;
    }
    if ($jmlByr <= 0) {
      $jmlByr = 0;
      $hbphtb = 0;
    }
    $total_temp = $jmlByr;
    $hasil = $npop . "," . $npkp . "," . $hbphtb . "," . $hbphtb_pengenaan . "," . $hbphtb_aphb . "," . $total_temp . "," . $jmlByr;
    $pilihhitung = explode(",", $hasil);
    // var_dump($npop);exit;

    //echo $hasil;exit;
    return $pilihhitung[$no];
  }

  private function cekGateway($ssbid)
  {
    global $DBLink;
    $dbName = $this->getConfigValue($a, 'BPHTBDBNAME');
    $dbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
    //$dbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
    $dbPwd = $this->getConfigValue($a, '');
    $dbTable = $this->getConfigValue($a, 'BPHTBTABLE');
    $dbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
    $qry = "SELECT * FROM ssb WHERE id_switching='" . $ssbid . "'";

    $rs = mysqli_query($DBLinkLookUp, $qry);
    $jumlah = mysqli_num_rows($rs);
    //echo $jumlah;exit;
    if ($jumlah > 0) {
      $hasil = 1;
    } else {
      $hasil = 0;
    }
    return $hasil;
  }

  private function GetPAYMENTCode($ssbid)
  {
    global $DBLink;
    $dbName = $this->getConfigValue($a, 'BPHTBDBNAME');
    $dbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
    $dbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
    // $dbPwd = $this->getConfigValue($a, '');
    $dbTable = $this->getConfigValue($a, 'BPHTBTABLE');
    $dbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
    $qry = "SELECT payment_flag, payment_code, expired_date FROM ssb WHERE id_switching='" . $ssbid . "'";

    $KODE = false;
    $rs = mysqli_query($DBLinkLookUp, $qry);
    while ($row = mysqli_fetch_assoc($rs)) {
      if ($row['payment_flag'] == 0) {
        $obj = (object)[];
        $obj->payment_code = $row['payment_code'];
        $obj->expired_date = $row['expired_date'];
        $KODE = $obj;
      } else {
        $KODE = false;
      }
    }
    return $KODE;
  }

  private function getDBQRIS($ssbid)
  {
    global $DBLink;
    $dbName = $this->getConfigValue($a, 'BPHTBDBNAME');
    $dbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
    $dbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
    // $dbPwd = $this->getConfigValue($a, '');
    $dbTable = $this->getConfigValue($a, 'BPHTBTABLE');
    $dbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

    $datetimenow = date('Y-m-d H:i:s');

    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
    $qry = "SELECT * FROM qris WHERE id_switching='$ssbid' AND expired_date_time>='$datetimenow'";

    $result = false;
    $rs = mysqli_query($DBLinkLookUp, $qry);
    while ($row = mysqli_fetch_assoc($rs)) $result = json_decode(json_encode($row));
    return $result;
  }

  public function callInsertToGateway($ssbid, $opr = "")
  {
    $this->insertToGateway($ssbid, $opr);
  }

  private function insertToGateway($ssbid, $opr = "")
  {
    global $DBLink;
    // SCANPayment_ConnectToDB($DBLinkV, $DBConnV, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME); // new ALDES
    // $DBLink = $DBLinkV;
    // die(var_dump($DBLinkV));
    // pengurangan SPN
    $sqlPenguranganBphtb = "Select * FROM cppmod_ssb_doc_pengurangan where CPM_SSB_ID ='{$ssbid}'";
    $res = mysqli_query($DBLink, $sqlPenguranganBphtb);
    $row = mysqli_fetch_assoc($res);
    if (mysqli_num_rows($res) >= 1) {
      $pengurangan = $row['nilaipengurangan'];
    }
    if ($pengurangan == NULL) {
      $pengurangan = 0;
    }
    // end pengurangan
    // var_dump($pengurangan);
    // die;
    $qry = "SELECT * FROM cppmod_ssb_doc WHERE CPM_SSB_ID='" . $ssbid . "'";

    $rs = mysqli_query($DBLink, $qry);
    while ($rw = mysqli_fetch_array($rs)) {

      $a = strval($rw['CPM_OP_LUAS_BANGUN']) * strval($rw['CPM_OP_NJOP_BANGUN']) + strval($rw['CPM_OP_LUAS_TANAH']) * strval($rw['CPM_OP_NJOP_TANAH']);
      $b = strval($rw['CPM_OP_HARGA']);
      $npop = 0;
      if ($b <= $a)
        $npop = $a;
      else
        $npop = $b;

      $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
      $tp = strval($rw['CPM_PAYMENT_TIPE_PENGURANGAN']);
      if ($tp != 0)
        $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

      $ccc = $this->getBPHTBPayment($rw['CPM_OP_LUAS_BANGUN'], $rw['CPM_OP_NJOP_BANGUN'], $rw['CPM_OP_LUAS_TANAH'], $rw['CPM_OP_NJOP_TANAH'], $rw['CPM_OP_HARGA'], $rw['CPM_PAYMENT_TIPE_PENGURANGAN'], $rw['CPM_OP_JENIS_HAK'], $rw['CPM_OP_NPOPTKP'], $rw['CPM_PENGENAAN'], $rw['CPM_APHB'], $rw['CPM_DENDA']) - $pengurangan;
      // var_dump($ccc);
      // die;
      // if (($rw['CPM_PAYMENT_TIPE'] == '2') && (!is_null($rw['CPM_OP_BPHTB_TU']))) {
      // $ccc = $rw['CPM_OP_BPHTB_TU'];
      // }
      // echo $rw['CPM_PAYMENT_TIPE'];
      // die;

      $id_switching = mysqli_real_escape_string($DBLink, $rw['CPM_SSB_ID']);
      $wp_nama = mysqli_real_escape_string($DBLink, $rw['CPM_WP_NAMA']);
      $wp_alamat = mysqli_real_escape_string($DBLink, $rw['CPM_WP_ALAMAT']);
      $wp_rt = mysqli_real_escape_string($DBLink, $rw['CPM_WP_RT']);
      $wp_rw = mysqli_real_escape_string($DBLink, $rw['CPM_WP_RW']);
      $wp_kelurahan = mysqli_real_escape_string($DBLink, $rw['CPM_WP_KELURAHAN']);
      $wp_kecamatan = mysqli_real_escape_string($DBLink, $rw['CPM_WP_KECAMATAN']);
      $wp_kabupaten = mysqli_real_escape_string($DBLink, $rw['CPM_WP_KABUPATEN']);
      $wp_kodepos = mysqli_real_escape_string($DBLink, $rw['CPM_WP_KODEPOS']);
      $op_letak = mysqli_real_escape_string($DBLink, $rw['CPM_OP_LETAK']);
      $op_rt = mysqli_real_escape_string($DBLink, $rw['CPM_OP_RT']);
      $op_rw = mysqli_real_escape_string($DBLink, $rw['CPM_OP_RW']);
      $op_kelurahan = mysqli_real_escape_string($DBLink, $rw['CPM_OP_KELURAHAN']);
      $op_kecamatan = mysqli_real_escape_string($DBLink, $rw['CPM_OP_KECAMATAN']);
      $op_kabupaten = mysqli_real_escape_string($DBLink, $rw['CPM_OP_KABUPATEN']);
      if ($rw['CPM_PAYMENT_TIPE'] == 2) {
        $bphtb_dibayar = $rw['CPM_KURANG_BAYAR'];
      } else {
        $bphtb_dibayar = $ccc;
      }
      $op_nomor = mysqli_real_escape_string($DBLink, $rw['CPM_OP_NOMOR']);
      $noktp = mysqli_real_escape_string($DBLink, $rw['CPM_WP_NOKTP']);
      //$payment_flag = '';
      $saved_date = date('Y-m-d H:i:s');
      $luas_tanah = mysqli_real_escape_string($DBLink, $rw['CPM_OP_LUAS_TANAH']);
      $luas_bangunan = mysqli_real_escape_string($DBLink, $rw['CPM_OP_LUAS_BANGUN']);
      $hasil = ($rw['CPM_OP_NJOP_TANAH'] * $rw['CPM_OP_LUAS_TANAH']) + ($rw['CPM_OP_NJOP_BANGUN'] * $rw['CPM_OP_LUAS_BANGUN']);
      if ($hasil > $rw['CPM_OP_HARGA']) {
        $npop = $hasil;
      } else {
        $npop = mysqli_real_escape_string($DBLink, $rw['CPM_OP_HARGA']);
      }
      $hak = mysqli_real_escape_string($DBLink, $rw['CPM_OP_JENIS_HAK']);
      $notaris = mysqli_real_escape_string($DBLink, $rw['CPM_SSB_AUTHOR']);
      $author = mysqli_real_escape_string($DBLink, $rw['CPM_SSB_AUTHOR']);
      $npwp = mysqli_real_escape_string($DBLink, $rw['CPM_WP_NPWP']);
    }

    if ($rs === false) {
      echo "Error select1:" . $qry;
      die("Error");
    }
    $query2 = "select DISTINCT c.nm_lengkap from tbl_reg_user_notaris c join cppmod_ssb_doc b 
		on ( c.userId = b.CPM_SSB_AUTHOR)
		where b.CPM_SSB_AUTHOR = '$notaris'";
    $hasil = mysqli_query($DBLink, $query2);
    $nama_notaris = mysqli_fetch_array($hasil);
    $notaris = $nama_notaris['nm_lengkap'];
    $payment_flag = 0;
    if (intval($ccc) == 0) {
      $payment_flag = 1;
      $payment_paid = $saved_date;
      $payment_bank_code = $this->getConfigValue("aBPHTB", "KODE_DAERAH") . "9999";
      $payment_settlement_date = date('Ymd');
      $bphtb_collectible = 0;
    }
    $dbName = $this->getConfigValue($a, 'BPHTBDBNAME');
    $dbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
    $dbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
    // $dbPwd = $this->getConfigValue($a, '');
    $dbTable = $this->getConfigValue($a, 'BPHTBTABLE');
    $dbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
    $dbLimit = $this->getConfigValue($a, 'TENGGAT_WAKTU');
    // Connect to lookup database
    // die(var_dump($dbHost, $dbUser, $dbPwd, $dbName));
    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);



    //kode daerah
    $sql = "SELECT * FROM CENTRAL_APP_CONFIG WHERE CTR_AC_KEY = 'KODE_DAERAH'";
    $res2 = mysqli_query($DBLink, $sql);
    while ($row = mysqli_fetch_assoc($res2)) {
      $kode_daerah = $row['CTR_AC_VALUE'];
    }

    //format kode bayar baru
    $jns = '02';
    $search_code = $jns;
    $length = 6;

    $today = date('Y-m-d');
    $pecahkan = explode('-', $today);
    $tah = substr($pecahkan[0], -2);;
    $bul = $pecahkan[1];
    $tahbul = $kode_daerah . $tah . $bul;

    /* 	---------------------------------------------------------------------------------
		|	KODE BAYAR 
		*/
    $query = "SELECT MAX(SUBSTRING(payment_code,9, {$length})) nomor FROM ssb WHERE PAYMENT_CODE LIKE '{$tahbul}%________'";
    $res = mysqli_query($DBLinkLookUp, $query);
    $nomor = 1;
    if ($data = mysqli_fetch_assoc($res)) {
      $nomor = $data['nomor'] + 1;
    }

    $payment_code = $tahbul . str_pad($nomor, $length, '0', STR_PAD_LEFT) . $jns;
    // 	---------------------------------------------------------------------------------

    /* 	---------------------------------------------------------------------------------
		|	KODE NTPD utk BPN // 180324010001
		*/
    $query = "SELECT RIGHT(ntpd,4) AS urut FROM ssb WHERE LEFT(ntpd,8)='{$tahbul}' ORDER BY ntpd DESC LIMIT 0,1";
    $res = mysqli_query($DBLinkLookUp, $query);
    $ntpd_x = 1;
    if ($data = mysqli_fetch_assoc($res)) {
      $ntpd_x = ((int)$data['urut']) + 1;
    }
    $ntpd = $tahbul . sprintf("%04d", $ntpd_x);
    // 	---------------------------------------------------------------------------------

    if (intval($ccc) == 0) {
      // echo $bphtb_dibayar;
      // die;
      $query2 = "INSERT INTO $dbTable (wp_nama,wp_alamat,wp_rt,wp_rw,wp_kelurahan,wp_kecamatan,wp_kabupaten,wp_kodepos,op_letak,
			op_rt,op_rw,op_kelurahan,op_kecamatan,op_kabupaten,bphtb_dibayar,op_nomor,saved_date,wp_noktp,id_switching,expired_date,
			payment_flag,op_luas_tanah,op_luas_bangunan,bphtb_npop,bphtb_jenis_hak,bphtb_notaris,wp_npwp,author,payment_paid,payment_bank_code,PAYMENT_SETTLEMENT_DATE,bphtb_collectible, payment_code,ntpd) VALUES (
			'$wp_nama','$wp_alamat','$wp_rt','$wp_rw','$wp_kelurahan','$wp_kecamatan','$wp_kabupaten','$wp_kodepos','$op_letak',
			'$op_rt','$op_rw','$op_kelurahan','$op_kecamatan','$op_kabupaten','$bphtb_dibayar','$op_nomor','$saved_date','$noktp','$id_switching',
			DATE_ADD(DATE(saved_date), INTERVAL {$dbLimit} DAY),'$payment_flag','$luas_tanah','$luas_bangunan','$npop','$hak','$notaris','$npwp', '$author','$payment_paid','$payment_bank_code','$payment_settlement_date','$bphtb_collectible', '$payment_code','$ntpd')";
    } else {
      // echo $bphtb_dibayar . " perta";
      // die;
      $query2 = "INSERT INTO $dbTable (wp_nama,wp_alamat,wp_rt,wp_rw,wp_kelurahan,wp_kecamatan,wp_kabupaten,wp_kodepos,op_letak,
			op_rt,op_rw,op_kelurahan,op_kecamatan,op_kabupaten,bphtb_dibayar,op_nomor,saved_date,wp_noktp,id_switching,expired_date,
			payment_flag,op_luas_tanah,op_luas_bangunan,bphtb_npop,bphtb_jenis_hak,bphtb_notaris,wp_npwp,author, payment_code,ntpd) VALUES (
			'$wp_nama','$wp_alamat','$wp_rt','$wp_rw','$wp_kelurahan','$wp_kecamatan','$wp_kabupaten','$wp_kodepos','$op_letak',
			'$op_rt','$op_rw','$op_kelurahan','$op_kecamatan','$op_kabupaten','$bphtb_dibayar','$op_nomor','$saved_date','$noktp','$id_switching',
			DATE_ADD(DATE(saved_date), INTERVAL {$dbLimit} DAY),'$payment_flag','$luas_tanah','$luas_bangunan','$npop','$hak','$notaris','$npwp', '$author', '$payment_code','$ntpd')";
    }
    // echo $query2;
    // die;
    // (var_dump($DBLinkLookUp));

    // var_dump($bphtb_dibayar);
    // die;	
    $r = mysqli_query($DBLinkLookUp, $query2);
    if ($r === false) {
      die("Error Insertxx: " . mysqli_error($DBLinkLookUp) . ' ' . $a);
    }
  }

  private function validation($str, &$err)
  {
    $OK = true;
    $j = count($str);
    $err = "";
    /* for ($i=0; $i<$j ; $i++) {
          if (($i!=31) && ($i!=32) && ($i!=33) && ($i!=34) && ($i!=35) && ($i!=37)) {
          if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
          $err .= $str[$i] ."<br>\n";
          $OK = false;
          }
          }
          if ($str[30]==2) {
          if (($i==31) || ($i==32) || ($i==33) || ($i==37)) {
          if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
          $err .= $str[$i] ."<br>\n";
          $OK = false;
          }
          }
          }
          if ($str[30]==3) {
          if ($i==34) {
          if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
          $err .= $str[$i] ."<br>\n";
          $OK = false;
          }
          }
          }
          if ($str[30]==4) {
          if ($i==35) {
          if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
          $err .= $str[$i] ."<br>\n";
          $OK = false;
          }
          }
          }
          } */
    return $OK;
  }



  function setSubmitNewVersion($sts)
  {

    global $data, $DBLink;

    $dat = $data;
    //print_r($appDbLink);
    $data = array();
    //$data[0] = $_REQUEST['tax-services-office']? $_REQUEST['tax-services-office'] :"Error: Nama Kantor Pelayanan Pajak Pratama tidak boleh dokosongkan !";
    //$data[1] = $_REQUEST['tax-services-office-code']? $_REQUEST['tax-services-office-code'] :"Error: Kode Kantor Pelayanan Pajak Pratama tidak boleh dikosongkan !";
    $data[0] = "-";
    $data[1] = "-";
    $data[2] = @isset($_REQUEST['name']) ? $_REQUEST['name'] : "Error: Nama Wajib Pajak tidak boleh dikosongkan!";
    $data[3] = @isset($_REQUEST['npwp']) ? $_REQUEST['npwp'] : "Error: NPWP tidak boleh dikosongkan!";
    $data[4] = @isset($_REQUEST['address']) ? $_REQUEST['address'] : "Error: Alamat tidak boleh dikosongkan!";
    $data[5] = "-";
    $data[6] = @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan'] : "Error: Kelurahan tidak boleh dikosongkan!";
    $data[7] = @isset($_REQUEST['rt']) ? $_REQUEST['rt'] : "Error: RT tidak boleh dikosongkan!";
    $data[8] = @isset($_REQUEST['rw']) ? $_REQUEST['rw'] : "Error: RW tidak boleh dikosongkan!";
    $data[9] = @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "Error: Kecamatan tidak boleh dikosongkan!";
    $data[10] = @isset($_REQUEST['kabupaten']) ? $_REQUEST['kabupaten'] : "Error: Kabupaten tidak boleh dikosongkan!";
    $data[11] = @isset($_REQUEST['zip-code']) ? $_REQUEST['zip-code'] : "Error: Kode POS tidak boleh dikosongkan!";
    $data[12] = @isset($_REQUEST['name2']) ? $_REQUEST['name2'] : "Error: NOP PBB tidak boleh dikosongkan!";
    $data[13] = @isset($_REQUEST['address2']) ? $_REQUEST['address2'] : "Error: Alamat Objek Pajak tidak boleh dikosongkan!";
    $data[14] = "-";
    $data[15] = @isset($_REQUEST['kelurahan2']) ? $_REQUEST['kelurahan2'] : "Error: Kelurahan Objek Pajak tidak boleh dikosongkan!";
    $data[16] = @isset($_REQUEST['rt2']) ? $_REQUEST['rt2'] : "Error: RT Objek Pajak tidak boleh dikosongkan!";
    $data[17] = @isset($_REQUEST['rw2']) ? $_REQUEST['rw2'] : "Error: RW Objek Pajak tidak boleh dikosongkan!";
    $data[18] = @isset($_REQUEST['kecamatan2']) ? $_REQUEST['kecamatan2'] : "Error: Kecamatan Objek Pajak tidak boleh dikosongkan!";
    $data[19] = @isset($_REQUEST['kabupaten2']) ? $_REQUEST['kabupaten2'] : "Error: Kabupaten Objek Pajak tidak boleh dikosongkan!";
    $data[20] = @isset($_REQUEST['zip-code2']) ? $_REQUEST['zip-code2'] : "Error: Kode POS Objek Pajak tidak boleh dikosongkan!";
    $data[21] = @isset($_REQUEST['right-year']) ? $_REQUEST['right-year'] : "Error: Tahun SPPT PBB tidak boleh dikosongkan!";
    $data[22] = @isset($_REQUEST['land-area']) ? $_REQUEST['land-area'] : "Error: Luas Tanah tidak boleh dikosongkan!";
    $data[23] = @isset($_REQUEST['land-njop']) ? $_REQUEST['land-njop'] : "Error: NJOP Tanah tidak boleh dikosongkan!";
    $data[24] = @isset($_REQUEST['building-area']) ? $_REQUEST['building-area'] : "Error: Luas Bangunan tidak boleh dikosongkan!";
    $data[25] = @isset($_REQUEST['building-njop']) ? $_REQUEST['building-njop'] : "Error: NJOP Bangunan tidak boleh dikosongkan!";
    $data[26] = @isset($_REQUEST['right-land-build']) ? $_REQUEST['right-land-build'] : "";
    $data[27] = @isset($_REQUEST['trans-value']) ? $_REQUEST['trans-value'] : "Error: Harga transasksi tidak boleh dikosongkan!";
    $data[28] = @isset($_REQUEST['certificate-number']) ? $_REQUEST['certificate-number'] : "Error: Nomor sertifikat tidak boleh dikosongkan!";
    $data[29] = @isset($_REQUEST['hd-npoptkp']) ? $_REQUEST['hd-npoptkp'] : "Error: Nomor sertifikat tidak boleh dikosongkan!";
    $data[30] = @isset($_REQUEST['RadioGroup1']) ? $_REQUEST['RadioGroup1'] : "Error: Pilihan Jumlah Setoran tidak dipilih!";
    $data[31] = @isset($_REQUEST['jsb-choose']) ? $_REQUEST['jsb-choose'] : "Error: Pilihan jenis tidak dipilih!";
    $data[32] = @isset($_REQUEST['jsb-choose-number']) ? $_REQUEST['jsb-choose-number'] : "Error: Nomor surat tidak boleh dikosongkan!";
    $data[33] = @isset($_REQUEST['jsb-choose-date']) ? $_REQUEST['jsb-choose-date'] : "Error: Tanggal surat tidak boleh dikosongkan!";
    $data[34] = "-"; //$_REQUEST['pdsk-choose']? $_REQUEST['pdsk-choose']:"Error: Pengurangan tidak dipilih!";
    $data[35] = @isset($_REQUEST['jsb-etc']) ? $_REQUEST['jsb-etc'] : "Error: Keterangan lain-lain tidak boleh dikosongkan!";
    $data[36] = @isset($_REQUEST['jsb-total-before']) ? $_REQUEST['jsb-total-before'] : "Error: Akumulasi nilai perolehan hak sebelumnya tidak boleh di kosongkan!";
    $data[37] = @isset($_REQUEST['jsb-choose-role-number']) ? $_REQUEST['jsb-choose-role-number'] : "Error: No Aturan KDH tidak boleh di kosongkan!";
    $data[38] = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "Error: Nomor KTP tidak boleh dikosongkan!";
    $data[39] = @isset($_REQUEST['jsb-choose-percent']) ? $_REQUEST['jsb-choose-percent'] : 0;
    $data[40] = @isset($_REQUEST['nama-wp-lama']) ? $_REQUEST['nama-wp-lama'] : "Error: Nama WP Lama tidak boleh dikosongkan!";
    $data[41] = @isset($_REQUEST['nama-wp-cert']) ? $_REQUEST['nama-wp-cert'] : "Error: Nama WP sesuai Sertifikat tidak boleh dikosongkan!";
    $data[43] = @isset($_REQUEST['jsb-choose-fraction1']) ? $_REQUEST['jsb-choose-fraction1'] : "1";
    $data[44] = @isset($_REQUEST['jsb-choose-fraction2']) ? $_REQUEST['jsb-choose-fraction2'] : "1";
    $data[45] = @isset($_REQUEST['op-znt']) ? $_REQUEST['op-znt'] : "";
    $data[46] = @isset($_REQUEST['pengurangan-aphb']) ? $_REQUEST['pengurangan-aphb'] : "1";
    $denda = @isset($_REQUEST['denda-value']) ? $_REQUEST['denda-value'] : "0";
    $pdenda = @isset($_REQUEST['denda-percent']) ? $_REQUEST['denda-percent'] : "0";
    $koordinat = @isset($_REQUEST['koordinat']) ? $_REQUEST['koordinat'] : "Error: titik koordinat tidak boleh dikosongkan!";

    //tambahan
    $cpm_nopendaftaran = @isset($_REQUEST['cpm_nopendaftaran']) ? $_REQUEST['cpm_nopendaftaran'] : "0";

    if (($data[29] == "0") || ($data[29] == 0)) {
      if (!$this->getNOKTP($_REQUEST['noktp'])) {
        //print_r($_REQUEST['right-land-build']);
        if ($_REQUEST['right-land-build'] == 5) {
          $data[29] = $this->getConfigValue('NPOPTKP_WARIS');
        } else if (($_REQUEST['right-land-build'] == 30) || ($_REQUEST['right-land-build'] == 31) || ($_REQUEST['right-land-build'] == 32) || ($_REQUEST['right-land-build'] == 33)) {
          $data[29] = 0;
        } else {
          $data[29] = $this->getConfigValue('NPOPTKP_STANDAR');
        }
      }
    }


    $pAPHB = "";
    if ($_REQUEST['right-land-build'] == 33 || $_REQUEST['right-land-build'] == 7) {
      $pAPHB = $data[46];
    } else {
      $pAPHB = "";
    }
    //print_r($data);
    $typeSurat = '';
    $typeSuratNomor = '';
    $typeSuratTanggal = '';
    $typePengurangan = '';
    $typeLainnya = '';
    $trdate = date("Y-m-d H:i:s");
    $opr = $dat->uname;
    $version = (1 + $_REQUEST['ver-doc']) . ".0";
    $nokhd = "";
    $pengurangansplit = explode(".", $data[39]);
    $pengurangan = $pengurangansplit[1];
    $kdpengurangan = $pengurangansplit[0];
    $config_laik_btn = $this->getConfigValue('CONFIG_PASAR_BTN_LOCK');
    if ($data[30] == 2) {
      $typeSurat = $data[31];
      $typeSuratNomor = $data[32];
      $typeSuratTanggal = $data[33];
      $nokhd = $data[37];
    } else if ($data[30] == 3) {
      $typePengurangan = $data[34];
    } else if ($data[30] == 4) {
      $typeLainnya = $data[35];
    } else if ($data[30] == 5) {
      $typePecahan  = $data[43] . "/" . $data[44];
    }
    $pengenaan = 0;
    if (($_REQUEST['right-land-build'] == 5) || ($_REQUEST['right-land-build'] == 4) || ($_REQUEST['right-land-build'] == 31)) {
      $pengenaan = $this->getConfigValue('PENGENAAN_HIBAH_WARIS');
    }
    //	if ($this->validation($data,$err)) {
    $iddoc = c_uuid();
    $refnum = c_uuid();
    $tranid = c_uuid();
    if ($data[30] == 2) {
      $ccc = $kurang_bayar;
    } else {
      $ccc = $this->getBPHTBPayment($data[24], $data[25], $data[22], $data[23], $data[27], $pengurangan, $data[26], $data[29], $pengenaan, $aphbt, $denda);
    }
    // please note %d in the format string, using %s would be meaningless
    $query = sprintf(
      "INSERT INTO cppmod_ssb_doc (
						CPM_NO_PENDAFTARAN, 
						CPM_SSB_ID,
						CPM_KPP,
						CPM_KPP_ID,
						CPM_WP_NAMA,
						CPM_WP_NPWP,
						CPM_WP_ALAMAT,
						CPM_WP_RT,
						CPM_WP_RW,
						CPM_WP_KELURAHAN,
						CPM_WP_KECAMATAN,
						CPM_WP_KABUPATEN,
						CPM_WP_KODEPOS,
						CPM_OP_NOMOR,
						CPM_OP_LETAK,
						CPM_OP_RT,
						CPM_OP_RW,
						CPM_OP_KELURAHAN,
						CPM_OP_KECAMATAN,
						CPM_OP_KABUPATEN,
						CPM_OP_KODEPOS,
						CPM_OP_THN_PEROLEH,
						CPM_OP_LUAS_TANAH,
						CPM_OP_LUAS_BANGUN,
						CPM_OP_NJOP_TANAH,
						CPM_OP_NJOP_BANGUN,
						CPM_OP_JENIS_HAK,
						CPM_OP_HARGA,
						CPM_OP_NMR_SERTIFIKAT,
						CPM_OP_NPOPTKP,
						CPM_PAYMENT_TIPE,
						CPM_PAYMENT_TIPE_SURAT,
						CPM_PAYMENT_TIPE_SURAT_NOMOR,
						CPM_PAYMENT_TIPE_SURAT_TANGGAL,
						CPM_PAYMENT_TIPE_PENGURANGAN,
						CPM_PAYMENT_TIPE_OTHER,
						CPM_SSB_CREATED,
						CPM_SSB_AUTHOR,
						CPM_SSB_VERSION,
						CPM_SSB_AKUMULASI,
						CPM_PAYMENT_TIPE_KHD_NOMOR,
						CPM_WP_NOKTP, 
						CPM_WP_NAMA_LAMA,
						CPM_WP_NAMA_CERT, 
						CPM_PAYMENT_TIPE_PECAHAN, 
						CPM_PAYMENT_TYPE_KODE_PENGURANGAN, 
						CPM_OP_ZNT, 
						CPM_PENGENAAN, 
						CPM_CONFIG_LAIK_BTN, 
						CPM_APHB,
						CPM_DENDA,
						CPM_PERSEN_DENDA,
						CPM_BPHTB_BAYAR,
						KOORDINAT
						) 
		VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
		'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
      $cpm_nopendaftaran,
      mysqli_real_escape_string($DBLink, $iddoc),
      '',
      '',
      mysqli_real_escape_string($DBLink, $data[2]),
      mysqli_real_escape_string($DBLink, $data[3]),
      mysqli_real_escape_string($DBLink, nl2br($data[4])),
      mysqli_real_escape_string($DBLink, $data[7]),
      mysqli_real_escape_string($DBLink, $data[8]),
      mysqli_real_escape_string($DBLink, $data[6]),
      mysqli_real_escape_string($DBLink, $data[9]),
      mysqli_real_escape_string($DBLink, $data[10]),
      mysqli_real_escape_string($DBLink, $data[11]),
      mysqli_real_escape_string($DBLink, $data[12]),
      mysqli_real_escape_string($DBLink, nl2br($data[13])),
      mysqli_real_escape_string($DBLink, $data[16]),
      mysqli_real_escape_string($DBLink, $data[17]),
      mysqli_real_escape_string($DBLink, $data[15]),
      mysqli_real_escape_string($DBLink, $data[18]),
      mysqli_real_escape_string($DBLink, $data[19]),
      mysqli_real_escape_string($DBLink, $data[20]),
      mysqli_real_escape_string($DBLink, $data[21]),
      mysqli_real_escape_string($DBLink, $data[22]),
      mysqli_real_escape_string($DBLink, $data[24]),
      mysqli_real_escape_string($DBLink, $data[23]),
      mysqli_real_escape_string($DBLink, $data[25]),
      mysqli_real_escape_string($DBLink, $data[26]),
      mysqli_real_escape_string($DBLink, $data[27]),
      mysqli_real_escape_string($DBLink, $data[28]),
      mysqli_real_escape_string($DBLink, $data[29]),
      mysqli_real_escape_string($DBLink, $data[30]),
      mysqli_real_escape_string($DBLink, $typeSurat),
      mysqli_real_escape_string($DBLink, $typeSuratNomor),
      mysqli_real_escape_string($DBLink, $typeSuratTanggal),
      mysqli_real_escape_string($DBLink, $pengurangan),
      mysqli_real_escape_string($DBLink, $typeLainnya),
      mysqli_real_escape_string($DBLink, $trdate),
      mysqli_real_escape_string($DBLink, $opr),
      mysqli_real_escape_string($DBLink, $version),
      mysqli_real_escape_string($DBLink, $data[36]),
      mysqli_real_escape_string($DBLink, $nokhd),
      mysqli_real_escape_string($DBLink, $data[38]),
      mysqli_real_escape_string($DBLink, $data[40]),
      mysqli_real_escape_string($DBLink, $data[41]),
      mysqli_real_escape_string($DBLink, $typePecahan),
      mysqli_real_escape_string($DBLink, $kdpengurangan),
      mysqli_real_escape_string($DBLink, $data[45]),
      $pengenaan,
      $config_laik_btn,
      $pAPHB,
      $denda,
      $pdenda,
      $ccc,
      $koordinat
    );

    //print_r ($query);exit;

    $result = mysqli_query($DBLink, $query);
    if ($result === false) {
      //handle the error here
      echo mysqli_error($DBLink);
    }

    if ($final == 2) {
      $this->save_berkas($iddoc);
    }

    //tambahan upload
    $trans_id = $_REQUEST['trsid'];
    $querys = "SELECT CPM_TRAN_SSB_ID FROM cppmod_ssb_tranmain WHERE CPM_TRAN_ID =  '" . $trans_id . "'";
    $tampilkan_ssb_id = mysqli_query($DBLink, $querys);
    while ($rows = mysqli_fetch_assoc($tampilkan_ssb_id)) {
      $ssb_id = $rows['CPM_TRAN_SSB_ID'];
    }

    $query = sprintf("UPDATE cppmod_ssb_berkas SET CPM_SSB_DOC_ID='%s'
			WHERE CPM_SSB_DOC_ID='%s' ", $iddoc, $ssb_id);
    $result = mysqli_query($DBLink, $query);

    $query = sprintf("UPDATE cppmod_ssb_upload_file SET CPM_SSB_ID='%s'
			WHERE CPM_SSB_ID='%s' ", $iddoc, $ssb_id);
    $result = mysqli_query($DBLink, $query);

    //end tambahan upload

    $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_SSB_NEW_VERSION='%s'
			WHERE CPM_TRAN_ID='%s' ", $version, $_REQUEST['trsid']);
    $result = mysqli_query($DBLink, $query);

    $query = "INSERT INTO cppmod_ssb_tranmain (CPM_TRAN_ID,CPM_TRAN_REFNUM,CPM_TRAN_SSB_ID,
			CPM_TRAN_SSB_VERSION,CPM_TRAN_STATUS,CPM_TRAN_FLAG,CPM_TRAN_DATE,CPM_TRAN_CLAIM,
			CPM_TRAN_OPR_NOTARIS) VALUES
			 ('" . $tranid . "','" . $refnum . "','" . $iddoc . "','" . $version . "','" . $sts . "','0','" . $trdate . "','0','" . $opr . "')";
    //echo $query;
    $result = mysqli_query($DBLink, $query);
    if ($result === false) {
      //handle the error here
      echo mysqli_error($DBLink);
    } else {
      echo "Data Berhasil disimpan ...! ";
      $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=4";

      $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
      echo "\n<script language=\"javascript\">\n";
      echo "	function delayer(){\n";
      echo "		window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
      echo "	}\n";
      echo "	Ext.onReady(function(){\n";
      echo "		setTimeout('delayer()', 2000);\n";
      echo "	});\n";
      echo "</script>\n";
    }

    //	} else {
    //		echo $err;
    //		echo formSSB($data,true);
    //}
  }

  function setSubmit($type, $info, $info_disetujui, $vers)
  {


    global $data, $DBLink;
    $this->submit = true;
    $this->getData();
    $idtran = c_uuid();
    $refnum = c_uuid();
    $version = $this->jsondata->data[0]->CPM_TRAN_SSB_VERSION;
    $x = $this->jsondata->data[0]->CPM_TRAN_SSB_ID;
    $sts = $this->jsondata->data[0]->CPM_TRAN_STATUS;
    $opr = $this->jsondata->data[0]->CPM_TRAN_OPR_NOTARIS;
    $dispenda1 = $this->jsondata->data[0]->CPM_TRAN_OPR_DISPENDA_1;
    $dispenda2 = $this->jsondata->data[0]->CPM_TRAN_OPR_DISPENDA_2;
    $dispenda3 = $this->jsondata->data[0]->CPM_TRAN_OPR_DISPENDA_3;
    $dispenda4 = $this->jsondata->data[0]->CPM_TRAN_OPR_DISPENDA_4;
    $dispenda5 = $this->jsondata->data[0]->CPM_TRAN_OPR_DISPENDA_5;
    if ($this->dispenda1)
      $dispenda1 = $data->uname;
    if ($this->dispenda2)
      $dispenda2 = $data->uname;
    if ($this->dispenda3)
      $dispenda3 = $data->uname;
    if ($this->dispenda4)
      $dispenda4 = $data->uname;
    if ($this->dispenda5)
      $dispenda5 = $data->uname;

    // var_dump($dispenda3);
    // die;
    $status = '3';
    $trdate = date("Y-m-d H:i:s");
    $sts_lpgn = 0;
    if ($type == 2) {
      $status = '4';
      if ($vers == 992) $sts_lpgn = 92;
    } else if (($type == 1) && ($this->dispenda2)) {
      $status = '6';
    } else if (($type == 1) && ($this->dispenda3)) {
      $status = '7';
    } else if (($type == 1) && ($this->dispenda4)) {
      $status = '5';
    } else if (($type == 1) && ($this->dispenda5)) {
      $status = '5';
    }
    // var_dump($status);
    // die;
    // var_dump($dispenda1);die;
    $cekGW = $this->cekGateway($x);
    $query = sprintf(
      "UPDATE cppmod_ssb_tranmain SET CPM_TRAN_STATUS='%s',
        												CPM_TRAN_FLAG='%s',
        												CPM_TRAN_OPR_NOTARIS='%s',
        												CPM_TRAN_FERIF_LAPANGAN = '%s'
						WHERE CPM_TRAN_SSB_ID='%s' AND CPM_TRAN_STATUS='%s' AND CPM_TRAN_FLAG=0",
      $sts,
      "1",
      mysqli_real_escape_string($DBLink, $opr),
      $sts_lpgn,
      mysqli_real_escape_string($DBLink, $x),
      $sts
    );
    // die(var_dump($query));
    $result = mysqli_query($DBLink, $query);

    $query = sprintf("INSERT INTO cppmod_ssb_tranmain (CPM_TRAN_ID,
        													CPM_TRAN_REFNUM,
        													CPM_TRAN_SSB_ID,
        													CPM_TRAN_SSB_VERSION,
        													CPM_TRAN_STATUS,
        													CPM_TRAN_FLAG,
        													CPM_TRAN_DATE,
        													CPM_TRAN_CLAIM,
        													CPM_TRAN_OPR_NOTARIS,
        													CPM_TRAN_OPR_DISPENDA_1,
        													CPM_TRAN_OPR_DISPENDA_2,
        													CPM_TRAN_INFO,
        													CPM_TRAN_INFO_DISETUJUI,
        													CPM_TRAN_FERIF_LAPANGAN) 
		VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')", $idtran, $refnum, $x, $version, $status, '0', mysqli_real_escape_string($DBLink, $trdate), '', $opr, $dispenda1, $dispenda2, nl2br($info), nl2br($info_disetujui), $sts_lpgn);
    $result = mysqli_query($DBLink, $query);


    if ($result === false) {
      //handle the error here
      echo "Error 2" . mysqli_error($DBLink);
    } else {
      //edit
      if ($this->dispenda2) { #(persetujuan)
        $action = ($status == 6) ? 11 : 12; #menyetujui : menolak
      } else if ($this->dispenda3) { //penilaian
        $action = ($status == 7) ? 9 : 10; #menyetujui : menolak

      } else if ($this->dispenda4) { //penetapan
        $action = ($status == 5) ? 8 : 7; #menyetujui : menolak

      } else { #verifikasi
        $action = ($status == 3) ? 6 : 5; #menyetujui : menolak
      }



      $get = "select CPM_OP_NOMOR,CPM_WP_NAMA,CPM_SSB_AUTHOR
                from cppmod_ssb_doc 
                where CPM_SSB_ID ='" . mysqli_real_escape_string($DBLink, $x) . "'";
      $getData = mysqli_query($DBLink, $get);
      while ($setData = mysqli_fetch_array($getData)) {
        $cpm_op_nomor = $setData['CPM_OP_NOMOR'];
        $cpm_wp_nama = $setData['CPM_WP_NAMA'];
        $cpm_ssb_author = $setData['CPM_SSB_AUTHOR'];
      }



      $log_verifikasi = "insert into cppmod_ssb_log(
                                    CPM_SSB_ID,
                                    CPM_SSB_LOG_ACTOR,
                                    CPM_SSB_LOG_ACTION,
                                    CPM_OP_NOMOR,
                                    CPM_WP_NAMA,
                                    CPM_SSB_AUTHOR) 
                            values ('" . mysqli_real_escape_string($DBLink, $x) . "',
                                    '" . mysqli_real_escape_string($DBLink, $data->uname) . "',                                   
                                    '" . mysqli_real_escape_string($DBLink, $action) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_op_nomor) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_wp_nama) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_ssb_author) . "')";

      mysqli_query($DBLink, $log_verifikasi);

      echo "Data Berhasil disimpan ...!";
      //   if ($action ==4) {
      // $getdt = "select * FROM cppmod_ssb_berkas WHERE CPM_SSB_DOC_ID='".$x."'";
      // // die(mysqli_real_escape_string($DBLink, $iddoc));

      // $resultw = mysqli_query($DBLink, $getdt);
      // if ($resultw === false)
      //     echo mysqli_error('error 1');
      // $rows = mysqli_fetch_array($resultw);
      //    $params = "a=" . $_REQUEST['a'] . "&m=mUploadBerkas&f=fUploadBerkas&tab=0&svcid=".$rows['CPM_BERKAS_ID'];
      //   }
      // else{
      $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=1";
      // }
      $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
      echo "\n<script language=\"javascript\">\n";
      echo "	function delayer(){\n";
      echo "		window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
      echo "	}\n";
      echo "	Ext.onReady(function(){\n";
      echo "		setTimeout('delayer()', 2000);\n";
      echo "	});\n";
      echo "</script>\n";
    }
  }

  function getData()
  {
    global $data, $DBLink;
    $query = sprintf("SELECT *,IF((TIMEDIFF(NOW(), B.CPM_TRAN_CLAIM_DATETIME))<'%s',
						IF(B.CPM_TRAN_OPR_DISPENDA_1<>'%s','1','0'),'0') AS CLAIM FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B 
						WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'", $this->maxtime, $data->uname, mysqli_real_escape_string($DBLink, $this->iddoc));

    // echo $query;
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
      echo $query . "<br>";
      echo mysqli_error($DBLink);
    }
    $json = new Services_JSON();
    $this->jsondata = $json->decode($this->mysql2json($res, "data"));
    $dt = $this->jsondata;
    $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_READ=1 
					WHERE CPM_TRAN_SSB_ID='%s'", $this->iddoc);
    $result = mysqli_query($DBLink, $query);
    if ($res === false) {
      echo "3:" . $query;
    }
    for ($i = 0; $i < count($dt->data); $i++) {
      $this->claim = false;
      // echo $this->claim.'X1 ';
      //if (($dt->data[$i]->CLAIM != '1') && ($this->approved) && ($dt->data[$i]->CPM_TRAN_STATUS != '4') && (($this->dispenda1) && ($dt->data[$i]->CPM_TRAN_STATUS != '3'))) {
      if (($this->approved) && ($dt->data[$i]->CPM_TRAN_STATUS != '4') && (($this->dispenda1) && ($dt->data[$i]->CPM_TRAN_STATUS != '3'))) {
        $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_CLAIM='%s', CPM_TRAN_CLAIM_DATETIME='%s', CPM_TRAN_OPR_DISPENDA_1='%s'  
					WHERE CPM_TRAN_SSB_ID='%s' AND CPM_TRAN_FLAG='%s'", "1", date('Y-m-d H:i:s'), $data->uname, $this->iddoc, "0");
        // $result = mysqli_query($DBLink, $query);
        if ($res === false) {
          echo "2:" . $query;
        }
        $this->claim = true;
        // echo $this->claim.'X2 ';
        if ($dt->data[$i]->CPM_TRAN_STATUS == '4')
          $this->stsrejected = true;
      }
      if (($this->dispenda2) && ($dt->data[$i]->CPM_TRAN_STATUS == '3')) {
        if ($this->right != 1) {
          $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_CLAIM='%s', CPM_TRAN_CLAIM_DATETIME='%s', CPM_TRAN_OPR_DISPENDA_2='%s'  
					WHERE CPM_TRAN_SSB_ID='%s' AND CPM_TRAN_FLAG='%s'", "1", date('Y-m-d H:i:s'), $data->uname, $this->iddoc, "0");
        }
        // $result = mysqli_query($DBLink, $query);
        if ($res === false) {
          echo "2:" . $query;
        }
        $this->claim = true;
        // echo $this->claim.'X3 ';
        if ($dt->data[$i]->CPM_TRAN_STATUS == '4')
          $this->stsrejected = true;
      }
    }
  }

  private function addApprove()
  {
    $HTML = "";
    //echo $this->approved ? "true":"false";
    //echo $this->claim ? "true":"false";
    //echo $this->stsrejected ? "true":"false";
    //echo $_REQUEST["f"];
    if ($_SESSION['role'] == 'rmBPHTBStaff') {
      $HTML .= "<tr><td>&nbsp;</td><td><table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\" style=\"background-color:#f1c3e2\">
					  <tr>
						<td colspan=\"2\" class=\"result-analisis\" style=\"border-radius: 10px 10px 0px 0px;\"><strong>Hasil analisis</strong></td>
					  </tr>
					  

						
					  <tr>
						<td width=\"144\" class=\"result-analisis\"><label>
							  <input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" onclick=\"enableE(this,1);\"/>
							  Ditolak</label></td>
						<td class=\"result-analisis\">Alasan</td>
						</tr>
					  <tr>
						<td class=\"result-analisis\">&nbsp;</td>
						<td class=\"result-analisis\"><label for=\"textarea\"></label>
						  <textarea name=\"textarea-info\" id=\"textarea-info\" cols=\"50\" rows=\"8\" disabled></textarea></td>
					  </tr>
					  <tr>
						<td class=\"result-analisis\">&nbsp;</td>
						<td align=\"right\" class=\"result-analisis\"><label for=\"textarea\" style=\"border-radius: 0px 0px 10px 10px;\"></label>
						  <input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Submit\"></textarea></td>
					  </tr>
					</table></td></tr>";
    } else {
      if ($this->approved && (!$this->stsrejected)) {
        // if asli ada claimnya
        // if ($this->approved && $this->claim && (!$this->stsrejected)) {
        $HTML .= "<tr><td>&nbsp;</td><td><table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\" style=\"background-color:#f1c3e2\">
					  <tr>
						<td colspan=\"2\" class=\"result-analisis\" style=\"border-radius: 10px 10px 0px 0px;\"><strong>Hasil analisis</strong></td>
					  </tr>
					  
					  <tr>
						<td width=\"144\" class=\"result-analisis\"><label>
							  <input type=\"radio\" name=\"RadioGroup1\" value=\"1\" id=\"RadioGroup1_0\" checked onclick=\"enableE(this,0);\"/>
							  Disetujui</label></td>
						<td class=\"result-analisis\">Alasan</td>
					  </tr>
					  <tr>
						<td class=\"result-analisis\">&nbsp;</td>
						<td class=\"result-analisis\"><label for=\"textarea\"></label>
						  <textarea name=\"textarea-info1\" id=\"textarea-info1\" cols=\"50\" rows=\"8\"></textarea></td>
					  </tr>						
					  <tr>
						<td width=\"144\" class=\"result-analisis\">
							<label>
							  <input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" onclick=\"enableE(this,1);\"/>
							  Ditolak
						  	</label>
						</td>
						<td class=\"result-analisis\">
							Alasan
						</td>
					  </tr>
					  <tr>
						<td class=\"result-analisis\">&nbsp;</td>
						<td class=\"result-analisis\"><label for=\"textarea\"></label>
						  <textarea name=\"textarea-info\" id=\"textarea-info\" cols=\"50\" rows=\"8\" disabled></textarea></td>
					  </tr>
					  <tr>
						<td class=\"result-analisis\">&nbsp;</td>
						<td class=\"result-analisis\">
							<label>
							  <input type=\"radio\" name=\"RadioGroup99\" value=\"991\" id=\"RadioGroup99_1\" checked disabled/>
							  Kelengkapan Document
						  	</label>
							<label>
							  <input type=\"radio\" name=\"RadioGroup99\" value=\"992\" id=\"RadioGroup99_2\" disabled/>
							  Verifikasi Lapangan
						  	</label>
					  	</td>
					  </tr>
					  <tr>
						<td class=\"result-analisis\">						 
             <input type=\"submit\" name=\"kurangbayar\" id=\"kurangbayar\" value=\"Kurang Bayar\"></input></td>
            </td>
						<td align=\"right\" class=\"result-analisis\"><label for=\"textarea\" style=\"border-radius: 0px 0px 10px 10px;\"></label>
						  <input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Submit\"></textarea></td>
					  </tr>
					</table></td></tr>";
      }
    }


    return $HTML;
  }

  function getNOKTP($noktp)
  {
    global $DBLink;

    $N1 = $this->getConfigValue("aBPHTB", 'NPOPTKP_STANDAR');
    $N2 = $this->getConfigValue('NPOPTKP_WARIS');
    $day = $this->getConfigValue("BATAS_HARI_NPOPTKP");
    $dbLimit = $this->getConfigValue('TENGGAT_WAKTU');

    $CHECK_NPOPTKP_KTP_PAYMENT = $this->getConfigValue('CHECK_NPOPTKP_KTP_PAYMENT');

    $dbName = $this->getConfigValue('BPHTBDBNAME');
    $dbHost = $this->getConfigValue('BPHTBHOSTPORT');
    //$dbPwd = $this->getConfigValue('BPHTBPASSWORD');
    $dbPwd = $this->getConfigValue('');
    $dbTable = $this->getConfigValue('BPHTBTABLE');
    $dbUser = $this->getConfigValue('BPHTBUSERNAME');
    // Connect to lookup database
    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
    //payment_flag, mysqli_real_escape_string($payment_flag),
    $tahun = date('Y');
    $qry = "select * 
		        from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
	            where A.CPM_WP_NOKTP = '{$noktp}' and CPM_OP_THN_PEROLEH= '{$tahun}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
				AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 30 AND A.CPM_OP_JENIS_HAK <> 31 
				AND A.CPM_OP_JENIS_HAK <> 32 AND A.CPM_OP_JENIS_HAK <> 33";
    //print_r($qry); 
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      return false;
    }

    if ($CHECK_NPOPTKP_KTP_PAYMENT == 0) {
      if (mysqli_num_rows($res)) {
        //$num_rows = mysqli_num_rows($res);
        // while($row = mysqli_fetch_assoc($res)){
        // $query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
        // FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
        // //print_r($query2);
        // $r = mysqli_query($query2, $DBLinkLookUp);
        // if ( $r === false ){
        // die("Error Insertxx: ".mysqli_error());
        // }
        // if(mysqli_num_rows ($r)){

        // while($rowx = mysqli_fetch_assoc($r)){
        // if ($rowx['EXPRIRE']) {
        // return false;
        // }else{
        // $query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
        // $r2 = mysqli_query($query3, $DBLinkLookUp);
        // if ( $r2 === false ){
        // die("Error Insertxx: ".mysqli_error());
        // }
        // if (mysqli_num_rows($r2)) {
        // return true;
        // }
        // }
        // }
        // return true;
        // }else return false;
        // }
        return true;
      } else return false;
    } else {
      if (mysqli_num_rows($res)) {
        $num_rows = mysqli_num_rows($res);
        while ($row = mysqli_fetch_assoc($res)) {
          $query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
								FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
          //print_r($query2);
          $r = mysqli_query($DBLinkLookUp, $query2);
          if ($r === false) {
            die("Error Insertxx: " . mysqli_error($DBLinkLookUp));
          }
          if (mysqli_num_rows($r)) {

            while ($rowx = mysqli_fetch_assoc($r)) {
              if ($rowx['EXPRIRE']) {
                return false;
              } else {
                $query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
                $r2 = mysqli_query($DBLinkLookUp, $query3);
                if ($r2 === false) {
                  die("Error Insertxx: " . mysqli_error($DBLinkLookUp));
                }
                if (mysqli_num_rows($r2)) {
                  return true;
                }
              }
            }
            return true;
          } else return false;
        }
      } else return false;
    }
  }

  
  function jenishak($js)
  {
    global $DBLink;

    $texthtml = "<select name=\"right-land-build\" id=\"right-land-build\" onchange=\"checkTransLast();hidepasar();cekAPHB();\" style=\"height: 30px\">";
    $qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
    //echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);

    while ($data = mysqli_fetch_assoc($res)) {
      if ($js == $data['CPM_KD_JENIS_HAK']) {
        $selected = "selected";
      } else {
        $selected = "";
      }
      $texthtml .= "<option value=\"" . $data['CPM_KD_JENIS_HAK'] . "\" " . $selected . " >" . str_pad($data['CPM_KD_JENIS_HAK'], 2, "0", STR_PAD_LEFT) . " " . $data['CPM_JENIS_HAK'] . "</option>";
    }
    $texthtml .= "			      </select>";
    return $texthtml;
  }

  function aphb($aphb)
  {
    global $DBLink;

    $texthtml = " Hamparan <select name=\"pengurangan-aphb\" id=\"pengurangan-aphb\" onchange=\"checkTransLast();\">
					    <option value=\"\">Pilih</option>
					    ";
    $qry = "select * from cppmod_ssb_aphb ORDER BY CPM_APHB_KODE asc";
    //echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);
    if (($aphb != $data['CPM_APHB']) || ($aphb == "")) {
      $selected = "";
    } else {
      $selected = "selected";
    }
    while ($data = mysqli_fetch_assoc($res)) {

      $texthtml .= "<option value=\"" . $data['CPM_APHB'] . "\" " . $selected . " >" . str_pad($data['CPM_APHB_KODE'], 2, "0", STR_PAD_LEFT) . ":" . $data['CPM_APHB'] . "</option>";
    }
    $texthtml .= "			      </select>";
    return $texthtml;
  }

  function save_berkas($idssb)
  {
    global $DBLink, $data;
    $nop = @isset($_REQUEST['name2']) ? $_REQUEST['name2'] : "";
    $jp = @isset($_REQUEST['right-land-build']) ? $_REQUEST['right-land-build'] : "";
    $nopel = getNoPel($jp);
    $alamat_op = @isset($_REQUEST['address2']) ? $_REQUEST['address2'] : "";
    $kec_op = @isset($_REQUEST['kecamatan2']) ? $_REQUEST['kecamatan2'] : "";
    $kel_op = @isset($_REQUEST['kelurahan2']) ? $_REQUEST['kelurahan2'] : "";

    $noktp = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "";
    $npwp = @isset($_REQUEST['npwp']) ? $_REQUEST['npwp'] : "";
    $npwp_as = $npwp;
    if ($npwp == "") {
      $npwp_as = $noktp;
    }
    $nama_wp = @isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
    $harga = @isset($_REQUEST['trans-value']) ? $_REQUEST['trans-value'] : "";
    $opr = $data->uname;
    $iddoc = $idssb;
    $qry = sprintf(
      "INSERT INTO cppmod_ssb_berkas (
	            CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP, 
	            CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,CPM_BERKAS_NAMA_WP,
	            CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL, 
	            CPM_BERKAS_HARGA_TRAN,CPM_SSB_DOC_ID           
	            ) VALUES ('%s','%s','%s',
	                    '%s','%s','%s',                    
	                    '%s','%s','%s',
	                    '%s','%s')",
      mysqli_escape_string($DBLink, $nop),
      date('d-m-Y'),
      mysqli_escape_string($DBLink, $alamat_op),
      mysqli_escape_string($DBLink, $kel_op),
      mysqli_escape_string($DBLink, $kec_op),
      mysqli_escape_string($DBLink, $npwp_as),
      mysqli_escape_string($DBLink, $nama_wp),
      mysqli_escape_string($DBLink, $jp),
      mysqli_escape_string($DBLink, $nopel),
      mysqli_escape_string($DBLink, $harga),
      mysqli_escape_string($DBLink, $iddoc)
    );


    $result = mysqli_query($DBLink, $qry);
    if ($result === false) {
      //handle the error here
      print_r(mysqli_error($DBLink) . $qry);
    }
  }

  function formSSB($dat, $edit)
  {

    global $data, $DBLink, $a;
    $pengenaan = $this->getConfigValue("1", 'PENGENAAN_HIBAH_WARIS');
    $configAPHB = $this->getConfigValue("1", 'CONFIG_APHB');
    $configPengenaan = $this->getConfigValue("1", 'CONFIG_PENGENAAN');

    ($configAPHB == "1") ? $display_aphb = "" : $display_aphb = "style=\"display:none\"";
    ($configPengenaan == "1") ? $display_pengenaan = "" : $display_pengenaan = "style=\"display:none\"";
    $a = strval($dat->CPM_OP_LUAS_BANGUN) * strval($dat->CPM_OP_NJOP_BANGUN) + strval($dat->CPM_OP_LUAS_TANAH) * strval($dat->CPM_OP_NJOP_TANAH);
    $b = strval($dat->CPM_OP_HARGA);
    $npop = 0;
    $type = $dat->CPM_PAYMENT_TIPE;
    $sel = $dat->CPM_PAYMENT_TIPE_SURAT;
    $sel_min = $dat->CPM_PAYMENT_TIPE_PENGURANGAN;
    $info = $dat->CPM_PAYMENT_TIPE_OTHER;
    $typeR = $dat->CPM_OP_JENIS_HAK;
    $APHB = $dat->CPM_APHB;
    $tAPHB = explode("/", $APHB);

    if ($APHB != "") {
      $pAPHB = $tAPHB[0] / $tAPHB[1];
    } else {
      $pAPHB = 0;
    }
    $msgNewVer = "";

    if ($sel_min != 0) {
      $option_pengurangan = "<option value=\"" . $dat->CPM_PAYMENT_TYPE_KODE_PENGURANGAN . "." . $dat->CPM_PAYMENT_TIPE_PENGURANGAN . "\">Kode " . $dat->CPM_PAYMENT_TYPE_KODE_PENGURANGAN . " : " . $dat->CPM_PAYMENT_TIPE_PENGURANGAN . "%</option>";
    } else {
      $option_pengurangan = "<option value=\"0\">0</option>";
    }
    /* $NPOPTKP =  $this->getConfigValue("1",'NPOPTKP_STANDAR');

          if (($typeR==4) || ($typeR==6)){
          $NPOPTKP =  $this->getConfigValue("1",'NPOPTKP_WARIS');
          }

          if($this->getNOKTP ($dat->CPM_WP_NOKTP,$dat->CPM_OP_NOMOR,$dat->CPM_SSB_CREATED)) {
          $NPOPTKP = 0;
          } */
    $NPOPTKP = $dat->CPM_OP_NPOPTKP;
    if ($dat->CPM_TRAN_STATUS == '4') {
      if ($dat->CPM_TRAN_SSB_NEW_VERSION)
        $msgNewVer = "<br><i>Dokumen ini telah dibuat versi barunya yaitu : " . $dat->CPM_TRAN_SSB_NEW_VERSION . "</i>";
      $infoReject = "\n<br><br><div id=\"info-reject\" class=\"msg-info\"> <strong>Ditolak karena : </strong><br/>" . str_replace("\n", "<br>", $dat->CPM_TRAN_INFO) . $msgNewVer . "</div>\n";
    }
    $sel1 = "";
    $sel2 = "";
    $sel3 = "";
    $sel4 = "";
    $sel5 = "";

    $c1 = "";
    $c2 = "";
    $c3 = "";
    $c4 = "";

    if ($sel_min == '1')
      $sel4 = "selected=\"selected\"";
    if ($sel_min == '2')
      $sel5 = "selected=\"selected\"";

    if ($sel == '1')
      $sel1 = "selected=\"selected\"";
    if ($sel == '2')
      $sel2 = "selected=\"selected\"";
    if ($sel == '3')
      $sel3 = "selected=\"selected\"";

    if ($type == '1')
      $c1 = "checked=\"checked\"";
    if ($type == '2')
      $c2 = "checked=\"checked\"";
    if ($type == '3')
      $c3 = "checked=\"checked\"";
    if ($type == '4')
      $c4 = "checked=\"checked\"";
    if ($type == '5')
      $c5 = "checked=\"checked\"";
    if ($b <= $a)
      $npop = $a;
    else
      $npop = $b;

    $readonly = "";
    // $btnSave = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan sebagai versi baru\" />"; #OLD
    // $btnSaveFinal = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan versi baru dan finalkan\" /></td>"; #OLD
    $btnSave = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan sebagai versi baru\" />";
    $btnSaveFinal = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan versi baru dan finalkan\" /></td>";
    $msgClaim = "";
    $berkas_berkas = 1;

    if (($dat->CLAIM != '0') && ($dat->CPM_TRAN_OPR_NOTARIS != $data->uname)) {
      $readonly = "readonly=\"readonly\"";
      $btnSave = "";
      $btnSaveFinal = "";
      $msgClaim = "<div id=\"msg-claim\">Data ini sedang di akses oleh user lain, mohon tunggu sebentar !</div><br>";
    }
    if ($dat->CPM_TRAN_SSB_NEW_VERSION) {
      $btnSave = "";
      $btnSaveFinal = "";
      $berkas_berkas = 0;
    }
    $vedit = "false";
    if ($edit)
      $vedit = "true";

    $tNPOPKP = ($npop - strval($dat->CPM_OP_NPOPTKP));
    //print_r($dat->CPM_OP_NPOPTKP);
    if ($tNPOPKP < 0)
      $tNPOPKP = 0;


    $dat->CPM_OP_LUAS_TANAH = number_format($dat->CPM_OP_LUAS_TANAH, 0, '', '');
    $dat->CPM_OP_NJOP_TANAH = number_format($dat->CPM_OP_NJOP_TANAH, 0, '', '');
    $dat->CPM_OP_LUAS_BANGUN = number_format($dat->CPM_OP_LUAS_BANGUN, 0, '', '');
    $dat->CPM_OP_NJOP_BANGUN = number_format($dat->CPM_OP_NJOP_BANGUN, 0, '', '');

    if ($this->getConfigValue("aBPHTB", 'DENDA') == '1') {
      $c_denda = "$(\"#denda-value\").val(0);
				$(\"#denda-percent\").val(0);
				$(\"#denda-percent\").focus(function() {
					if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(\"\");
					}
				  
				});
				$(\"#denda-value\").blur(function() {
						if($(\"#denda-value\").val()==0){
						$(\"#denda-value\").val(0);
					}
					  
					});
					
				$(\"#denda-percent\").blur(function() {
						if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(0);
					}
					  
					});
					";
      $kena_denda = "<tr>
					<td>Denda &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"denda-percent\" id=\"denda-percent\" value=\"" . $dat->CPM_PERSEN_DENDA . "\"  onKeyPress=\"return numbersonly(this, event);checkTransaction();\" onkeyup=\"checkTransaction()\" title=\"Denda\" size=\"2\"  /> %</td>
					<td id=\"tdenda\" align=\"right\"><input type=\"text\" name=\"denda-value\" id=\"denda-value\" value=\"" . $dat->CPM_DENDA . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"checkTransaction()\" title=\"Denda\" readonly=\"readonly\"/></td>
				  </tr>";
      $kena_denda2 = "";
      $kena_denda2 = "";
    } else {
      $c_denda = "$(\"#denda-value\").val(0);
						$(\"#denda-percent\").val(0);";
      $kena_denda = "";
      $kena_denda2 = "<input type=\"hidden\" name=\"denda-percent\" id=\"denda-percent\" />
						  <input type=\"hidden\" name=\"denda-value\" id=\"denda-value\"/>";
    }
    $hitungAPHB = $this->getConfigValue("1", 'HITUNG_APHB');
    $html = "<link rel=\"stylesheet\" href=\"./function/BPHTB/notaris/func-detail-notaris.css\" type=\"text/css\">\n";
    $html .= "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
    $html .= "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";

    //$html .= "<script src=\"./inc/js/jquery-1.3.2.min.js\"></script>\n";
    $html .= "<script src=\"./inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
    $html .= "<script src=\"inc/js/jquery.formatCurrency-1.4.0.min.js\" type=\"text/javascript\"></script>\n";
    $html .= "<script src=\"./function/BPHTB/notaris/func-new-ssb.js?ver=0\"></script>\n";
    $html .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"./inc/datepicker/datepickercontrol.css\">\n";
    $html .= "<script src=\"./inc/datepicker/datepickercontrol.js\"></script>\n";
    $html .= "<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css\">";
    $html .= "
		<!-- <link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\"> -->
		<script language=\"javascript\">
					var cnpoptkp = " . $dat->CPM_OP_NPOPTKP . ";
					var edit = " . $vedit . ";
					var hitungaphb = " . $hitungAPHB . ";
					var configaphb = " . $configAPHB . ";
					var configpengenaan = " . $configPengenaan . ";
					$(function(){
						
						$('#loaderCek').hide();
						$('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
						var jh=$(\"select#right-land-build option:selected\").val();
						if(jh==33 || jh==7){
							$('#pengurangan-aphb').removeAttr(\"disabled\", \"disabled\");
						}
						$(\"#name2\").mask(\"" . $this->getConfigValue($a, 'PREFIX_NOP') . "?99999999999999999\");
						$(\"#noktp\").focus(function() {
						  $(\"#noktp\").val(\"" . $this->getConfigValue($a, 'PREFIX') . "\");
						});
						$(\"#noktp\").keyup(function() {
							var input = $(this),
							text = input.val().replace(/[^./0-9-_\s]/g, \"\");
							if(/_|\s/.test(text)) {
								text = text.replace(/_|\s/g, \"\");
								// logic to notify user of replacement
							}
							input.val(text);
						});
						function setElementsVal(data){
            var elVal = data.split('*');
            
            //current
            
            $(\"#nama-wp-lama\").val(elVal[0]);
            $(\"#nama-wp-cert\").val(elVal[0]);
            /*$(\"#name\").val(elVal[0]);
            $(\"#npwp\").val('0');
            $(\"#noktp\").val('0000000000000000');
            $(\"#address\").val(elVal[1]);
            $(\"#kelurahan\").val(elVal[2]);
            $(\"#rt\").val(elVal[3]);
            $(\"#rw\").val(elVal[4]);
            $(\"#kecamatan\").val(elVal[5]);
            $(\"#kabupaten\").val(elVal[6]);
            $(\"#zip-code\").val(elVal[7]);*/            
            $(\"#address2\").val(elVal[8]);
            $(\"#kelurahan2\").val(elVal[9]);
            $(\"#rt2\").val(elVal[10]);
            $(\"#rw2\").val(elVal[11]);
            $(\"#kecamatan2\").val(elVal[12]);
            $(\"#kabupaten2\").val(elVal[13]);            
            $(\"#zip-code2\").val('0');
			$(\"#zip-code\").val('0');
			$(\"#koordinat\").val('1, 1');
            $(\"#land-area\").val(elVal[14]);
            $(\"#land-njop\").val(elVal[15]);
            $(\"#building-area\").val(elVal[16]);
            $(\"#building-njop\").val(elVal[17]);
            $(\"#right-year\").val(elVal[18]);
			$(\"#op-znt\").val(elVal[19]);
            
            //old
            $(\"#nmWPOld\").val(elVal[0]);
            $(\"#alamatWPOld\").val(elVal[1]);
            $(\"#kelurahanWPOld\").val(elVal[2]);
            $(\"#rtWPOld\").val(elVal[3]);
            $(\"#rwWPOld\").val(elVal[4]);
            $(\"#kecamatanWPOld\").val(elVal[5]);
            $(\"#kabupatenWPOld\").val(elVal[6]);
            $(\"#alamatOPOld\").val(elVal[8]);
            $(\"#kelurahanOPOld\").val(elVal[9]);
            $(\"#rtOPOld\").val(elVal[10]);
            $(\"#rwOPOld\").val(elVal[11]);
            $(\"#kecamatanOPOld\").val(elVal[12]);
            $(\"#kabupatenOPOld\").val(elVal[13]);            
            $(\"#luasBumiOld\").val(elVal[14]);
            $(\"#njopBumiOld\").val(elVal[15]);
            $(\"#luasBangunanOld\").val(elVal[16]);
            $(\"#njopBangunanOld\").val(elVal[17]);
            $(\"#tahunSPPTOld\").val(elVal[18]);
            
            addSN();
            addET();
            checkTransaction();            
            setDisabledVal(true);
            $(\"#cekUpdateData\").css('display','');
            changeColor('#eeeeee');
        }
        
        $(\"#chkDisElements\").click(function(){
            if ($(\"#chkDisElements\").is(\":checked\")){
                setDisabledVal(false);
                changeColor('#ffffff');
                $(\"#isPBB\").val(1);
            }else{
                setDisabledVal(true);
                changeColor('#eeeeee');
                $(\"#isPBB\").val(0);
            }        
        });
            
        function setDisabledVal(valDis){
            
            $(\"#nama-wp-lama\").attr('readonly',valDis);
            $(\"#nama-wp-cert\").attr('readonly',valDis);
            /*$(\"#name\").attr('readonly',valDis);
            $(\"#npwp\").attr('readonly',valDis);
            $(\"#noktp\").attr('readonly',valDis);
            $(\"#address\").attr('readonly',valDis);
            $(\"#kelurahan\").attr('readonly',valDis);
            $(\"#rt\").attr('readonly',valDis);
            $(\"#rw\").attr('readonly',valDis);
            $(\"#kecamatan\").attr('readonly',valDis);
            $(\"#kabupaten\").attr('readonly',valDis);
            $(\"#zip-code\").attr('readonly',valDis);
            $(\"#address2\").attr('readonly',valDis);
            $(\"#kelurahan2\").attr('readonly',valDis);
            $(\"#rt2\").attr('readonly',valDis);
            $(\"#rw2\").attr('readonly',valDis);
            $(\"#kecamatan2\").attr('readonly',valDis);
            $(\"#kabupaten2\").attr('readonly',valDis);            
            $(\"#zip-code2\").attr('readonly',valDis);*/
            //$(\"#land-area\").attr('readonly',valDis);
            $(\"#land-njop\").attr('readonly',valDis);
               if ($(\"#building-area\").value =='0' || $(\"#building-area\").value ==''){
            $(\"#building-area\").attr('readonly',valDis);
            }
            $(\"#building-njop\").attr('readonly',valDis);
            $(\"#right-year\").attr('readonly',valDis);
        }

        function changeColor(vColor){
            
            $(\"#nama-wp-lama\").css('background-color',vColor);
            $(\"#nama-wp-cert\").css('background-color',vColor);
            /*$(\"#name\").css('background-color',vColor);
            $(\"#npwp\").css('background-color',vColor);
            $(\"#noktp\").css('background-color',vColor);
            $(\"#address\").css('background-color',vColor);
            $(\"#kelurahan\").css('background-color',vColor);
            $(\"#rt\").css('background-color',vColor);
            $(\"#rw\").css('background-color',vColor);
            $(\"#kecamatan\").css('background-color',vColor);
            $(\"#kabupaten\").css('background-color',vColor);
            $(\"#zip-code\").css('background-color',vColor);*/
            $(\"#address2\").css('background-color',vColor);
            $(\"#kelurahan2\").css('background-color',vColor);
            $(\"#rt2\").css('background-color',vColor);
            $(\"#rw2\").css('background-color',vColor);
            $(\"#kecamatan2\").css('background-color',vColor);
            $(\"#kabupaten2\").css('background-color',vColor);            
            $(\"#zip-code2\").css('background-color',vColor);
            //$(\"#land-area\").css('background-color',vColor);
            $(\"#land-njop\").css('background-color',vColor);
            if ($(\"#building-area\").value =='0' || $(\"#building-area\").value ==''){
                    $(\"#building-area\").css('background-color',vColor);
            }
            
            $(\"#building-njop\").css('background-color',vColor);
            $(\"#right-year\").css('background-color',vColor);
        }
					});
					/*$(\"#certificate-number\").keyup(function() {
						var input = $(this),
						text = input.val().replace(/[^./0-9-_\s]/g, \"\");
						if(/_|\s/.test(text)) {
							text = text.replace(/_|\s/g, \"\");
							// logic to notify user of replacement
						}
						input.val(text);
					});*/
		function setForm(d){
		$(\"#name\").val(d.CPM_WP_NAMA);
		$(\"#address\").val(d.CPM_WP_ALAMAT);
		$(\"#rt\").val(d.CPM_WP_RT);
		$(\"#rw\").val(d.CPM_WP_RW);
		//$(\"#WP_PROPINSI\").val(PROV);
		$(\"#kabupaten\").val(\"CIANJUR\");
		$(\"#kecamatan\").val(d.CPM_WP_KECAMATAN);
		$(\"#kelurahan\").val(d.CPM_WP_KELURAHAN);
		}

		function checkDukcapil(){
		var appID	= '" . $_REQUEST['a'] . "';	
		var noKTP 	= $('#noktp').val();
		
		$('#loaderCek').show();
		$.ajax({
			type: 'POST',
			data: '&noKTP='+noKTP+'&appID='+appID,
			url: './function/BPHTB/notaris/svcCheckDukcapil.php',
			success: function(res){  
				d=jQuery.parseJSON(res);
				if(d.res==1){
					$('#loaderCek').hide();
					$(\"<div>\"+d.msg+\"</div>\").dialog({
						modal: true,
						buttons: {
							Ya: function() {
								$(this).dialog( \"close\" );
								setForm(d.dat);
							},
							Tidak: function() {
								$(this).dialog( \"close\" );
							}
						}
					});
				} else if(d.res==0){
					$('#loaderCek').hide();
					$(\"<div>\"+d.msg+\"</div>\").dialog({
						modal: true,
						buttons: {
							OK: function() {
								$(this).dialog( \"close\" );
							}
						}
					});
				}
			}	
		});
		}

		</script>
		<style>
			.myButton {
			-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
			-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
			box-shadow:inset 0px 1px 0px 0px #ffffff;
			background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ffffff), color-stop(1, #f6f6f6));
			background:-moz-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
			background:-webkit-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
			background:-o-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
			background:-ms-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
			background:linear-gradient(to bottom, #ffffff 5%, #f6f6f6 100%);
			filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f6f6f6',GradientType=0);
			background-color:#ffffff;
			-moz-border-radius:6px;
			-webkit-border-radius:6px;
			border-radius:6px;
			border:2px solid #dcdcdc;
			display:inline-block;
			cursor:pointer;
			color:#666666;
			font-family:Arial;
			font-size:11px;
			font-weight:bold;
			padding:6px 6px;
			text-decoration:none;
			text-shadow:0px 1px 0px #ffffff;
		}
		.myButton:hover {
			background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #f6f6f6), color-stop(1, #ffffff));
			background:-moz-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
			background:-webkit-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
			background:-o-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
			background:-ms-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
			background:linear-gradient(to bottom, #f6f6f6 5%, #ffffff 100%);
			filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f6f6f6', endColorstr='#ffffff',GradientType=0);
			background-color:#f6f6f6;
		}
		.myButton:active {
			position:relative;
			top:1px;
		}

		}
		</style>
		<div id=\"main-content\"><form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
			  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
				<tr>
				  <td colspan=\"2\" align='center'><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong>$infoReject</td>
				</tr>
				
				<tr>
				  <td align=\"center\" valign=\"top\"><strong><font size=\"+2\">A</font></strong></td>
				  <td width=\"97%\">
				  	<table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
					<tr>
					  <td width=\"10\"><div align=\"right\">1.</div></td>
					  <td width=\"200\">NOP PBB</td>
					  <td width=\"200\"><input type=\"text\" name=\"name2\" id=\"name2\" value=\"" . $dat->CPM_OP_NOMOR . "\" onBlur=\"checkNOP(this);\" maxlength=\"18\" size=\"25\"  " . $readonly . " title=\"NOP PBB\"/></td>
					  <td>Nama WP Lama : </td>
					  <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" value=\"" . $dat->CPM_WP_NAMA_LAMA . "\" size=\"30\" maxlength=\"30\" title=\"Nama WP Lama\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\"/>
					  </td>
					  </tr>
					<tr valign='top'>
					  <td><div align=\"right\">2.</div></td>
					  <td>Lokasi Objek Pajak</td>
					  <td><textarea  name=\"address2\" id=\"address2\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" cols=\"30\" rows=\"4\" title=\"Lokasi Objek Pajak\" " . $readonly . ">" . str_replace("<br />", "\n", $dat->CPM_OP_LETAK) . "</textarea></td>
                      <td>Nama WP Sesuai Sertifikat : </td>
					  <td><input type=\"text\" name=\"nama-wp-cert\" id=\"nama-wp-cert\" value=\"" . $dat->CPM_WP_NAMA_CERT . "\" size=\"30\" maxlength=\"30\" title=\"Nama WP Sesuai Sertifikat\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\"/>
					  </td>    
					</tr>
					<tr>
					  <td><div align=\"right\">3.</div></td>
					  <td>Kelurahan/Desa</td>
					  <td><input type=\"text\" name=\"kelurahan2\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kelurahan2\" value=\"" . $dat->CPM_OP_KELURAHAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kelurahan/Desa\"/></td>
					  <td>&nbsp;</td>
					  <td><input type=\"text\" name=\"op-znt\" id=\"op-znt\"  value=\"" . $dat->CPM_OP_ZNT . "\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">4.</div></td>
					  <td>RT/RW</td>
					  <td colspan=\"3\"><input type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" value=\"" . $dat->CPM_OP_RT . "\" onKeyPress=\"return nextFocus(this, event)\" " . $readonly . " title=\"RT\"/>
						/
						<input type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"3\" value=\"" . $dat->CPM_OP_RW . "\" onKeyPress=\"return nextFocus(this, event)\" " . $readonly . " title=\"RW\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">5.</div></td>
					  <td>Kecamatan</td>
					  <td colspan=\"3\"><input type=\"text\" name=\"kecamatan2\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kecamatan2\" value=\"" . $dat->CPM_OP_KECAMATAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kecamatan\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">6.</div></td>
					  <td>Kabupaten/Kota</td>
					  <td colspan=\"3\"><input type=\"text\" name=\"kabupaten2\" id=\"kabupaten2\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $dat->CPM_OP_KABUPATEN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kabupaten\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">7.</div></td>
					  <td>Kode Pos</td>
					  <td colspan=\"3\"><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" value=\"" . $dat->CPM_OP_KODEPOS . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" " . $readonly . " title=\"Kode POS\"/></td>
					</tr>

					<tr>
					  <td><div align=\"right\">8.</div></td>
					  <td>Nomor sertifikat tanah</td>
					  <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" value=\"" . $dat->CPM_OP_NMR_SERTIFIKAT . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"30\" maxlength=\"40\" " . $readonly . " title=\"Nomor Sertifikat\"/></td>
					</tr>
				  </table>
				  </td>
				</tr>
				<tr>
				  <td width=\"3%\" align=\"center\" valign=\"top\"><strong><font size=\"+2\">B</font></strong></td>
				  <td>
				  	<table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
					<tr>
					  <td width=\"10\"><div align=\"right\">1.</div></td>
					  <td>Nomor KTP</td>
					  <td><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"" . $dat->CPM_WP_NOKTP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"17\" maxlength=\"24\" title=\"No KTP\" onblur=\"checkTransLast();checkTransaksi();\" />&nbsp;&nbsp;<input type=\"button\" name=\"checkKTP\" id=\"checkKTP\" value=\"Ambil Data Dukcapil\" class=\"myButton\" onclick=\"checkDukcapil();checkTransLast();\"><img src=\"./image/icon/loading.gif\" id=\"loaderCek\"><div id=\"newl\"></div></td>
					</tr>
					<tr>
					  <td><div align=\"right\">2.</div></td>
					  <td>NPWP</td>
					  <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"" . $dat->CPM_WP_NPWP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\"  " . $readonly . " title=\"NPWP\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">3.</div></td>
					  <td width=\"200\">Nama Wajib Pajak</td>
					  <td width=\"\"><input type=\"text\" name=\"name\" id=\"name\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $dat->CPM_WP_NAMA . "\" onkeypress=\"return nextFocus(this,event)\" size=\"30\" maxlength=\"50\" " . $readonly . " title=\"Nama Wajib Pajak\"/></td>
					</tr>
					<tr valign='top'>
					  <td><div align=\"right\">4.</div></td>
					  <td>Alamat Wajib Pajak</td>
					  <td><textarea  name=\"address\" id=\"address\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" cols=\"30\" rows=\"4\" title=\"Alamat Wajib Pajak\" " . $readonly . ">" . str_replace("<br />", "\n", $dat->CPM_WP_ALAMAT) . "</textarea></td>
					</tr>
					<tr>
					  <td><div align=\"right\">5.</div></td>
					  <td>Kelurahan/Desa</td>
					  <td><input type=\"text\" name=\"kelurahan\"  onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kelurahan\" value=\"" . $dat->CPM_WP_KELURAHAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\"  " . $readonly . " title=\"Kelurahan\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">6.</div></td>
					  <td>RT/RW</td>
					  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  value=\"" . $dat->CPM_WP_RT . "\" onkeypress=\"return nextFocus(this,event)\"  " . $readonly . " title=\"RT\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  value=\"" . $dat->CPM_WP_RW . "\" onkeypress=\"return nextFocus(this,event)\"  " . $readonly . " title=\"RW\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">7.</div></td>
					  <td>Kecamatan</td>
					  <td><input type=\"text\" name=\"kecamatan\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kecamatan\"  value=\"" . $dat->CPM_WP_KECAMATAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\"  " . $readonly . " title=\"Kecamatan\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">8.</div></td>
					  <td>Kabupaten/Kota</td>
					  <td><input type=\"text\" name=\"kabupaten\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kabupaten\"  value=\"" . $dat->CPM_WP_KABUPATEN . "\" onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\"  " . $readonly . " title=\"Kabupaten Kota\"/></td>
					</tr>
					<tr>
					  <td><div align=\"right\">9.</div></td>
					  <td>Kode Pos</td>
					  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"" . $dat->CPM_WP_KODEPOS . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" " . $readonly . " title=\"Kode POS\"/></td>
					</tr>

					<tr>
					  <td><div align=\"right\">10.</div></td>
					  <td>Titik Koordinat</td>
					  <td><input type=\"text\" name=\"koordinat\" id=\"koordinat\"  value=\"" . $dat->KOORDINAT . "\" size=\"35\" maxlength=\"300\" " . $readonly . " title=\"Titik Koordinat\"/></td>
					</tr>
				  </table>
				  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
					<tr>
				  <td width=\"14\"><div align=\"right\">11.</div></td>
				  <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
			    </tr>
                <tr>
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\">" . $this->jenishak($typeR) . "</td>
			    <tr>
				<tr id=\"aphb\" " . $display_aphb . ">
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\">" . $this->aphb($APHB) . "</td>
			    </tr>						
				  </table>
				  
				  <table width=\"900\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">
			  <tr>
				<td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></td>
				</tr>
			  <tr>
				<td width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</td>
				<td width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</td>
				<td width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi berdasakan SPPT PBB terjadi perolehan hak tahun 
				  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"" . $dat->CPM_OP_THN_PEROLEH . "\" onKeyPress=\"return numbersonly(this, event)\" " . $readonly . " title=\"Tahun SPPT PBB\"/></td>
				<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</td>
				</tr>
			  <tr>
				<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
				<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
				</tr>
			  <tr>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"land-area\" id=\"land-area\" value=\"" . number_format(strval($dat->CPM_OP_LUAS_TANAH), 0, '', '') . "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" " . $readonly . " title=\"Luas Tanah\"/>
				  m²</td>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" id=\"land-njop\" value=\"" . $dat->CPM_OP_NJOP_TANAH . "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" " . $readonly . " title=\"NJOP Tanah\"/></td>
				<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
				<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">" . number_format(strval($dat->CPM_OP_LUAS_TANAH) * strval($dat->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
			  </tr>
			  <tr>
				<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
				<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
				</tr>
			  <tr>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"building-area\" id=\"building-area\" value=\"" . number_format(strval($dat->CPM_OP_LUAS_BANGUN), 0, '', '0') . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" " . $readonly . " title=\"Luas Bangunan\"/>
			m²</td>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"building-njop\" id=\"building-njop\" value=\"" . $dat->CPM_OP_NJOP_BANGUN . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" " . $readonly . " title=\"NJOP Bangunan\"/></td>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
				<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">" . number_format(strval($dat->CPM_OP_LUAS_BANGUN) * strval($dat->CPM_OP_NJOP_BANGUN), 0, '.', ',') . "</td>
			  </tr>
			  <tr>
				<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
				<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">" . number_format(strval($dat->CPM_OP_LUAS_BANGUN) * strval($dat->CPM_OP_NJOP_BANGUN) + strval($dat->CPM_OP_LUAS_TANAH) * strval($dat->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
			  </tr>
			   
				  </table>
				  <div id=\"nilai-pasar\">
				</div>
				<br>
		12. Harga Transaksi Rp. <input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"" . $dat->CPM_OP_HARGA . "\" onKeyPress=\"return numbersonly(this, event)\"  onchange=\"checkTransaction()\" title=\"Harga Transaksi\"/ onblur=\"loadLaikPasar();\">
			  </td>
				</tr>
				<tr style=\"display:none\">
				  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">C</font></strong></td>
				  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
					<tr>
					  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
					  <td width=\"188\"  id=\"akumulasi\">" . number_format(strval($dat->CPM_SSB_AKUMULASI), 0, '.', ',') . "</td></td>
					</tr>
				  </table></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">C</font></strong></td>
				  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
					  <tr>
						<td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
						<td width=\"188\"><em><strong>Dalam rupiah</strong></em></td>
					  </tr>
					  <tr>
						<td>Nilai Perolehan Objek Pajak (NPOP)</td>
						<td id=\"tNJOP\" align=\"right\">" . number_format($npop, 0, '.', ',') . "</td>
					  </tr>
					  <tr>
						<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
						<td  id=\"NPOPTKP\" align=\"right\">" . number_format($NPOPTKP, 0, '.', ',') . "</td>
					  </tr>
					  <tr>
						<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP) </td>
						<td id=\"tNPOPKP\" align=\"right\">" . number_format($tNPOPKP, 0, '.', ',') . "</td>
					  </tr>
				<tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang terhutang</td>
					<td id=\"tBPHTBTS\" align=\"right\">" . number_format(($tNPOPKP) * 0.05, 0, '.', ',') . "</td>
				  </tr>
				  </tr>
				   <tr " . $display_pengenaan . ">
					<td>Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;<input style=\"border:none\" type=\"text\" id=\"Phibahwaris\" size=\"1\" name=\"Phibahwaris\" value=\"" . $pengenaan . "\" readonly=\"readonly\"/>%</td>
					<td id=\"tPengenaan\" align=\"right\">" . number_format($tNPOPKP * 0.05 * $dat->CPM_PENGENAAN * 0.01, 0, '.', ',') . "</td>
				  </tr>
				  <tr " . $display_aphb . ">
					<td>APHB &nbsp;&nbsp;</td>
					<td id=\"tAPHB\" align=\"right\">" . number_format(($tNPOPKP) * 0.05 * $pAPHB, 0, '.', ',') . "</td>
				  </tr>
				  " . $kena_denda . "" . $kena_denda2 . "
				  <tr>
					<td><strong>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar</strong></td>
					<!-- <td id=\"tBPHTBT\" align=\"right\">" . number_format(($tNPOPKP * 0.05) - ($tNPOPKP * 0.05) * $dat->CPM_PENGENAAN * 0.01, 0, '.', ',') . "</td> -->
					<td id=\"tBPHTBT\" align=\"right\">" . number_format($dat->CPM_BPHTB_BAYAR, 0, '.', ',') . "</td>
				  </tr>
				  </table></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">D</font></strong></td>
				  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong><em>(tekan kotak yang sesuai)</em></td>
					";
    if ($berkas_berkas == 1) {
      $html .= "<td colspan='2' align='right' style='padding-right:50px'>
						<a href=\"#\" onclick=\"cekberkas('" . $data->CPM_SSB_ID . "','" . $data->CPM_OP_JENIS_HAK . "')\">Berkas</a>
						</td>";
    }



    $html .= "
				  </tr>
				<tr>
				  <td width=\"24\" align=\"center\" valign=\"top\"><p>
					<label>
					  <input type=\"radio\" name=\"RadioGroup1\" value=\"1\" id=\"RadioGroup1_0\"  onclick=\"enableE(this,0);\" " . $c1 . " />
					</label>
					<br />
					<br />
				  </p></td>
				  <td width=\"15\" align=\"right\" valign=\"top\">a.</td>
				  <td width=\"583\" valign=\"top\">Penghitungan Wajib Pajak</td>
				</tr>
                                <tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" disabled onclick=\"enableE(this,1);\" " . $c2 . " /></td>
				  <td align=\"right\" valign=\"top\">b.</td>
				  <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\" " . $r2 . ">
					<option value=\"1\" " . $sel1 . " >STPD BPHTB</option>
					<option value=\"2\" " . $sel2 . " >SKPD Kurang Bayar</option>
					<option value=\"3\" " . $sel3 . " >SKPD Kurang Bayar Tambahan</option>
				  </select><font size=\"2\" color=\"red\">*hanya bisa dilakukan di menu kurang bayar</font></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Nomor : 
					<input type=\"text\" name=\"jsb-choose-number\" id=\"jsb-choose-number\" size=\"30\" maxlength=\"30\" value=\"" . $dat->CPM_PAYMENT_TIPE_SURAT_NOMOR . "\" " . $readonly . "\" " . $r2 . " title=\"Nomor Surat Pengurangan\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Tanggal : 
					<input type=\"text\" name=\"jsb-choose-date\" id=\"jsb-choose-date\" datepicker=\"true\" datepicker_format=\"DD/MM/YYYY\" readonly=\"readonly\" size=\"10\" maxlength=\"10\" value=\"" . $dat->CPM_PAYMENT_TIPE_SURAT_TANGGAL . "\" " . $readonly . "\" " . $r2 . " title=\"Tanggal Surat Pengurangan\"/></td>
				</tr>
				
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"3\" id=\"RadioGroup1_4\"  onclick=\"enableE(this,2);\"/></td>
				  <td align=\"right\" valign=\"top\">c.</td>
				  <td valign=\"top\">Pengurangan dihitung sendiri menjadi <select name=\"jsb-choose-percent\" id=\"jsb-choose-percent\" onchange=\"checkTransLast();\">" . $option_pengurangan . "
				    ";
    $qry = "select * from cppmod_ssb_pengurangan";
    //echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);

    while ($data = mysqli_fetch_assoc($res)) {
      $html .= "<option value=\"" . $data['CPM_KODE_PENGURANGAN'] . "." . $data['CPM_PENGURANGAN'] . "\">Kode " . $data['CPM_KODE_PENGURANGAN'] . " : " . $data['CPM_PENGURANGAN'] . "%</option>";
    }
    $html .= "</select></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"4\" id=\"RadioGroup1_6\" onclick=\"enableE(this,3);\" " . $c4 . " /></td>
				  <td align=\"right\" valign=\"top\">d.</td>
				  <td valign=\"top\"><textarea name=\"jsb-etc\" id=\"jsb-etc\" cols=\"35\" rows=\"5\" " . $readonly . " " . $r4 . " title=\"Lain-lain\">" . $info . "</textarea>
				  <input type=\"hidden\" id=\"ver-doc\" value=\"" . $dat->CPM_TRAN_SSB_VERSION . "\" name=\"ver-doc\">
				  <input type=\"hidden\" id=\"trsid\" value=\"" . $dat->CPM_TRAN_ID . "\" name=\"trsid\">
				  </td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"5\" id=\"RadioGroup1_8\"  onclick=\"enableE(this,4);\" " . $c5 . " hidden=\"hidden\" /></td>
				  <td align=\"right\" valign=\"top\"></td>
				  <td valign=\"top\"><!--Khusus untuk waris dan Hibah Pengurangan dihitung sendiri menjadi --> <input type=\"text\" name=\"jsb-choose-fraction1\" id=\"jsb-choose-fraction1\" size=\"1\" maxlength=\"2\" value=\"" . $typePecahan[0] . "\" " . $readonly . " " . $r5 . " title=\"pecahan 1\" onkeyup=\"checkTransaction()\" onKeyPress=\"return numbersonly(this, event)\" hidden=\"hidden\"/><input type=\"text\" name=\"jsb-choose-fraction2\" id=\"jsb-choose-fraction2\" size=\"1\" maxlength=\"2\" value=\"" . $typePecahan[1] . "\" " . $readonly . " " . $r6 . " title=\"pecahan 2\" onkeyup=\"checkTransaction()\" onKeyPress=\"return numbersonly(this, event)\" hidden=\"hidden\"/></td>
				</tr>

			  </table></td>
				</tr>
				<tr>
				<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\">Jumlah yang dibayarkan: " . number_format($dat->CPM_BPHTB_BAYAR, 0, '.', ',') . "</td>
				</tr>
				<tr><input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\"><input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\" value=\"" . $dat->CPM_OP_NPOPTKP . "\">
				<input type=\"hidden\" id=\"cpm_nopendaftaran\" name=\"cpm_nopendaftaran\" value=\"" . $dat->CPM_NO_PENDAFTARAN . "\">
				<td colspan=\"2\" align=\"center\" valign=\"middle\">" . $btnSave . "
				  &nbsp;&nbsp;&nbsp;" . $btnSaveFinal . "
				</tr>
				<tr>
			  <td colspan=\"2\" align=\"center\" valign=\"middle\"><input type=\"hidden\" name=\"role\" id=\"role\" name=\"role\" value=\"" . $this->getRole() . "\"></td>
			</tr>
			  </table>
			</form></div>
			
			 <div id=\"id01\" class=\"w3-modal\">
	    <div class=\"w3-modal-content\">
	      <div id=\"w3-container\">
	        
	        
	      </div>
	    </div>
		</div>";
    return $html;
  }
  function getRole()
  {
    global $DBLink, $data;
    $id = $_REQUEST['a'];
    $qry = "select * from central_user_to_app where CTR_APP_ID = '" . $id . "' and CTR_USER_ID = '" . $data->uid . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      echo $qry . "<br>";
      echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
      return $row['CTR_RM_ID'];
    }
  }
  function getroles($user)
  {
    global $appDbLink;
    $id = $user;
    $qry = "select * from tbl_reg_user_bphtb where userId = '" . $id . "'";
    $res = mysqli_query($appDbLink, $qry);
    if ($res === false) {
      echo $qry . "<br>";
      echo mysqli_error($appDbLink);
    }
    $row = mysqli_fetch_assoc($res);
    while ($row = mysqli_fetch_assoc($res)) {
      return $row['jabatan'];
    }
  }

  function getSPPTInfo($idssb)
  {
    global $a;

    $iErrCode = 0;
    $a = $a;
    //LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
    $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
    $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
    //$DbPwd = $this->getConfigValue($a,'BPHTBPASSWORD');
    $DbPwd = $this->getConfigValue($a, '');
    $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
    $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

    SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
    if ($iErrCode != 0) {
      $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
      if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
      exit(1);
    }

    $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE id_switching = '" . $idssb . "' ORDER BY saved_date DESC limit 1  ";
    return $query;
    $res = mysqli_query($LDBLink, $query);
    if ($res === false) {
      print_r(mysqli_error($LDBLink));
      return "Tidak Ditemukan";
    }
    $json = new Services_JSON();
    $data =  $json->decode($this->mysql2json($res, "data"));
    for ($i = 0; $i < count($data->data); $i++) {

      return $data->data[$i]->PAYMENT_FLAG ? "1" : "0";
    }


    SCANPayment_CloseDB($LDBLink);
    return "Tidak Ditemukan";
  }
  function getnilaipasar($nop, $znt)
  {
    global $DBLink;
    $ceknop = substr($nop, 0, 13);
    $qry = "select * from cppmod_ssb_nilai_pasar WHERE CONCAT(CPM_OP_KELURAHAN_KODE,CPM_OP_BLOK)='{$ceknop}' and CPM_OP_ZNT_KODE='{$znt}' limit 1";
    //echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);
    $row = mysqli_fetch_assoc($res);
    if (mysqli_num_rows($res) >= 1) {
      $npt = $row['CPM_OP_NILAI_PASAR_TANAH'];
    } else {
      $npt = 0;
    }

    return $npt;
  }



  function getjenishak($js)
  {
    global $DBLink;
    $id = null;
    if (isset($appID)) {
      $id = $appID;
    }
    $qry = "select * from cppmod_ssb_jenis_hak where CPM_KD_JENIS_HAK = '" . $js . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      echo $qry . "<br>";
      echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
      return str_pad($row['CPM_KD_JENIS_HAK'], 2, "0", STR_PAD_LEFT) . ". " . $row['CPM_JENIS_HAK'];
    }
  }

  function getjenishakmilik($js)
  {
    global $DBLink;
    $id = null;
    if (isset($appID)) {
      $id = $appID;
    }
    $qry = "select * from cppmod_ssb_jenis_hak_milik where ID_JENIS_MILIK = '" . $js . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      echo $qry . "<br>";
      echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
      return str_pad($row['ID_JENIS_MILIK'], 2, "0", STR_PAD_LEFT) . ". " . $row['NAMA_JENIS_HAK_MILIK'];
    }
  }

  function display()
  {
   
    global $data, $DBLink, $sRootPath;
    //print_r($this->approved);
    $dat = $data;
    $opr = $dat->uname;
    $hitungAPHB = $this->getConfigValue("1", 'HITUNG_APHB');
    $configAPHB = $this->getConfigValue("1", 'CONFIG_APHB');
    $configPengenaan = $this->getConfigValue("1", 'CONFIG_PENGENAAN');

    ($configAPHB == "1") ? $display_aphb = "" : $display_aphb = "style=\"display:none\"";
    ($configPengenaan == "1") ? $display_pengenaan = "" : $display_pengenaan = "style=\"display:none\"";
    $this->getData();
    $data = $this->jsondata->data[0];
    $jenishak = "<span class=\"document-x\">Jual Beli</span>";
    $npop = 0;
    $typeR = $data->CPM_OP_JENIS_HAK;
    $params = "a=" . $_REQUEST['a'] . "&m=modNotarisBPHTB";
    $par1 = $params . "&f=funcKurangBayar&idssb=" . $data->CPM_SSB_ID . "&idtid=" . $data->CPM_TRAN_ID;
    $statusSPPT = $this->getSPPTInfo($data->CPM_SSB_ID);
    $payment_code = ($statusSPPT == 0) ? $this->GetPAYMENTCode(trim($data->CPM_SSB_ID)) : false;
    $expired_date = ($payment_code) ? $payment_code->expired_date : false;
    $payment_code = ($payment_code) ? $payment_code->payment_code : false;
    // var_dump($data->CPM_TRAN_STATUS);
    // die;

    $roleuser = $this->getroles($opr);
    $kurang = "";
    if ((($roleuser == "pejabat dispenda") || ($roleuser == "staff dispenda")) && ($statusSPPT == "1") && ($data->CPM_TRAN_STATUS == '5')) {
      $kurang = "<tr>
				<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\"><a href=\"main.php?param=" . base64_encode($par1) . "\" style=\"text-decoration: none;\"><input type=\"button\" name=\"btn-kurang\" id=\"btn-kurang\" value=\"Kurang Bayar\" /></a></td>
				</tr>";
    }
    /* $NPOPTKP =  $this->getConfigValue("1",'NPOPTKP_STANDAR');



          if (($typeR==4) || ($typeR==6)){
          $NPOPTKP =  $this->getConfigValue("1",'NPOPTKP_WARIS');
          } else {

          }


          if($this->getNOKTP($data->CPM_WP_NOKTP,$data->CPM_OP_NOMOR,$data->CPM_SSB_CREATED)) {
          $NPOPTKP = 0;
          } */
    //print_r("h".$NPOPTKP);
    // pengurangan SPN
    $sqlPenguranganBphtb = "Select * FROM cppmod_ssb_doc_pengurangan where CPM_SSB_ID ='{$data->CPM_SSB_ID}'";
    $res = mysqli_query($DBLink, $sqlPenguranganBphtb);
    $row = mysqli_fetch_assoc($res);
    if (mysqli_num_rows($res) >= 1) {
      $row = $row['nilaipengurangan'];
    }
    // end pengurangan
    $NPOPTKP = $data->CPM_OP_NPOPTKP;

    $a = strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH);
    $b = strval($data->CPM_OP_HARGA);
    if ($data->CPM_OP_JENIS_HAK == '8') {
      $npop = $b;
    } else {
      if ($b <= $a) $npop = $a;
      else $npop = $b;
    }
    if (($data->CPM_PAYMENT_TIPE == '2') && (!is_null($data->CPM_OP_NPOP)))
      $npop = $data->CPM_OP_NPOP;
    // die;
    if ($typeR == 1)
      $jsb = "01. Jual Beli";
    if ($typeR == 2)
      $jsb = "02. Tukar Menukar";
    if ($typeR == 3)
      $jsb = "03. Hibah";
    if ($typeR == 4)
      $jsb = "04. Hibah Wasiat";
    if ($typeR == 5)
      $jsb = "05. Waris";
    if ($typeR == 6)
      $jsb = "06. Pemasukan Dalam Perseroan";
    if ($typeR == 7)
      $jsb = "07. APHB";
    if ($typeR == 8)
      $jsb = "08. Lelang";
    if ($typeR == 9)
      $jsb = "09. Putusan Hakim";
    if ($typeR == 10)
      $jsb = "10. Penggabungan Usaha";
    if ($typeR == 11)
      $jsb = "11. Peleburan Usaha";
    if ($typeR == 12)
      $jsb = "12. Pemekaran Usaha";
    if ($typeR == 13)
      $jsb = "13. Hadiah";
    if ($typeR == 14)
      $jsb = "14. Jual Beli Khusus Perolehan hak RSS melalui KPR bersubsidi";
    if ($typeR == 21)
      $jsb = "21. Pemberian Hak Baru Sebagai Kelanjutan Pelepasan Hak";
    if ($typeR == 22)
      $jsb = "22. Pemberian Hak Baru Diluar Pelepasan Hak";
    if ($typeR == 30)
      $jsb = "30. 1 Wajib Pajak : Jual Beli";
    if ($typeR == 31)
      $jsb = "31. 1 Wajib Pajak : Waris";
    if ($typeR == 32)
      $jsb = "32. 1 Wajib Pajak : Hibah/Waris";
    if ($typeR == 33)
      $jsb = "33. 1 Wajib Pajak : APHB";


    $jenishak = "<span class=\"document-x\">" . $jsb . "</span>";


    $fieldTambahan = "";
    if ($data->CPM_PAYMENT_TIPE != 2) {
      $typepayment = "<span  class=\"document-x\">Penghitungan Wajib Pajak</span>";
    } else {
      if ($data->CPM_PAYMENT_TIPE_SURAT == 1)
        $typepayment = "<span class=\"document-x\">STPD BPHTB</span>";
      if ($data->CPM_PAYMENT_TIPE_SURAT == 2)
        $typepayment = "<span class=\"document-x\">SKPD Kurang Bayar</span>";
      if ($data->CPM_PAYMENT_TIPE_SURAT == 3)
        $typepayment = "<span class=\"document-x\">SKPD Kurang Bayar Tambahan</span>";
      $fieldTambahan = "<tr>
				   <td valign=\"top\" class=\"document-x\">Nomor : " . $data->CPM_PAYMENT_TIPE_SURAT_NOMOR . "</td>
				</tr>
				<tr>
				  <td valign=\"top\" class=\"document-x\">Tanggal : " . $data->CPM_PAYMENT_TIPE_SURAT_TANGGAL . "</td>
				</tr>";
    }

    if ($data->CPM_PAYMENT_TIPE == 3) {
      $fieldTambahan = "<tr>
				   <td valign=\"top\" class=\"document-x\">Penguranan di hitung sendiri menjadi " . $data->CPM_PAYMENT_TIPE_PENGURANGAN . "%</td>
				</tr>
				<tr>
				  <td valign=\"top\" class=\"document-x\">Berdasakan peraturan KDH No : " . $data->CPM_PAYMENT_TIPE_KHD_NOMOR . "</td>
				</tr> ";
    }
    $infoReject = "";


    if ($data->CPM_TRAN_STATUS == '4') {
      $infoReject = "\n<br><br><div id=\"info-reject\" class=\"msg-info\"> <strong>Ditolak karena :</strong>
							<br>" . str_replace("\n", "<br>", $data->CPM_TRAN_INFO) . "</div>\n";
    }
    #print PDF
    if ($data->CPM_TRAN_STATUS == '5' || $data->CPM_TRAN_STATUS == '2' || $data->CPM_TRAN_STATUS == '3') {
      $draf = 0;
      $setuju = 0;
      #untuk data di tab tertunda maka tulisan "SEMENTARA" atau "SALINAN" di hilangkan
      # ket : $draf == 0 => salinan, $draf == 1 => sementara
      if ($this->right != 1 || $data->CPM_TRAN_STATUS == '2' || ($data->CPM_TRAN_STATUS == '3' && $data->CPM_TRAN_FLAG == '0')) {
        $draf = 3;
        if ($data->CPM_PAYMENT_TIPE == 2) {
          $draf = 1;
        }
      }
      if ($data->CPM_TRAN_STATUS == '5' && $data->CPM_TRAN_FLAG == '0')
        $setuju = 1;

      // $param = "{\'id\':\'" . $data->CPM_SSB_ID . "\',\'draf\':$draf,\'setuju\':\'$setuju\',\'uname\':\'" . $this->uname . "\',\'axx\':\'" . base64_encode($_REQUEST['a']) . "\'}"; //OLD

      $param = $data->CPM_SSB_ID;
      $param = "[{\'id\':\'" . $data->CPM_SSB_ID . "\',\'draf\':$draf,\'setuju\':\'$setuju\',\'uname\':\'" . $this->uname . "\',\'axx\':\'" . base64_encode($_REQUEST['a']) . "\'}]";
      if ($setuju == 1 || $draf == 3) {
        if ($data->CPM_PAYMENT_TIPE != 2) {
          $infoReject = '<div align=right>
									<label style="cursor:pointer" onclick="printToPDF_view(\'' . $param . '\');">Print to PDF 
										<img src="./image/icon/pdf.png" width="17px" height="22px" title="Dokumen PDF">
									</label>
								</div>';

          $arParam = "{
						'id':'{$data->CPM_SSB_ID}',
						'draf':'{$draf}',
						'setuju':'{$setuju}',
						'uname':'{$this->uname}',
						'axx':'" . base64_encode($_REQUEST['a']) . "'
					}";

          // $param = base64_encode($arParam);
          // $infoReject.= "<div align=\"right\"><label onclick=\"printToPrinter('$param');\">Send to Printer
          // <img src=\"image/icon/devices-printer-icon.png\" 
          // width=\"16px\" height=\"16px\" title=\"Print\" ></label></div>";
        } else {

          $infoReject = "<div align=\"right\" class=\"download\">Print SSPD Kurang Bayar
							<img src=\"./image/icon/document-pdf-text.png\" width=\"16px\" height=\"16px\" 
							title=\"Dokumen PDF\" onclick=\"printToPDFKurangBayar('$param');\" ></div>";
          $infoKetetapanKB = "<div align=\"right\">Print Ketetapan Kurang Bayar
							<img src=\"./image/icon/document--pencil.png\" width=\"16px\" height=\"16px\" 
							title=\"Dokumen PDF\" onclick=\"printToPDFKetetapanKB('$param');\" ></div>";
        }
      }
    }
    $html = "";
    $addApp = "";
    // var_dump($this->dispenda3);
    // die;
    if ($this->approved) {
      $html .= "<script language=\"javascript\" src=\"./function/BPHTB/dispenda/func-display-dispenda.js?N\" type=\"text/javascript\"></script>";
      // if (($this->dispenda1) && (($data->CPM_TRAN_STATUS != '3') && ($data->CPM_TRAN_STATUS != '5'))) {
      if ($data->CPM_TRAN_FERIF_LAPANGAN == 99) {

        $qry_berkas = "SELECT * FROM cppmod_ssb_berkas WHERE CPM_SSB_DOC_ID =\"$data->CPM_SSB_ID\"";
        $res_b = mysqli_query($DBLink, $qry_berkas);
        if ($res_b === false) {
          echo $qry_berkas . "<br>";
          echo mysqli_error($DBLink);
        }
        $json = new Services_JSON();
        $dtb = $json->decode($this->mysql2json($res_b, "data"));
        $usr_upt = $dtb->data[0]->CPM_USER_UPT_UPDATE;
        $info_upt_update = $dtb->data[0]->CPM_INFO_UPDATE_UPT;

        $nobkt = 0;
        $upload_upt1 = '';
        if ($dtb->data[0]->CPM_UPLOAD_UPT1 != '') {
          $nobkt++;
          $upload_upt1 = "<tr>
			        						<td>
		        								<a href=\"function/BPHTB/VerifikasiLpgn/{$dtb->data[0]->CPM_UPLOAD_UPT1}\" target=\"_blank\">Lihat Bukti {$nobkt}</a>
        									</td>
    									</tr>";
        }
        $upload_upt2 = '';
        if ($dtb->data[0]->CPM_UPLOAD_UPT2 != '') {
          $nobkt++;
          $upload_upt2 = "<tr>
			        						<td>
			        							<a href=\"function/BPHTB/VerifikasiLpgn/{$dtb->data[0]->CPM_UPLOAD_UPT2}\" target=\"_blank\">Lihat Bukti {$nobkt}</a>
		        							</td>
	        							</tr>";
        }
        $upload_upt3 = '';
        if ($dtb->data[0]->CPM_UPLOAD_UPT3 != '') {
          $nobkt++;
          $upload_upt3 = "<tr>
			        						<td>
			        							<a href=\"function/BPHTB/VerifikasiLpgn/{$dtb->data[0]->CPM_UPLOAD_UPT3}\" target=\"_blank\">Lihat Bukti {$nobkt}</a>
			        						</td>
		        						</tr>";
        }
        $addApp = "<table width=\"900\" cellspacing=\"1\" cellpadding=\"6\" border=\"0\">
				  					<tbody>
		            					<tr>
		            						<td width=\"3%\" valign=\"top\" align=\"center\"><div id=\"rcorners3\">
		            							<font size=\"+1\" color=\"white\"><b>E. </b></font></div>
	            							</td>
	            							<td>
						            			<table width=\"900\" cellspacing=\"1\" cellpadding=\"6\" border=\"0\">
								  					<tbody>
								  						<tr>
								  							<td><b>UPT Update by $usr_upt</b></td>
								  						</tr>
								  						{$upload_upt1}
								  						{$upload_upt2}
								  						{$upload_upt3}
						            					<tr>
						            						<td>
						            							Catatan pembaharuan data dari UPT:
						            						</td>
						            						<td class=\"document-x\" align=\"right\">
						            							<textarea disabled>$info_upt_update</textarea>
						            						</td>
						            					</tr>
						            				</tbody>
								            	</table
	            							</td>
		            					</tr>
	            					</tbody>
	        					<table>";
      }
      $addApp .= $this->addApprove();
      // }
      // if (($this->dispenda2) && ($data->CPM_TRAN_FLAG == '0' && $data->CPM_TRAN_STATUS == '3')) {
      //   $addApp = $this->addApprove();
      // }
    }
    if (($data->CPM_OP_JENIS_HAK == '33') || ($data->CPM_OP_JENIS_HAK == '7')) {

      $aphbt = $data->CPM_APHB;
      $p = explode("/", $aphbt);
      $aphb = $p[0] / $p[1];
    } else {
      $aphb = 1;
    }
    $pdenda = $data->CPM_PERSEN_DENDA;
    $denda = $data->CPM_PERSEN_DENDA;
    $jmlByr = (($npop - strval($NPOPTKP)) * 0.05) + $denda;
    if (($typeR == 4) || ($typeR == 5) || ($typeR == 31)) {
      $jmlByr = ($jmlByr * $data->CPM_PENGENAAN * 0.01) + $denda;
    } else if (($typeR == 33) || ($typeR == 7)) {
      $jmlByr = (($npop - strval($NPOPTKP)) * 0.05 * $aphb) + $denda;
    }
    if (isset($tp) && $tp != 0)
      $jmlByr = $denda + $jmlByr - ($jmlByr * ($tp * 0.01));

    if ($jmlByr < 0)
      $jmlByr = 0;
    $tp = strval($data->CPM_PAYMENT_TIPE_PENGURANGAN);


    $tNPOPKP = $npop - strval($NPOPTKP);
    if ($tNPOPKP < 0)
      $tNPOPKP = 0;
    $bphtbx = number_format($tNPOPKP * 0.05, 0, '.', ',');
    $pengenaan = strval($data->CPM_PENGENAAN);

    $bphtbpengenaan = number_format($tNPOPKP * 0.05 * $pengenaan * 0.01, 0, '.', ',');
    $bphtbaphb = 0;

    if (($typeR == 33) || ($typeR == 7)) {
      $bphtbaphb = number_format($aphb * (($tNPOPKP * 0.05) - ($tNPOPKP * 0.05 * $pengenaan * 0.01)), 0, '.', ',');
    }

    $bphtbakhir = number_format($aphb * (($tNPOPKP * 0.05) - ($tNPOPKP * 0.05 * $pengenaan * 0.01)), 0, '.', ',');
    if ($data->CPM_PAYMENT_TIPE == '2') {
      $bphtbx = number_format($data->CPM_KURANG_BAYAR, 0, '.', ',');
      $jmlByr = $data->CPM_KURANG_BAYAR;
    }

    $data->CPM_OP_LUAS_TANAH = number_format($data->CPM_OP_LUAS_TANAH, 0, '', '');
    $data->CPM_OP_NJOP_TANAH = number_format($data->CPM_OP_NJOP_TANAH, 0, '', '');
    $data->CPM_OP_LUAS_BANGUN = number_format($data->CPM_OP_LUAS_BANGUN, 0, '', '');
    $data->CPM_OP_NJOP_BANGUN = number_format($data->CPM_OP_NJOP_BANGUN, 0, '', '');

    $nilaipasartanah = $this->getnilaipasar($data->CPM_OP_NOMOR, $data->CPM_OP_ZNT);


    if ($this->getConfigValue("aBPHTB", 'DENDA') == '1') {
      $hitung_denda = " <tr>
					<td>Denda " . $pdenda . "%</td>
					<td class=\"document-x\" align=\"right\">" . number_format($denda, 0, '.', ',') . "</td>
				  </tr>";
    } else {
      $hitung_denda = "";
    }

    if ($this->getConfigValue("aBPHTB", 'CONFIG_PASAR') == '1') {
      $desc_pasar = " <br><br>
			  <table width=\"650\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td colspan=\"5\"><strong>Penghitungan NJOP Menurut Harga Pasar:</strong></td>
					</tr>
				  <tr>
					
					<td colspan=\"5\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP /m²</td>
					</tr>
				  <tr>
					<td rowspan=\"2\" width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" width=\"176\">Luas Tanah (Bumi)</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\" width=\"197\">NJOP Tanah (Bumi) /m²</td>
					<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Tanah X NJOP Tanah</td>
					</tr>
				  <tr>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($data->CPM_OP_LUAS_TANAH, 0, ',', '.') . "
					  m²</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">" . number_format($nilaipasartanah, 0, ',', '.') . "</td>
					<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">" . number_format($data->CPM_OP_LUAS_TANAH * $nilaipasartanah, 0, ',', '.') . "</td>
				  </tr>
				  <tr>
					<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">NJOP Bangunan / m²</td>
					<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Luas Bangunan X NJOP Bangunan / m²</td>
					</tr>
				  <tr>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">" . number_format($data->CPM_OP_LUAS_BANGUN, 0, ',', '.') . "
				m²</td>
					<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\">" . number_format($data->CPM_OP_NJOP_BANGUN, 0, ',', '.') . "</td>
					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">" . number_format($data->CPM_OP_LUAS_BANGUN * $data->CPM_OP_NJOP_BANGUN, 0, ',', '.') . "</td>
				  </tr>
				  <tr>
					<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP Harga Pasar </td>
					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" colspan=\"2\">" . number_format(($data->CPM_OP_LUAS_TANAH * $nilaipasartanah) + ($data->CPM_OP_LUAS_BANGUN * $data->CPM_OP_NJOP_BANGUN), 0, ',', '.') . "</td>
				  </tr>
					  </table>";
    } else {
      $desc_pasar = "";
    }
    $jenishakprint = $this->getjenishak($data->CPM_OP_JENIS_HAK);
    $jenishakmilikprint = $this->getjenishakmilik($data->CPM_JENIS_HAK_MILIK);
    $html .= "<link rel=\"stylesheet\" href=\"./function/BPHTB/dispenda/func-display-dispenda.css\" type=\"text/css\">\n";
    $html .= "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\">";
    $html .= "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
    $html .= "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";

    $html .= '<script language="javascript">
					var repeat = 0;

					function sleep(ms) {
						return new Promise(resolve => setTimeout(resolve, ms));
					}

					function getQRCode(id,sha1) {
						if(repeat==0){
							if(confirm("Proses ini akan men-generate QRIS code \n\nApakah mau lanjut ?\n")){
								document.getElementById("QRico").src = "./image/large-loading.gif";
								repeat = 1;
								hitit(id,sha1);
							}else{
								document.getElementById("QRico").src = "./image/icon/qr_disable.png";
								repeat = 0;
							}
						}
					}

					function hitit(id, sha1) {
						if (repeat != 0) {
							Ext.Ajax.request({
								url: "function/BPHTB/func-getQRIS-GET.php",
								method: "GET",
								params: {
									ssbid: id,
									sha1: sha1
								},
								success: function(result, request) {
									var respon = JSON.parse(result.responseText);
									if (respon.status) {
										repeat = 0;
										alert("\nB E R H A S I L");
										document.getElementById("idico" + id).src = "./image/icon/qr.png";
										let elem = document.getElementById("divico" + id).firstElementChild;
										elem.removeAttribute("onclick");
										elem.removeAttribute("href");
									} else if (respon.msg == "repeat") {
										repeat++;
										if (repeat >= 4) {
											repeat = 0;
											document.getElementById("idico" + id).src = "./image/icon/qr_disable.png";
											alert("Gagal koneksi ke Server REST API");
										}
										sleep(2000).then(() => {
											hitit(id, sha1);
										});
									} else {
										repeat = 0;
										alert(respon.msg);
										document.getElementById("idico" + id).src = "./image/icon/qr_disable.png";
										let elem = document.getElementById("divico" + id).firstElementChild;
										elem.removeAttribute("onclick");
										elem.removeAttribute("href");
									}
								},
								failure: function(result, request) {
									repeat = 0;
									alert("Gagal mengirim data via Ajax Javascript\n");
									document.getElementById("idico" + id).src = "./image/icon/qr_disable.png";
								}
							});
						}
					}
				</script>';

    //// DEdi = Kode bayar
    // check available qris di tabel
    $dataQRIS = ($payment_code) ? $this->getDBQRIS(trim($data->CPM_SSB_ID)) : false;

    if ($dataQRIS) {
      // print_r($dataQRIS->qr);exit;
      // QRcode::png($dataQRIS->qr, 'image/gen_qr_temp.png', QR_ECLEVEL_H, 10, 1);
      // $imgQRIS = '<img width="120px" height="120px" src="./image/gen_qr_temp.png">';
      $d = new DNS2D();
      $d->setStorPath(__DIR__ . '/cache/');
      $img = $d->getBarcodeSVG($dataQRIS->qr, 'QRCODE', 3, 3);
      // echo '<div style="width:20px;margin-left:20px">'.$img.'</div>';
      $imgQRIS = $img;
    } elseif ($payment_code) {
      // get New QRIS from API Bank
      $ssbid = trim($data->CPM_SSB_ID . '');
      $sha1 = sha1('#BPHTB#LAMPUNG#SELATAN#' . $ssbid . '#' . date('Ymd') . '#');
      $parPOST = "'$ssbid','$sha1'";
      $imgQRIS = '<label style="cursor:pointer" onclick="getQRCode(' . $parPOST . ');" title="Klik untuk men-generate">QRIS <img id="QRico" src="./image/icon/qr_disable.png" width="20px" height="20px"></label>';
    } else {
      $imgQRIS = '';
    }
    //==================================================================
    // var_dump($data->CPM_PAYMENT_TIPE);die;
    $html .= "<div id=\"main-content\" style=\"margin-bottom:30px;padding:30px 0px\">";

    $html .= '<div style="position:absolute;top:330px;right:100px;width:170px;height:170px;text-align:right">' . $imgQRIS . '</div>';

    $html .= "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
		  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"6\">
			<tr>
			  <td colspan=\"2\" align='center' style=\"border-radius: 10px 10px 0px 0px;\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah<br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong>$infoReject<br></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>A. </b></font></div></td>
			  <td width=\"97%\"><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"10\"><div align=\"right\">1.</div></td>	
				  <td>Nomor KTP</td>
				  <td class=\"document-x\">" . $data->CPM_WP_NOKTP . "</td>		   
				</tr>
				<tr>
				  <td><div align=\"right\">2.</div></td>
				  <td>NPWP</td>
				  <td class=\"document-x\">" . $data->CPM_WP_NPWP . "</td>				 
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td width=\"200\">Nama Wajib Pajak</td>
				  <td width=\"\" class=\"document-x\">" . $data->CPM_WP_NAMA . "</td>	
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>Alamat Wajib Pajak</td>
				  <td class=\"document-x\">" . $data->CPM_WP_ALAMAT . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td class=\"document-x\">" . $data->CPM_WP_KELURAHAN . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>RT/RW</td>
				  <td class=\"document-x\">" . $data->CPM_WP_RT . "/" . $data->CPM_WP_RW . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kecamatan</td>
				  <td class=\"document-x\">" . $data->CPM_WP_KECAMATAN . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td class=\"document-x\">" . $data->CPM_WP_KABUPATEN . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">9.</div></td>
				  <td>Kode Pos</td>
				  <td class=\"document-x\">" . $data->CPM_WP_KODEPOS . "</td>
				</tr>
				
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>B. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"10\"><div align=\"right\">1.</div></td>
				  <td width=\"200\">NOP PBB</td>
				  <td width=\"220\" class=\"document-x\">" . $data->CPM_OP_NOMOR . "</td>
				  <td>Nama WP Lama :</td>
				  <td class=\"document-x\">" . $data->CPM_WP_NAMA_LAMA . "</td>
				  </tr>
				<tr>
				  <td><div align=\"right\">2.</div></td>
				  <td>Lokasi Objek Pajak</td>
				  <td class=\"document-x\">" . $data->CPM_OP_LETAK . "</td>
                  <td width=170>Nama WP Sesuai Sertifikat :</td>
				  <td class=\"document-x\">" . $data->CPM_WP_NAMA_CERT . "</td>    
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td class=\"document-x\" colspan=\"3\">" . $data->CPM_OP_KELURAHAN . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>RT/RW</td>
				  <td class=\"document-x\" colspan=\"3\">" . $data->CPM_OP_RT . "
					/
					" . $data->CPM_OP_RW . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kecamatan</td>
				  <td class=\"document-x\" colspan=\"3\">" . $data->CPM_OP_KECAMATAN . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td class=\"document-x\" colspan=\"3\">" . $data->CPM_OP_KABUPATEN . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kode Pos</td>
				  <td class=\"document-x\" colspan=\"3\">" . $data->CPM_OP_KODEPOS . "</td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Titik Koordinat</td>
				  <td class=\"document-x\" colspan=\"3\">" .
      ($data->KOORDINAT ? "<a href=\"https://www.google.com/maps/@{$data->KOORDINAT},15z\" target=\"_blank\" style=\"text-decoration: underline\">" . $data->KOORDINAT . "</a>" : $data->KOORDINAT)
      . "</td>
				</tr>
			  </table><table width=\"900\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\" style=\"border: 1px solid black;border-collapse: collapse;\" class=\"pure-table\"><thead>
		  <tr>
			<td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></th>
			</tr>
		  <tr>
			<th width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</th>
			<th width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</th>
			<th width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak 
			  <span class=\"document-x\">" . $data->CPM_OP_THN_PEROLEH . "</span></th>
			<th colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</th>
			</tr>
			</thead>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"document-x\">" . number_format(strval($data->CPM_OP_LUAS_TANAH), 0, '.', ',') . " m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\" class=\"document-x\">" . number_format(strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
			<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
			<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\" class=\"document-x\">" . number_format(strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"document-x\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN), 0, '.', ',') . " m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\" class=\"document-x\">" . number_format(strval($data->CPM_OP_NJOP_BANGUN), 0, '.', ',') . "</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\" class=\"document-x\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\" class=\"document-x\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">Harga Transaksi/ Nilai Pasar</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">14.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\" class=\"document-x\">" . number_format(strval($data->CPM_OP_HARGA), 0, '.', ',') . "</td>
		  </tr>
			  </table>
			  " . $desc_pasar . "
			  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"10\"><div align=\"right\">15.</div></td>
				  <td width=\"315\">Jenis Hak Milik	</td>
				  <td width=\"\">" . $jenishakmilikprint . "</td>
				</tr>	

				<tr>
				  <td width=\"10\"><div align=\"right\">16.</div></td>
				  <td width=\"315\">Jenis perolehan hak atas tanah atau bangunan</td>
				  <td width=\"\">" . $jenishakprint . "</td>
				</tr>				
				<tr>
				  <td><div align=\"right\">17.</div></td>
				  <td>Nomor sertifikat tanah</td>
				  <td class=\"document-x\">" . $data->CPM_OP_NMR_SERTIFIKAT . "</td>
				</tr>				
			  </table></td>
			</tr>			
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
					<td width=\"188\" align=\"right\"><em><strong>Dalam rupiah</strong></em></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak (NPOP) </td>
					<td id=\"tNJOP\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(0), 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
					<td class=\"document-x\" align=\"right\">" . number_format($NPOPTKP, 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
					<td id=\"tNPOPKP\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(1), 0, '.', ',') . "</td>
				  </tr>";
    if ($data->CPM_PAYMENT_TIPE != 2) {
      if ($data->CPM_OP_JENIS_HAK == 7) {
        $CPM_BPHTB_BAYAR = ($this->getBPHTBPayment_all(1) * 0.05) / 2;
      } else {
        $CPM_BPHTB_BAYAR = $data->CPM_BPHTB_BAYAR;
      }
      // var_dump($CPM_BPHTB_BAYAR);die;  
      $html .= "<tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar Sementara</td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(2), 0, '.', ',') . "</td>
				  </tr>
				  <tr " . $display_pengenaan . ">
					<td>Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;" . $pengenaan . "%</td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(3), 0, '.', ',') . "</td>
				  </tr>
				  <tr $display_aphb>
					<td>APHB</td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(4), 0, '.', ',') . "</td>
				  </tr>
				  " . $hitung_denda . "
				 
				  <tr>
					<td><strong>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar</strong></td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($CPM_BPHTB_BAYAR, 0, '.', ',') . "</td>
					<!-- <td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(5), 0, '.', ',') . "</td> -->
				  </tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>D. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong data=\"" . ($this->dispenda2) . "\"> Jumlah Setoran Berdasarkan</strong> : $typepayment</td>
				</tr>
				<tr>
				<td colspan='2' align='right' style='padding-right:50px'>
					<a href=\"#\" onclick=\"cekberkas('" . $data->CPM_SSB_ID . "','" . $data->CPM_OP_JENIS_HAK . "')\">Berkas</a>
				</td>
			</tr>
				$fieldTambahan
				</table>
			  </td>
			</tr>
			" . $addApp . "
			<tr>
				<!-- <td colspan=\"2\" align=\"center\" valign=\"middle\" style=\"border-radius: 10px 10px 10px 10px;\" id=\"jmlBayar\">Jumlah yang dibayarkan: " . number_format($this->getBPHTBPayment_all(5), 0, '.', ',') . "</td> -->
				<td colspan=\"2\" align=\"center\" valign=\"middle\" style=\"border-radius: 10px 10px 10px 10px;\" id=\"jmlBayar\">Jumlah yang dibayarkan: " . number_format($data->CPM_BPHTB_BAYAR, 0, '.', ',') . "</td>
				</tr>
			" . $kurang . "
		  </table>
			</form>";

      // if($_SESSION['role'] != 'rmBPHTBNotaris' && ($data->CPM_TRAN_STATUS == '2')){
      // 	$html .="<tr ><td>&nbsp;</td><td>
      // 	<table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\" style=\"background-color:#f1c3e2; margin-left:40px\">
      // 		  <tr>
      // 			<td colspan=\"2\" class=\"result-analisis\" style=\"border-radius: 10px 10px 0px 0px;\"><strong>Hasil analisis</strong></td>
      // 		  </tr>

      // 		  <tr>
      // 			<td width=\"144\" class=\"result-analisis\"><label>
      // 				  <input type=\"radio\" name=\"RadioGroup1\" value=\"1\" id=\"RadioGroup1_0\" checked onclick=\"enableE(this,0);\"/>
      // 				  Disetujui</label></td>
      // 			<td class=\"result-analisis\">Alasan</td>
      // 		  </tr>
      // 		  <tr>
      // 			<td class=\"result-analisis\">&nbsp;</td>
      // 			<td class=\"result-analisis\"><label for=\"textarea\"></label>
      // 			  <textarea name=\"textarea-info1\" id=\"textarea-info1\" cols=\"50\" rows=\"8\"></textarea></td>
      // 		  </tr>						
      // 		  <tr>
      // 			<td width=\"144\" class=\"result-analisis\">
      // 				<label>
      // 				  <input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" onclick=\"enableE(this,1);\"/>
      // 				  Ditolak
      // 			  	</label>
      // 			</td>
      // 			<td class=\"result-analisis\">
      // 				Alasan
      // 			</td>
      // 		  </tr>
      // 		  <tr>
      // 			<td class=\"result-analisis\">&nbsp;</td>
      // 			<td class=\"result-analisis\"><label for=\"textarea\"></label>
      // 			  <textarea name=\"textarea-info\" id=\"textarea-info\" cols=\"50\" rows=\"8\" disabled></textarea></td>
      // 		  </tr>
      // 		  <tr>
      // 			<td class=\"result-analisis\">&nbsp;</td>
      // 			<td class=\"result-analisis\">
      // 				<label>
      // 				  <input type=\"radio\" name=\"RadioGroup99\" value=\"991\" id=\"RadioGroup99_1\" checked disabled/>
      // 				  Kelengkapan Document
      // 			  	</label>
      // 				<label>
      // 				  <input type=\"radio\" name=\"RadioGroup99\" value=\"992\" id=\"RadioGroup99_2\" disabled/>
      // 				  Verifikasi Lapangan
      // 			  	</label>
      // 		  	</td>
      // 		  </tr>
      // 		  <tr>
      // 			<td class=\"result-analisis\">&nbsp;</td>
      // 			<td align=\"right\" class=\"result-analisis\"><label for=\"textarea\" style=\"border-radius: 0px 0px 10px 10px;\"></label>
      // 			  <input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Submit\"></textarea></td>
      // 		  </tr>
      // 		</table>
      // 		</td>
      // 		</tr>";
      // }

      $html .= "</div>
		
			<div id=\"id01\" class=\"w3-modal\">
				<div class=\"w3-modal-content\">
				<div id=\"w3-container\"   width='80%' style='padding:40px'>
					
					
				</div>
				</div>
			</div>
			
			<div id=\"id02\" class=\"w3-modal\">
				<div class=\"w3-modal-content\">
				<div id=\"w3-container2\"   width='80%' style='padding:40px'>
					
					
				</div>
				</div>
			</div>
			
			";
    } else {
      $html .= "
				<tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar Sementara</td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(2), 0, '.', ',') . "</td>
				  </tr>
				<tr>
					<td>Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;" . $pengenaan . "%</td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(3), 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>APHB</td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(4), 0, '.', ',') . "</td>
				  </tr>
				  " . $hitung_denda . "
				<tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar Seharusnya</td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($this->getBPHTBPayment_all(5), 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang sudah dibayar</td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($data->CPM_KURANG_BAYAR_SEBELUM, 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td><strong>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar</strong></td>
					<td id=\"tTotal\" class=\"document-x\" align=\"right\">" . number_format($data->CPM_KURANG_BAYAR, 0, '.', ',') . "</td>
				  </tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>D. </b></font></div></td>
			  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan</strong> : $typepayment</td>
				</tr>
				$fieldTambahan
				</table>
			  </td>
			</tr>
			" . $addApp . "
			<tr >
				<td colspan=\"3\" align=\"center\"  valign=\"middle\"  style=\"border-radius: 10px 10px 10px 10px;\" id=\"jmlBayar\">Jumlah yang dibayarkan : " . number_format($data->CPM_KURANG_BAYAR, 0, '.', ',') . "</td>
				</tr>
			" . $kurang . "
		  </table>
				</form></div>
		
			<div id=\"id01\" class=\"w3-modal\">
				<div class=\"w3-modal-content\">
				<div id=\"w3-container\"   width='80%' style='padding:40px'>
					
					
				</div>
				</div>
			</div>
			
			<div id=\"id02\" class=\"w3-modal\">
				<div class=\"w3-modal-content\">
				<div id=\"w3-container2\"   width='80%' style='padding:40px'>
					
					
				</div>
				</div>
			</div>";
    }
    if ($this->submit)
      $html = "";
    if ($this->newversion)
      $html = $this->formSSB($data, true);
    return $html;
  }
  
}
