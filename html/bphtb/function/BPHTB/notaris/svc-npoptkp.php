<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
  $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}


$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

function getConfigValue($id, $key)
{
  global $DBLink;
  //$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
  $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }
  while ($row = mysqli_fetch_assoc($res)) {
    return $row['CTR_AC_VALUE'];
  }
}

function getConfigure($appID)
{
  $config = array();
  $a = $appID;
  $config['TENGGAT_WAKTU'] = getConfigValue($a, 'TENGGAT_WAKTU');
  $config['NPOPTKP_STANDAR'] = getConfigValue($a, 'NPOPTKP_STANDAR');
  $config['NPOPTKP_WARIS'] = getConfigValue($a, 'NPOPTKP_WARIS');
  $config['TARIF_BPHTB'] = getConfigValue($a, 'TARIF_BPHTB');
  $config['PRINT_SSPD_BPHTB'] = getConfigValue($a, 'PRINT_SSPD_BPHTB');
  $config['NAMA_DINAS'] = getConfigValue($a, 'NAMA_DINAS');
  $config['ALAMAT'] = getConfigValue($a, 'ALAMAT');
  $config['NAMA_DAERAH'] = getConfigValue($a, 'NAMA_DAERAH');
  $config['KODE_POS'] = getConfigValue($a, 'KODE_POS');
  $config['NO_TELEPON'] = getConfigValue($a, 'NO_TELEPON');
  $config['NO_FAX'] = getConfigValue($a, 'NO_FAX');
  $config['EMAIL'] = getConfigValue($a, 'EMAIL');
  $config['WEBSITE'] = getConfigValue($a, 'WEBSITE');
  $config['KODE_DAERAH'] = getConfigValue($a, 'KODE_DAERAH');
  $config['KEPALA_DINAS'] = getConfigValue($a, 'KEPALA_DINAS');
  $config['NAMA_JABATAN'] = getConfigValue($a, 'NAMA_JABATAN');
  $config['NIP'] = getConfigValue($a, 'NIP');
  $config['NAMA_PJB_PENGESAH'] = getConfigValue($a, 'NAMA_PJB_PENGESAH');
  $config['JABATAN_PJB_PENGESAH'] = getConfigValue($a, 'JABATAN_PJB_PENGESAH');
  $config['NIP_PJB_PENGESAH'] = getConfigValue($a, 'NIP_PJB_PENGESAH');

  $config['BPHTBDBNAME'] = getConfigValue($a, 'BPHTBDBNAME');
  $config['BPHTBHOSTPORT'] = getConfigValue($a, 'BPHTBHOSTPORT');
  $config['BPHTBPASSWORD'] = getConfigValue($a, 'BPHTBPASSWORD');
  $config['BPHTBTABLE'] = getConfigValue($a, 'BPHTBTABLE');
  $config['BPHTBUSERNAME'] = getConfigValue($a, 'BPHTBUSERNAME');

  return $config;
}

function check_noktp_wp($noktp = '', $tahun = '')
{

  global $DBLink;

  $qry = "select * 
          from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
            where A.CPM_WP_NOKTP = '{$noktp}' $tahun";
  // echo $qry;
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }
  return mysqli_num_rows($res);
}
function check_noktp_wpv2($noktp = '', $tahun = '')
{

  global $DBLink, $nop, $trsid;
  $qry = "select * 
          from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
            where A.CPM_WP_NOKTP = '{$noktp}' $tahun AND CPM_OP_NOMOR = '{$nop}' AND (CPM_TRAN_ID ='{$trsid}' OR A.CPM_SSB_ID ='{$trsid}') LIMIT 1";
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }
  while ($rows = mysqli_fetch_assoc($res)) {
    if ($rows['CPM_OP_NPOPTKP'] != 0) {
      $return['npoptkp'] = $rows['CPM_OP_NPOPTKP'];
    }
  }
  $row = mysqli_num_rows($res);
  $return['rows'] = $row;
  return $return;
}
$id = @isset($_REQUEST['id']) ? intval($_REQUEST['id']) : "";
$lama = @isset($_REQUEST['lama']) ? intval($_REQUEST['lama']) : "0";
$appId = base64_decode(@isset($_REQUEST['axx']) ? $_REQUEST['axx'] : "");
$harga_trans = @isset($_REQUEST['harga_trans']) ? $_REQUEST['harga_trans'] : "";
$noktp = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "";
$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$trsid = @isset($_REQUEST['trsid']) ? $_REQUEST['trsid'] : "";
$cnfg_npoptkp = getConfigValue($appId, 'CONFIG_PEMB_NPOPTKP');
// $cnfg_npoptkp = 5;


$verdoc = @isset($_REQUEST['verdoc']) ? $_REQUEST['verdoc'] : "";
$tahun = date("Y");
$result = array();
if ($id) {
  $result['success'] = true;

  $CONFIG_PEMB_NPOPTKP = getConfigValue($appId, 'CONFIG_PEMB_NPOPTKP');
  // var_dump($CONFIG_PEMB_NPOPTKP);

  if ($CONFIG_PEMB_NPOPTKP == '1') {
    if ($id == 5 || $id == 4) {
      $result["result"] =  getConfigValue($appId, 'NPOPTKP_WARIS');
    } else {
      if ($lama === 9) {
        $result["result"] =  getConfigValue($appId, 'NPOPTKP_LAMA');
      } else {
        $result["result"] =  getConfigValue($appId, 'NPOPTKP_STANDAR');
      }
    }
  } else if ($CONFIG_PEMB_NPOPTKP == '2') {
    $cek_tahun = "";
    $cek_jenis_perolehan = "";
    if (check_noktp_wp($noktp, $jh, $cek_tahun, $cek_jenis_perolehan, $appId)) {
      if ($id == 5 || $id == 4) {
        $result["result"] =  getConfigValue($appId, 'NPOPTKP_WARIS');
      } else {
        if ($lama === 9) {
          $result["result"] =  getConfigValue($appId, 'NPOPTKP_LAMA');
        } else {
          $result["result"] =  getConfigValue($appId, 'NPOPTKP_STANDAR');
        }
      }
    } else {
      $result["result"] =  0;
    }
  } else if ($CONFIG_PEMB_NPOPTKP == '3') {
    $cek_tahun = " and SUBSTRING(CPM_SSB_CREATED, 1, 4)= '{$tahun}' ";
    $cek_jenis_perolehan = "";
    if (check_noktp_wp($noktp, $cek_tahun) == 0) {
      if ($id == 5 || $id == 4) {
        $result["result"] =  getConfigValue($appId, 'NPOPTKP_WARIS');
      } else {
        if ($lama === 9) {
          $result["result"] =  getConfigValue($appId, 'NPOPTKP_LAMA');
        } else {
          $result["result"] =  getConfigValue($appId, 'NPOPTKP_STANDAR');
        }
      }
    } else {
      $checked = check_noktp_wpv2($noktp, $cek_tahun);
      if ($checked['rows'] != 0) {
        if (!isset($checked['npoptkp']) || $checked['npoptkp'] == 0) {
          $result["result"] =  0;
        } else {
          if ($id == 5 || $id == 4) {
            $result["result"] =  getConfigValue($appId, 'NPOPTKP_WARIS');
          } else {
            if ($lama === 9) {
              $result["result"] =  getConfigValue($appId, 'NPOPTKP_LAMA');
            } else {
              $result["result"] =  getConfigValue($appId, 'NPOPTKP_STANDAR');
            }
          }
        }
      } else {
        $result["result"] =  0;
      }
    }
  } else if ($CONFIG_PEMB_NPOPTKP == '4') {
    $cek_tahun = " and CPM_OP_THN_PEROLEH= '{$tahun}' ";
    $cek_jenis_perolehan = " AND A.CPM_OP_JENIS_HAK = {$jh} ";
    if (check_noktp_wp($noktp, $jh, $cek_tahun, $cek_jenis_perolehan, $appId) == 0) {
      if ($id == 5 || $id == 4) {
        $result["result"] =  getConfigValue($appId, 'NPOPTKP_WARIS');
      } else {
        if ($lama === 9) {
          $result["result"] =  getConfigValue($appId, 'NPOPTKP_LAMA');
        } else {
          $result["result"] =  getConfigValue($appId, 'NPOPTKP_STANDAR');
        }
      }
    } else {
      $result["result"] =  0;
    }
  }

  $sResponse = $json->encode($result);
  echo $sResponse;
}

SCANPayment_CloseDB($DBLink);
