<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'uploadberkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js\"></script>";

echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"function/BPHTB/berkas/jquery.validate.min.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<link rel=\"stylesheet\" href=\"function/BPHTB/berkas/func-mod-pelayanan.css\" type=\"text/css\">\n";
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getConfigValue($id, $key)
{
  global $DBLink;
  $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo "<link rel=\"stylesheet\" href=\"function/BPHTB/berkas/func-mod-pelayanan.css\" type=\"text/css\">\n";
    echo mysqli_error($DBLink);
  }
  while ($row = mysqli_fetch_assoc($res)) {
    return $row['CTR_AC_VALUE'];
  }
}
function jenishak($js)
{
  global $DBLink;

  $texthtml = "<select name=\"jnsPerolehan\" style=\"width:250px;height: 30px;\" id=\"jnsper\" onchange='cleancheckbox();javascript:showJnsPerolehan(this);'>";
  $qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
  //echo $qry;exit;
  $res = mysqli_query($DBLink, $qry);
  $texthtml .= "<option value=\"0\" />Pilih Jenis Hak</option>";
  while ($data = mysqli_fetch_assoc($res)) {
    if (($js != $data['CPM_KD_JENIS_HAK']) || ($js == "")) {
      $selected = "";
    } else {
      $selected = "selected";
    }
    $texthtml .= "<option value=\"" . $data['CPM_KD_JENIS_HAK'] . "\" " . $selected . " >" . str_pad($data['CPM_KD_JENIS_HAK'], 2, "0", STR_PAD_LEFT) . " " . $data['CPM_JENIS_HAK'] . "</option>";
  }
  $texthtml .= "           </select>";
  return $texthtml;
}

function getberkas($no, $ssb_id, $berkas)
{
  global $DBLink;
  $thn_berkas = explode(".", $berkas);

  $qry = "SELECT * FROM cppmod_ssb_upload_file WHERE CPM_SSB_ID = '$ssb_id' AND CPM_BERKAS_ID='$berkas' AND CPM_KODE_LAMPIRAN = '$no'";
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }
  $row = mysqli_num_rows($res);
  if ($row >= 1) {
    while ($row = mysqli_fetch_assoc($res)) {
      $berkas = "<a href ='function/BPHTB/uploadberkas/berkas/{$thn_berkas[0]}/{$berkas}/{$row['CPM_FILE_NAME']}' id='a_{$no}' target='_blank'>Download/view</a>";
    }
  } else {
    $berkas = "";
  }
  return $berkas;
}
function formPenerimaan($value)
{
  global $a, $m, $appConfig, $arConfig, $DBLink;
  $today = date("d-m-Y");
  $value = explode(",", "CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_LAMPIRAN,CPM_BERKAS_PETUGAS,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP,CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,
                CPM_BERKAS_NAMA_WP,CPM_BERKAS_ALAMAT_WP,CPM_BERKAS_STATUS,CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL");

  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";
  $lampiran[] = "";

  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";
  $jnsPerolehan[] = "";

  $strJnsPerolehan = "";

  $jnsPerolehan = jenishak("");
  $value['CPM_BERKAS_NOPEL'] = "";
  if (isset($_REQUEST['svcid'])) {
    $query = "select * from cppmod_ssb_berkas where CPM_BERKAS_ID = '{$_REQUEST['svcid']}'";
    // var_dump($query);die;
    $result = mysqli_query($DBLink, $query);
    $value = mysqli_fetch_array($result);
    $jnsPerolehan = jenishak($value['CPM_BERKAS_JNS_PEROLEHAN']);


    $strJnsPerolehan = $value['CPM_BERKAS_JNS_PEROLEHAN'];

    $lampiran[0] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "checked" : "";
    $lampiran[1] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "checked" : "";
    $lampiran[2] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "checked" : "";
    $lampiran[3] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "checked" : "";
    $lampiran[4] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "checked" : "";
    $lampiran[5] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "checked" : "";
    $lampiran[6] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "checked" : "";
    $lampiran[7] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "8") !== false) ? "checked" : "";
    $lampiran[8] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "9") !== false) ? "checked" : "";
    $lampiran[9] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "10") !== false) ? "checked" : "";
    $lampiran[10] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "checked" : "";
    $lampiran[11] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "checked" : "";
    $lampiran[12] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "13") !== false) ? "checked" : "";
    $lampiran[13] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "14") !== false) ? "checked" : "";
    $lampiran[14] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "15") !== false) ? "checked" : "";
    $lampiran[15] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "16") !== false) ? "checked" : "";
    $lampiran[16] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "17") !== false) ? "checked" : "";
    $lampiran[17] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "18") !== false) ? "checked" : "";
    $lampiran[18] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "19") !== false) ? "checked" : "";
    $lampiran[19] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "20") !== false) ? "checked" : "";
    $lampiran[20] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "21") !== false) ? "checked" : "";
    $lampiran[21] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "22") !== false) ? "checked" : "";
    $lampiran[22] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "23") !== false) ? "checked" : "";
    $lampiran[23] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "24") !== false) ? "checked" : "";
    $lampiran[24] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "25") !== false) ? "checked" : "";
    $lampiran[25] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "26") !== false) ? "checked" : "";
    $lampiran[26] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "27") !== false) ? "checked" : "";
    $lampiran[27] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "28") !== false) ? "checked" : "";
    $lampiran[28] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "29") !== false) ? "checked" : "";
    //tambahan upload
    $lampiran[30] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "30") !== false) ? "checked" : "";
    $lampiran[31] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "31") !== false) ? "checked" : "";
    $lampiran[32] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "32") !== false) ? "checked" : "";
    $lampiran[33] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "33") !== false) ? "checked" : "";
    $lampiran[34] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "34") !== false) ? "checked" : "";
    $lampiran[35] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "35") !== false) ? "checked" : "";
    $lampiran[36] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "36") !== false) ? "checked" : "";
    $lampiran[37] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "37") !== false) ? "checked" : "";

    $lampiran[38] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "38") !== false) ? "checked" : "";
    $lampiran[39] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "39") !== false) ? "checked" : "";
    $lampiran[40] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "40") !== false) ? "checked" : "";
    $lampiran[41] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "41") !== false) ? "checked" : "";
    $lampiran[42] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "42") !== false) ? "checked" : "";
    $lampiran[43] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "43") !== false) ? "checked" : "";
    $lampiran[44] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "44") !== false) ? "checked" : "";

    $lampiran[45] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "45") !== false) ? "checked" : "";
    $lampiran[46] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "46") !== false) ? "checked" : "";
    $lampiran[47] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "47") !== false) ? "checked" : "";
  }

  $berkas_lamp1 = getberkas(1, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp2 = getberkas(2, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp3 = getberkas(3, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp4 = getberkas(4, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp5 = getberkas(5, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp6 = getberkas(6, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp7 = getberkas(7, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp8 = getberkas(8, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp9 = getberkas(9, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp10 = getberkas(10, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp11 = getberkas(11, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp12 = getberkas(12, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp13 = getberkas(13, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp14 = getberkas(14, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp15 = getberkas(15, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp16 = getberkas(16, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp17 = getberkas(17, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp18 = getberkas(18, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp19 = getberkas(19, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp20 = getberkas(20, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp21 = getberkas(21, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp22 = getberkas(22, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp23 = getberkas(23, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp24 = getberkas(24, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp25 = getberkas(25, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp26 = getberkas(26, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp27 = getberkas(27, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp28 = getberkas(28, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  //tambahan upload
  $berkas_lamp30 = getberkas(30, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp31 = getberkas(31, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp32 = getberkas(32, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp33 = getberkas(33, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp34 = getberkas(34, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp35 = getberkas(35, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp36 = getberkas(36, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp37 = getberkas(37, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);

  $berkas_lamp38 = getberkas(38, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp39 = getberkas(39, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp40 = getberkas(40, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp41 = getberkas(41, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp42 = getberkas(42, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp43 = getberkas(43, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp44 = getberkas(44, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);

  $berkas_lamp45 = getberkas(45, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp46 = getberkas(46, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp47 = getberkas(47, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);

  $berkas_lamp901 = getberkas(901, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp902 = getberkas(902, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp903 = getberkas(903, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp904 = getberkas(904, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp905 = getberkas(905, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp906 = getberkas(906, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp907 = getberkas(907, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp908 = getberkas(908, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp909 = getberkas(909, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp910 = getberkas(910, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp911 = getberkas(911, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);

  $berkas_lamp912 = getberkas(912, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp913 = getberkas(913, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp914 = getberkas(914, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp915 = getberkas(915, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp916 = getberkas(916, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp917 = getberkas(917, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp918 = getberkas(918, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp919 = getberkas(919, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp920 = getberkas(920, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp921 = getberkas(921, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp922 = getberkas(922, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);

  $berkas_lamp923 = getberkas(923, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp924 = getberkas(924, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp925 = getberkas(925, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp926 = getberkas(926, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp927 = getberkas(927, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp928 = getberkas(928, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp929 = getberkas(929, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp930 = getberkas(930, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp931 = getberkas(931, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp932 = getberkas(932, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp933 = getberkas(933, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp934 = getberkas(934, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp935 = getberkas(935, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp936 = getberkas(936, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp937 = getberkas(937, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);

  $berkas_lamp938 = getberkas(938, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp939 = getberkas(939, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp940 = getberkas(940, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp941 = getberkas(941, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);

  $berkas_lamp942 = getberkas(942, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);
  $berkas_lamp943 = getberkas(943, $value['CPM_SSB_DOC_ID'], $value['CPM_BERKAS_NOPEL']);



  $html = "
    <style>
    #main-content {
        width: 900px;
    }
    #form-penerimaan input.error {
        border-color: #ff0000;
    }
    #form-penerimaan textarea.error {
        border-color: #ff0000;
    }
    .fileContainer {
      overflow: hidden;
      position: relative;
    }

    .fileContainer [type=file] {
      cursor: inherit;
      display: block;
      font-size: 999px;
      filter: alpha(opacity=0);
      min-height: 100%;
      min-width: 100%;
      opacity: 0;
      position: absolute;
      right: 0;
      text-align: right;
      top: 0;
    }
    
    </style>
      
    <script language=\"javascript\">
        jQuery.validator.setDefaults({
            debug: true,
            success: \"valid\"
          });
        $(document).ready(function(){
            $('#jnsper').attr('disabled','disabled');
             var form = $(\"#form-penerimaan\");
             form.validate({
                 rules : {
                         \"nop\" :{
                                    required : true,
                                    digits : true,
                                    },
                         \"alamatOp\" : \"required\",
                         \"kelurahanOp\" : \"required\",
                         \"kecamatanOp\" : \"required\",
                         \"npwp\" : \"required\",
                         \"namaWp\" : \"required\",
                         \"alamatWp\" : \"required\",
                         \"jnsPerolehan\" : \"required\",
                         \"noPel\" : \"required\",
                         \"telppnjl\" : {required:true,
                                        minlength:11,
                                        },
                         \"telpWp\" : {required:true,
                                        minlength:11
                                        }
                         
                         },
                  messages : {
                         \"nop\" :{
                                    required : \"harus diisi\",
                                    digits : \"harus berupa angka\",
                                    },
                         \"alamatOp\" : \"harus diisi\",
                         \"kelurahanOp\" : \"harus diisi\",
                         \"kecamatanOp\" : \"harus diisi\",
                         \"npwp\" : \"harus diisi\",
                         \"namaWp\" : \"harus diisi\",
                         \"alamatWp\" : \"harus diisi\",
                         \"jnsPerolehan\":\"harus diisi\",
                         \"noPel\" : \"harus diisi\",
                         \"telppnjl\" : {
                                        required : \"harus diisi\",
                                        minlength: jQuery.validator.format(\"Minimal 11 angka\")
                                      },
                         \"telpWp\" : {
                                        required : \"harus diisi\",
                                        minlength: jQuery.validator.format(\"Minimal 11 angka\")
                                      }
                    }
             });

            $(\"#btn-simpan\").click(function(){

                var id_bks=document.getElementById(\"idssb\").value;
                var jnsPerolehan__=document.getElementById(\"jnsper\").value;
            console.log(jnsPerolehan__)

                $(\"#process\").val($(this).val());
                if(form.valid()){
                  check_uploadan(jnsPerolehan__)
                }
                else{
                  alert(\"Harap Di check kembali, Ada data yang belum terisi!\")
                  window.scrollTo({top: 0});
                }
            });
            function check_uploadan(jnsPerolehan){
               if(jnsPerolehan==1){
                 var lamp901 = document.getElementById(\"a_901\");
                 var lamp902 = document.getElementById(\"a_902\");
                 var lamp903 = document.getElementById(\"a_903\");
                 var lamp904 = document.getElementById(\"a_904\");
                 var lamp905 = document.getElementById(\"a_905\");
                 var lamp908 = document.getElementById(\"a_908\");
                 var lamp909 = document.getElementById(\"a_909\");
                 var lamp937 = document.getElementById(\"a_937\");
                 var lamp937 = document.getElementById(\"a_937\");


                 document.getElementById(\"form-penerimaan\").submit();

                //  if(lamp901 !== null && lamp902 !== null && lamp909 !== null && lamp904 !== null && lamp905 !== null && 
                //   lamp908 !== null && lamp937 !== null){
                //      document.getElementById(\"form-penerimaan\").submit();
                //  }
                //  else{
                //     alert(\"Harap lengkapi Berkas yang diberikan keterangan WAJIB!!123\")
                //  }
               }
               else if(jnsPerolehan==2){
                 var lamp9012 = $(\"#uploaded_image_9012 > a\");
                 var lamp9022 = $(\"#uploaded_image_9022 > a\");
                 var lamp9042 = $(\"#uploaded_image_9042 > a\");
                 var lamp9052 = $(\"#uploaded_image_9052 > a\");
                 var lamp9062 = $(\"#uploaded_image_9062 > a\");
                 var lamp9122 = $(\"#uploaded_image_9122 > a\");
                 var lamp9092 = $(\"#uploaded_image_9092 > a\");
                 var lamp9372 = $(\"#uploaded_image_9372 > a\");

                 if(lamp9012.length >= 0 && 
                  lamp9022.length >= 0 && 
                  lamp9042.length >= 0 && 
                  lamp9052.length >= 0 && 
                  lamp9062.length >= 0 && 
                  lamp9122.length >= 0 && 
                  lamp9092.length >= 0 && 
                  lamp9372.length){
                     document.getElementById(\"form-penerimaan\").submit();
                 }
                 else{
                    alert(\"Harap lengkapi Berkas yang diberikan keterangan WAJIB!!\")
                 }
               }
               else if(jnsPerolehan==3){
                 var lamp9013 = $(\"#uploaded_image_9013 > a\");
                 var lamp9023 = $(\"#uploaded_image_9023 > a\");
                 var lamp9043 = $(\"#uploaded_image_9043 > a\");
                 var lamp9053 = $(\"#uploaded_image_9053 > a\");
                 var lamp9063 = $(\"#uploaded_image_9063 > a\");
                 var lamp9113 = $(\"#uploaded_image_9113 > a\");
                 var lamp9143 = $(\"#uploaded_image_9143 > a\");
                 var lamp9093 = $(\"#uploaded_image_9093 > a\");
                 var lamp9373 = $(\"#uploaded_image_9373 > a\");

                 if(lamp9013.length >= 0 && 
                  lamp9023.length >= 0 && 
                  lamp9043.length >= 0 && 
                  lamp9053.length >= 0 && 
                  lamp9063.length >= 0 && 
                  lamp9113.length >= 0 && 
                  lamp9143.length >= 0 && 
                  lamp9093.length >= 0 && 
                  lamp9373.length){
                     document.getElementById(\"form-penerimaan\").submit();
                 } 
                 else{
                    alert(\"Harap lengkapi Berkas yang diberikan keterangan WAJIB!!\")
                 }
               }
               else if(jnsPerolehan==4){
                 var lamp9014 = $(\"#uploaded_image_9014 > a\");
                 var lamp9024 = $(\"#uploaded_image_9024 > a\");
                 var lamp9044 = $(\"#uploaded_image_9044 > a\");
                 var lamp9054 = $(\"#uploaded_image_9054 > a\");
                 var lamp9064 = $(\"#uploaded_image_9064 > a\");

                 var lamp9164 = $(\"#uploaded_image_9164 > a\");
                 var lamp9174 = $(\"#uploaded_image_9174 > a\");
                 var lamp9184 = $(\"#uploaded_image_9184 > a\");
                 var lamp9194 = $(\"#uploaded_image_9194 > a\");
                 var lamp9204 = $(\"#uploaded_image_9204 > a\");

                 var lamp9094 = $(\"#uploaded_image_9094 > a\");
                 var lamp9374 = $(\"#uploaded_image_9374 > a\");

                 if(lamp9014.length >= 0 && 
                  lamp9024.length >= 0 && 
                  lamp9044.length >= 0 && 
                  lamp9054.length >= 0 && 
                  lamp9064.length >= 0 && 

                  lamp9164.length >= 0 && 
                  lamp9174.length >= 0 && 
                  lamp9184.length >= 0 && 
                  lamp9194.length >= 0 && 
                  lamp9204.length >= 0 && 
                  
                  lamp9094.length >= 0 && 
                  lamp9374.length){
                     document.getElementById(\"form-penerimaan\").submit();
                 } 
                 else{
                    alert(\"Harap lengkapi Berkas yang diberikan keterangan WAJIB!!\")
                 }
               }
               else if(jnsPerolehan==5){
                 var lamp9015 = $(\"#uploaded_image_9015 > a\");
                 var lamp9025 = $(\"#uploaded_image_9025 > a\");
                 var lamp9045 = $(\"#uploaded_image_9045 > a\");
                 var lamp9055 = $(\"#uploaded_image_9055 > a\");
                 var lamp9065 = $(\"#uploaded_image_9065 > a\");

                 var lamp9165 = $(\"#uploaded_image_9165 > a\");
                 var lamp9175 = $(\"#uploaded_image_9175 > a\");
                 var lamp9235 = $(\"#uploaded_image_9235 > a\");
                 var lamp9205 = $(\"#uploaded_image_9205 > a\");
                 var lamp9255 = $(\"#uploaded_image_9255 > a\");
                 
                 var lamp9095 = $(\"#uploaded_image_9095 > a\");
                 var lamp9375 = $(\"#uploaded_image_9375 > a\");

                 if(lamp9015.length >= 0 && 
                  lamp9025.length >= 0 && 
                  lamp9045.length >= 0 && 
                  lamp9055.length >= 0 && 
                  lamp9065.length >= 0 && 
                  
                  lamp9165.length >= 0 && 
                  lamp9175.length >= 0 && 
                  lamp9235.length >= 0 && 
                  lamp9205.length >= 0 && 
                  lamp9255.length >= 0 && 

                  lamp9095.length >= 0 && 
                  lamp9375.length){
                     document.getElementById(\"form-penerimaan\").submit();
                 } 
                 else{
                    alert(\"Harap lengkapi Berkas yang diberikan keterangan WAJIB!!\")
                 }
               }
               else if(jnsPerolehan==6){
                 var lamp9016 = $(\"#uploaded_image_9016 > a\");
                 var lamp9026 = $(\"#uploaded_image_9026 > a\");
                 var lamp9046 = $(\"#uploaded_image_9046 > a\");
                 var lamp9056 = $(\"#uploaded_image_9056 > a\");
                 var lamp9066 = $(\"#uploaded_image_9066 > a\");

                 var lamp9266 = $(\"#uploaded_image_9266 > a\");
                 var lamp9276 = $(\"#uploaded_image_9276 > a\");
                 var lamp9286 = $(\"#uploaded_image_9286 > a\");
                 
                 var lamp9096 = $(\"#uploaded_image_9096 > a\");
                 var lamp9376 = $(\"#uploaded_image_9376 > a\");

                 if(lamp9016.length >= 0 && 
                  lamp9026.length >= 0 && 
                  lamp9046.length >= 0 && 
                  lamp9056.length >= 0 && 
                  lamp9066.length >= 0 && 
                  
                  lamp9266.length >= 0 && 
                  lamp9276.length >= 0 && 
                  lamp9286.length >= 0 && 

                  lamp9096.length >= 0 && 
                  lamp9376.length){
                     document.getElementById(\"form-penerimaan\").submit();
                 } 
                 else{
                    alert(\"Harap lengkapi Berkas yang diberikan keterangan WAJIB!!\")
                 }
               }
               else if(jnsPerolehan==7){
                 var lamp9017 = $(\"#uploaded_image_9017 > a\");
                 var lamp9027 = $(\"#uploaded_image_9027 > a\");
                 var lamp9047 = $(\"#uploaded_image_9047 > a\");
                 var lamp9057 = $(\"#uploaded_image_9057 > a\");
                 var lamp9067 = $(\"#uploaded_image_9067 > a\");

                 var lamp9267 = $(\"#uploaded_image_9267 > a\");
                 var lamp9277 = $(\"#uploaded_image_9277 > a\");
                 var lamp9287 = $(\"#uploaded_image_9287 > a\");
                 
                 var lamp9097 = $(\"#uploaded_image_9097 > a\");
                 var lamp9377 = $(\"#uploaded_image_9377 > a\");
                 if(lamp9017.length >= 0 && 
                  lamp9027.length >= 0 && 
                  lamp9047.length >= 0 && 
                  lamp9057.length >= 0 && 
                  lamp9067.length >= 0 && 
                  
                  lamp9267.length >= 0 && 
                  lamp9277.length >= 0 && 
                  lamp9287.length >= 0 && 

                  lamp9097.length >= 0 && 
                  lamp9377.length){
                     document.getElementById(\"form-penerimaan\").submit();
                 } 
                 else{
                    alert(\"Harap lengkapi Berkas yang diberikan keterangan WAJIB!!\")
                 }
               }
               else if(jnsPerolehan==8){
                 var lamp9018 = $(\"#uploaded_image_9018 > a\");
                 var lamp9028 = $(\"#uploaded_image_9028 > a\");
                 var lamp9048 = $(\"#uploaded_image_9048 > a\");
                 var lamp9058 = $(\"#uploaded_image_9058 > a\");
                 var lamp9068 = $(\"#uploaded_image_9068 > a\");

                 var lamp9298 = $(\"#uploaded_image_9298 > a\");
                 if(lamp9018.length >= 0 && 
                  lamp9028.length >= 0 && 
                  lamp9048.length >= 0 && 
                  lamp9058.length >= 0 && 
                  lamp9068.length >= 0 && 
                  
                  lamp9298.length >= 0){
                     document.getElementById(\"form-penerimaan\").submit();
                 } 
                 else{
                    alert(\"Harap lengkapi Berkas yang diberikan keterangan WAJIB!!\")
                 }
               }
               else{
                  document.getElementById(\"form-penerimaan\").submit();

               }
            }
            $(\".jnsPerolehan\").hide();
            disabledJnsPerolehan();
            enabledJnsPerolehan('#jnsPerolehan" . $strJnsPerolehan . "')
                

            $( \"#nop\" ).focusout(function(){
                var val = $.trim($(\"#nop\").val());
                showDialog('Load', '<img src=\"image/icon/loading.gif\" width=\"32\" height=\"32\" style=\"margin-right:8px;\" align=\"absmiddle\"/>Tunggu', 'prompt', false, true);
                    $.ajax({
                        type: \"post\",
                        data: \"nop=\" + val,
                        url: \"./function/BPHTB/berkas/svc-check-nop.php\",
                        dataType: \"json\",
                        success: function(res) {

                            if (res.message)
                                alert(res.message);

                            $('#errnop').remove();
                            if (res.denied)
                                if (res.denied == 1) {


                                    var errnop = $(\"<span id='errnop'><br>\" + res.message + \"</span>\").css({'color': '#FF0000'});

                            }
                            

                            hideDialog();           
                        },
                        error: hideDialog() , //function(res){ hideMask();console.log(res) },
                        failure: hideDialog() //function(res){ hideMask();console.log(res) }
                    });

                                });
        });
        
        function iniAngka(evt,x){
            x.value=x.value.replace(/[^0-9]+/g, '');
        }
        
        function disabledJnsPerolehan(){            
            $(\".jnsPerolehan input[type='checkbox']\").each(function(){
                $(this).prop('disabled','disabled')
            });
        }

        function enabledJnsPerolehan(id){
            $(id).show();
            $(id+\" input[type='checkbox']\").each(function(){
                $(this).removeAttr('disabled');
            });
        }
        
        function setNoPel(id){
            $.ajax({
                type : 'post',
                data : 'type='+id,
                url: './function/BPHTB/berkas/svc-get-nopel.php',
                success : function(res){
                    $('#noPel').val(res);
                }
            });
        }
        
        function showJnsPerolehan(obj){
            var id = obj.value;
            $(\".jnsPerolehan\").hide();
            disabledJnsPerolehan();
            setNoPel(id);
            $(\"#jnsPerolehan\"+id).show();
            enabledJnsPerolehan(\"#jnsPerolehan\"+id);            
        }
    function checkfile(){
          
        var id_bks=document.getElementById(\"idssb\").value;
                $.ajax({
                        url: \"./function/BPHTB/uploadberkas/svc-cek-kelberkas.php\",
                        method: \"post\",
                        data: {id_bks:id_bks},
                        success: function(msg)
                        {                        
                            if(msg==\"1\"){
                                return true;
                            }else{
                                return true;
                            }
                        }
                    });  
            }
    function cleancheckbox(){
          var b1=document.getElementById(\"lamp1\");        var b77=document.getElementById(\"lamp77\");
          var b2=document.getElementById(\"lamp2\");              var b78=document.getElementById(\"lamp78\");
          var b3=document.getElementById(\"lamp3\");              var b79=document.getElementById(\"lamp79\");
          var b4=document.getElementById(\"lamp4\");              var b80=document.getElementById(\"lamp80\");
          var b5=document.getElementById(\"lamp5\");              var b81=document.getElementById(\"lamp81\");
          var b6=document.getElementById(\"lamp6\");              var b82=document.getElementById(\"lamp82\");
          var b7=document.getElementById(\"lamp7\");              var b83=document.getElementById(\"lamp83\");
          var b8=document.getElementById(\"lamp8\");              var b84=document.getElementById(\"lamp84\");
          var b9=document.getElementById(\"lamp9\");              var b85=document.getElementById(\"lamp85\");
          var b10=document.getElementById(\"lamp10\");            var b86=document.getElementById(\"lamp86\");
          var b11=document.getElementById(\"lamp11\");            var b87=document.getElementById(\"lamp87\");
          var b12=document.getElementById(\"lamp12\");            var b88=document.getElementById(\"lamp88\");
          var b13=document.getElementById(\"lamp13\");            var b89=document.getElementById(\"lamp89\");
          var b14=document.getElementById(\"lamp14\");            var b90=document.getElementById(\"lamp90\");
          var b15=document.getElementById(\"lamp15\");            var b91=document.getElementById(\"lamp91\");
          var b16=document.getElementById(\"lamp16\");            var b92=document.getElementById(\"lamp92\");
          var b17=document.getElementById(\"lamp17\");            var b93=document.getElementById(\"lamp93\");
          var b18=document.getElementById(\"lamp18\");            var b94=document.getElementById(\"lamp94\");
          var b19=document.getElementById(\"lamp19\");            var b95=document.getElementById(\"lamp95\");
          var b20=document.getElementById(\"lamp20\");            var b96=document.getElementById(\"lamp96\");
          var b21=document.getElementById(\"lamp21\");            var b97=document.getElementById(\"lamp97\");
          var b22=document.getElementById(\"lamp22\");            var b98=document.getElementById(\"lamp98\");
          var b23=document.getElementById(\"lamp23\");            var b99=document.getElementById(\"lamp99\");
          var b24=document.getElementById(\"lamp24\");            var b100=document.getElementById(\"lamp100\");
          var b25=document.getElementById(\"lamp25\");            var b101=document.getElementById(\"lamp101\");
          var b26=document.getElementById(\"lamp26\");            var b102=document.getElementById(\"lamp102\");
          var b27=document.getElementById(\"lamp27\");            var b103=document.getElementById(\"lamp103\");
          var b28=document.getElementById(\"lamp28\");            var b104=document.getElementById(\"lamp104\");
          var b29=document.getElementById(\"lamp29\");            var b105=document.getElementById(\"lamp105\");
          var b30=document.getElementById(\"lamp30\");            var b106=document.getElementById(\"lamp106\");
          var b31=document.getElementById(\"lamp31\");            var b107=document.getElementById(\"lamp107\");
          var b32=document.getElementById(\"lamp32\");            var b108=document.getElementById(\"lamp108\");
          var b33=document.getElementById(\"lamp33\");            var b109=document.getElementById(\"lamp109\");
          var b34=document.getElementById(\"lamp34\");            var b110=document.getElementById(\"lamp110\");
          var b35=document.getElementById(\"lamp35\");            var b111=document.getElementById(\"lamp111\");
          var b36=document.getElementById(\"lamp36\");            var b112=document.getElementById(\"lamp112\");
          var b37=document.getElementById(\"lamp37\");            var b113=document.getElementById(\"lamp113\");
          var b38=document.getElementById(\"lamp38\");            var b114=document.getElementById(\"lamp114\");
          var b39=document.getElementById(\"lamp39\");      var b115=document.getElementById(\"lamp115\");
          var b40=document.getElementById(\"lamp40\");        var b116=document.getElementById(\"lamp116\");
          var b41=document.getElementById(\"lamp41\");            var b117=document.getElementById(\"lamp117\");
          var b42=document.getElementById(\"lamp42\");            var b118=document.getElementById(\"lamp118\");
          var b43=document.getElementById(\"lamp43\");            var b119=document.getElementById(\"lamp119\");
          var b44=document.getElementById(\"lamp44\");            var b120=document.getElementById(\"lamp120\");
          var b45=document.getElementById(\"lamp45\");            var b121=document.getElementById(\"lamp121\");
          var b46=document.getElementById(\"lamp46\");            var b122=document.getElementById(\"lamp122\");
          var b47=document.getElementById(\"lamp47\");            var b123=document.getElementById(\"lamp123\");
          var b48=document.getElementById(\"lamp48\");            var b124=document.getElementById(\"lamp124\");
          var b49=document.getElementById(\"lamp49\");            var b125=document.getElementById(\"lamp125\");
          var b50=document.getElementById(\"lamp50\");            var b126=document.getElementById(\"lamp126\");
          var b51=document.getElementById(\"lamp51\");            var b127=document.getElementById(\"lamp127\");
          var b52=document.getElementById(\"lamp52\");            var b128=document.getElementById(\"lamp128\");
          var b53=document.getElementById(\"lamp53\");            var b129=document.getElementById(\"lamp129\");
          var b54=document.getElementById(\"lamp54\");            var b130=document.getElementById(\"lamp130\");
          var b55=document.getElementById(\"lamp55\");            var b131=document.getElementById(\"lamp131\");
          var b56=document.getElementById(\"lamp56\");            var b132=document.getElementById(\"lamp132\");
          var b57=document.getElementById(\"lamp57\");            var b133=document.getElementById(\"lamp133\");
          var b58=document.getElementById(\"lamp58\");            var b134=document.getElementById(\"lamp134\");
          var b59=document.getElementById(\"lamp59\");            var b135=document.getElementById(\"lamp135\");
          var b60=document.getElementById(\"lamp60\");            var b136=document.getElementById(\"lamp136\");
          var b61=document.getElementById(\"lamp61\");            var b137=document.getElementById(\"lamp137\");
          var b62=document.getElementById(\"lamp62\");            var b138=document.getElementById(\"lamp138\");
          var b63=document.getElementById(\"lamp63\");            var b139=document.getElementById(\"lamp139\");
          var b64=document.getElementById(\"lamp64\");            var b140=document.getElementById(\"lamp140\");
          var b65=document.getElementById(\"lamp65\");            var b141=document.getElementById(\"lamp141\");
          var b66=document.getElementById(\"lamp66\");            var b142=document.getElementById(\"lamp142\");
          var b67=document.getElementById(\"lamp67\");            var b143=document.getElementById(\"lamp143\");
          var b68=document.getElementById(\"lamp68\");            var b144=document.getElementById(\"lamp144\");
          var b69=document.getElementById(\"lamp69\");            var b145=document.getElementById(\"lamp145\");
          var b70=document.getElementById(\"lamp70\");            var b146=document.getElementById(\"lamp146\");
          var b71=document.getElementById(\"lamp71\");            var b147=document.getElementById(\"lamp147\");
          var b72=document.getElementById(\"lamp72\");            var b148=document.getElementById(\"lamp148\");
          var b73=document.getElementById(\"lamp73\");            var b149=document.getElementById(\"lamp149\");
          var b74=document.getElementById(\"lamp74\");            var b150=document.getElementById(\"lamp150\");
          var b75=document.getElementById(\"lamp75\");            var b151=document.getElementById(\"lamp151\");
          var b76=document.getElementById(\"lamp76\");      var b152=document.getElementById(\"lamp152\");
          var b153=document.getElementById(\"lamp153\");
          var b154=document.getElementById(\"lamp154\");
          var b155=document.getElementById(\"lamp155\");
          
          var b156=document.getElementById(\"lamp156\");
          var b157=document.getElementById(\"lamp157\");
          var b158=document.getElementById(\"lamp158\");
          var b159=document.getElementById(\"lamp159\");
          var b160=document.getElementById(\"lamp160\");
          var b161=document.getElementById(\"lamp161\");
          var b162=document.getElementById(\"lamp162\");
          
          var b163=document.getElementById(\"lamp163\");
          var b164=document.getElementById(\"lamp164\");
          var b165=document.getElementById(\"lamp165\");
          var b166=document.getElementById(\"lamp166\");
          var b167=document.getElementById(\"lamp167\");
          var b168=document.getElementById(\"lamp168\");
          var b169=document.getElementById(\"lamp169\");
          
          var b170=document.getElementById(\"lamp170\");
          var b171=document.getElementById(\"lamp171\");
          var b172=document.getElementById(\"lamp172\");
          var b173=document.getElementById(\"lamp173\");
          var b174=document.getElementById(\"lamp174\");
          var b175=document.getElementById(\"lamp175\");
          var b176=document.getElementById(\"lamp176\");
          
          var b177=document.getElementById(\"lamp177\");
          var b178=document.getElementById(\"lamp178\");
          var b179=document.getElementById(\"lamp179\");
          var b180=document.getElementById(\"lamp180\");
          var b181=document.getElementById(\"lamp181\");
          var b182=document.getElementById(\"lamp182\");
          var b183=document.getElementById(\"lamp183\");
          
          var b184=document.getElementById(\"lamp184\");
          var b185=document.getElementById(\"lamp185\");
          var b186=document.getElementById(\"lamp186\");
          var b187=document.getElementById(\"lamp187\");
          var b188=document.getElementById(\"lamp188\");
          var b189=document.getElementById(\"lamp189\");
          var b190=document.getElementById(\"lamp190\");
          
          var b191=document.getElementById(\"lamp191\");
          var b192=document.getElementById(\"lamp192\");
          var b193=document.getElementById(\"lamp193\");
          var b194=document.getElementById(\"lamp194\");
          var b195=document.getElementById(\"lamp195\");
          var b196=document.getElementById(\"lamp196\");
          var b197=document.getElementById(\"lamp197\");
          
          var b198=document.getElementById(\"lamp198\");
          var b199=document.getElementById(\"lamp199\");
          var b200=document.getElementById(\"lamp200\");
          var b201=document.getElementById(\"lamp201\");
          var b202=document.getElementById(\"lamp202\");
          var b203=document.getElementById(\"lamp203\");
          var b204=document.getElementById(\"lamp204\");
          var b205=document.getElementById(\"lamp205\");
          var b206=document.getElementById(\"lamp206\");
          var b207=document.getElementById(\"lamp207\");
          var b208=document.getElementById(\"lamp208\");
          var b209=document.getElementById(\"lamp209\");
          var b210=document.getElementById(\"lamp210\");
          var b211=document.getElementById(\"lamp211\");
          var b212=document.getElementById(\"lamp212\");
          
          b1.checked=false;       b77.checked=false;
          b2.checked=false;             b78.checked=false;
          b3.checked=false;             b79.checked=false;
          b4.checked=false;             b80.checked=false;
          b5.checked=false;             b81.checked=false;
          b6.checked=false;             b82.checked=false;
          b7.checked=false;             b83.checked=false;
          b8.checked=false;             b84.checked=false;
          b9.checked=false;             b85.checked=false;
          b10.checked=false;            b86.checked=false;
          b11.checked=false;            b87.checked=false;
          b12.checked=false;            b88.checked=false;
          b13.checked=false;            b89.checked=false;
          b14.checked=false;            b90.checked=false;
          b15.checked=false;            b91.checked=false;
          b16.checked=false;            b92.checked=false;
          b17.checked=false;            b93.checked=false;
          b18.checked=false;            b94.checked=false;
          b19.checked=false;            b95.checked=false;
          b20.checked=false;            b96.checked=false;
          b21.checked=false;            b97.checked=false;
          b22.checked=false;            b98.checked=false;
          b23.checked=false;            b99.checked=false;
          b24.checked=false;            b100.checked=false;
          b25.checked=false;            b101.checked=false;
          b26.checked=false;            b102.checked=false;
          b27.checked=false;            b103.checked=false;
          b28.checked=false;            b104.checked=false;
          b29.checked=false;            b105.checked=false;
          b30.checked=false;            b106.checked=false;
          b31.checked=false;            b107.checked=false;
          b32.checked=false;            b108.checked=false;
          b33.checked=false;            b109.checked=false;
          b34.checked=false;            b110.checked=false;
          b35.checked=false;            b111.checked=false;
          b36.checked=false;            b112.checked=false;
          b37.checked=false;            b113.checked=false;
          b38.checked=false;            b114.checked=false;
          b39.checked=false;        b115.checked=false;
          b40.checked=false;          b116.checked=false;
          b41.checked=false;            b117.checked=false;
          b42.checked=false;            b118.checked=false;
          b43.checked=false;            b119.checked=false;
          b44.checked=false;            b120.checked=false;
          b45.checked=false;            b121.checked=false;
          b46.checked=false;            b122.checked=false;
          b47.checked=false;            b123.checked=false;
          b48.checked=false;            b124.checked=false;
          b49.checked=false;            b125.checked=false;
          b50.checked=false;            b126.checked=false;
          b51.checked=false;            b127.checked=false;
          b52.checked=false;            b128.checked=false;
          b53.checked=false;            b129.checked=false;
          b54.checked=false;            b130.checked=false;
          b55.checked=false;            b131.checked=false;
          b56.checked=false;            b132.checked=false;
          b57.checked=false;            b133.checked=false;
          b58.checked=false;            b134.checked=false;
          b59.checked=false;            b135.checked=false;
          b60.checked=false;            b136.checked=false;
          b61.checked=false;            b137.checked=false;
          b62.checked=false;            b138.checked=false;
          b63.checked=false;            b139.checked=false;
          b64.checked=false;            b140.checked=false;
          b65.checked=false;            b141.checked=false;
          b66.checked=false;            b142.checked=false;
          b67.checked=false;            b143.checked=false;
          b68.checked=false;            b144.checked=false;
          b69.checked=false;            b145.checked=false;
          b70.checked=false;            b146.checked=false;
          b71.checked=false;            b147.checked=false;
          b72.checked=false;            b148.checked=false;
          b73.checked=false;            b149.checked=false;
          b74.checked=false;            b150.checked=false;
          b75.checked=false;            b151.checked=false;
          b76.checked=false;        b152.checked=false;
          b153.checked=false;
          b154.checked=false;
          b155.checked=false;
          
          b156.checked=false;
          b157.checked=false;
          b158.checked=false;
          b159.checked=false;
          b160.checked=false;
          b161.checked=false;
          b162.checked=false;
          
          b163.checked=false;
          b164.checked=false;
          b165.checked=false;
          b166.checked=false;
          b167.checked=false;
          b168.checked=false;
          b169.checked=false;
          
          b170.checked=false;
          b171.checked=false;
          b172.checked=false;
          b173.checked=false;
          b174.checked=false;
          b175.checked=false;
          b176.checked=false;
          
          b177.checked=false;
          b178.checked=false;
          b179.checked=false;
          b180.checked=false;
          b181.checked=false;
          b182.checked=false;
          b183.checked=false;
          
          b184.checked=false;
          b185.checked=false;
          b186.checked=false;
          b187.checked=false;
          b188.checked=false;
          b189.checked=false;
          b190.checked=false;
          
          b191.checked=false;
          b192.checked=false;
          b193.checked=false;
          b194.checked=false;
          b195.checked=false;
          b196.checked=false;
          b197.checked=false;
          
          b198.checked=false;
          b199.checked=false;
          b200.checked=false;
          b201.checked=false;
          b202.checked=false;
          b203.checked=false;
          b204.checked=false;
          b205.checked=false;
          b206.checked=false;
          b207.checked=false;
          b208.checked=false;
          b209.checked=false;
          b210.checked=false;
          b211.checked=false;
          b212.checked=false;
    }           
    function validateCheckBoxes() 
        {
          // for(var i=1;i<=146;i++){
          // var b[i]=document.getElementById('lamp'+i);
          // eval( 'var b'+i+' = document.getElementById(\"lamp' + i +'\");' );
          // }
          var b1=document.getElementById(\"lamp1\");              var b77=document.getElementById(\"lamp77\");
          var b2=document.getElementById(\"lamp2\");              var b78=document.getElementById(\"lamp78\");
          var b3=document.getElementById(\"lamp3\");              var b79=document.getElementById(\"lamp79\");
          var b4=document.getElementById(\"lamp4\");              var b80=document.getElementById(\"lamp80\");
          var b5=document.getElementById(\"lamp5\");              var b81=document.getElementById(\"lamp81\");
          var b6=document.getElementById(\"lamp6\");              var b82=document.getElementById(\"lamp82\");
          var b7=document.getElementById(\"lamp7\");              var b83=document.getElementById(\"lamp83\");
          var b8=document.getElementById(\"lamp8\");              var b84=document.getElementById(\"lamp84\");
          var b9=document.getElementById(\"lamp9\");              var b85=document.getElementById(\"lamp85\");
          var b10=document.getElementById(\"lamp10\");            var b86=document.getElementById(\"lamp86\");
          var b11=document.getElementById(\"lamp11\");            var b87=document.getElementById(\"lamp87\");
          var b12=document.getElementById(\"lamp12\");            var b88=document.getElementById(\"lamp88\");
          var b13=document.getElementById(\"lamp13\");            var b89=document.getElementById(\"lamp89\");
          var b14=document.getElementById(\"lamp14\");            var b90=document.getElementById(\"lamp90\");
          var b15=document.getElementById(\"lamp15\");            var b91=document.getElementById(\"lamp91\");
          var b16=document.getElementById(\"lamp16\");            var b92=document.getElementById(\"lamp92\");
          var b17=document.getElementById(\"lamp17\");            var b93=document.getElementById(\"lamp93\");
          var b18=document.getElementById(\"lamp18\");            var b94=document.getElementById(\"lamp94\");
          var b19=document.getElementById(\"lamp19\");            var b95=document.getElementById(\"lamp95\");
          var b20=document.getElementById(\"lamp20\");            var b96=document.getElementById(\"lamp96\");
          var b21=document.getElementById(\"lamp21\");            var b97=document.getElementById(\"lamp97\");
          var b22=document.getElementById(\"lamp22\");            var b98=document.getElementById(\"lamp98\");
          var b23=document.getElementById(\"lamp23\");            var b99=document.getElementById(\"lamp99\");
          var b24=document.getElementById(\"lamp24\");            var b100=document.getElementById(\"lamp100\");
          var b25=document.getElementById(\"lamp25\");            var b101=document.getElementById(\"lamp101\");
          var b26=document.getElementById(\"lamp26\");            var b102=document.getElementById(\"lamp102\");
          var b27=document.getElementById(\"lamp27\");            var b103=document.getElementById(\"lamp103\");
          var b28=document.getElementById(\"lamp28\");            var b104=document.getElementById(\"lamp104\");
          var b29=document.getElementById(\"lamp29\");            var b105=document.getElementById(\"lamp105\");
          var b30=document.getElementById(\"lamp30\");            var b106=document.getElementById(\"lamp106\");
          var b31=document.getElementById(\"lamp31\");            var b107=document.getElementById(\"lamp107\");
          var b32=document.getElementById(\"lamp32\");            var b108=document.getElementById(\"lamp108\");
          var b33=document.getElementById(\"lamp33\");            var b109=document.getElementById(\"lamp109\");
          var b34=document.getElementById(\"lamp34\");            var b110=document.getElementById(\"lamp110\");
          var b35=document.getElementById(\"lamp35\");            var b111=document.getElementById(\"lamp111\");
          var b36=document.getElementById(\"lamp36\");            var b112=document.getElementById(\"lamp112\");
          var b37=document.getElementById(\"lamp37\");            var b113=document.getElementById(\"lamp113\");
          var b38=document.getElementById(\"lamp38\");            var b114=document.getElementById(\"lamp114\");
          var b39=document.getElementById(\"lamp39\");            var b115=document.getElementById(\"lamp115\");
          var b40=document.getElementById(\"lamp40\");            var b116=document.getElementById(\"lamp116\");
          var b41=document.getElementById(\"lamp41\");            var b117=document.getElementById(\"lamp117\");
          var b42=document.getElementById(\"lamp42\");            var b118=document.getElementById(\"lamp118\");
          var b43=document.getElementById(\"lamp43\");            var b119=document.getElementById(\"lamp119\");
          var b44=document.getElementById(\"lamp44\");            var b120=document.getElementById(\"lamp120\");
          var b45=document.getElementById(\"lamp45\");            var b121=document.getElementById(\"lamp121\");
          var b46=document.getElementById(\"lamp46\");            var b122=document.getElementById(\"lamp122\");
          var b47=document.getElementById(\"lamp47\");            var b123=document.getElementById(\"lamp123\");
          var b48=document.getElementById(\"lamp48\");            var b124=document.getElementById(\"lamp124\");
          var b49=document.getElementById(\"lamp49\");            var b125=document.getElementById(\"lamp125\");
          var b50=document.getElementById(\"lamp50\");            var b126=document.getElementById(\"lamp126\");
          var b51=document.getElementById(\"lamp51\");            var b127=document.getElementById(\"lamp127\");
          var b52=document.getElementById(\"lamp52\");            var b128=document.getElementById(\"lamp128\");
          var b53=document.getElementById(\"lamp53\");            var b129=document.getElementById(\"lamp129\");
          var b54=document.getElementById(\"lamp54\");            var b130=document.getElementById(\"lamp130\");
          var b55=document.getElementById(\"lamp55\");            var b131=document.getElementById(\"lamp131\");
          var b56=document.getElementById(\"lamp56\");            var b132=document.getElementById(\"lamp132\");
          var b57=document.getElementById(\"lamp57\");            var b133=document.getElementById(\"lamp133\");
          var b58=document.getElementById(\"lamp58\");            var b134=document.getElementById(\"lamp134\");
          var b59=document.getElementById(\"lamp59\");            var b135=document.getElementById(\"lamp135\");
          var b60=document.getElementById(\"lamp60\");            var b136=document.getElementById(\"lamp136\");
          var b61=document.getElementById(\"lamp61\");            var b137=document.getElementById(\"lamp137\");
          var b62=document.getElementById(\"lamp62\");            var b138=document.getElementById(\"lamp138\");
          var b63=document.getElementById(\"lamp63\");            var b139=document.getElementById(\"lamp139\");
          var b64=document.getElementById(\"lamp64\");            var b140=document.getElementById(\"lamp140\");
          var b65=document.getElementById(\"lamp65\");            var b141=document.getElementById(\"lamp141\");
          var b66=document.getElementById(\"lamp66\");            var b142=document.getElementById(\"lamp142\");
          var b67=document.getElementById(\"lamp67\");            var b143=document.getElementById(\"lamp143\");
          var b68=document.getElementById(\"lamp68\");            var b144=document.getElementById(\"lamp144\");
          var b69=document.getElementById(\"lamp69\");            var b145=document.getElementById(\"lamp145\");
          var b70=document.getElementById(\"lamp70\");            var b146=document.getElementById(\"lamp146\");
          var b71=document.getElementById(\"lamp71\");            var b147=document.getElementById(\"lamp147\");
          var b72=document.getElementById(\"lamp72\");            var b148=document.getElementById(\"lamp148\");
          var b73=document.getElementById(\"lamp73\");            var b149=document.getElementById(\"lamp149\");
          var b74=document.getElementById(\"lamp74\");            var b150=document.getElementById(\"lamp150\");
          var b75=document.getElementById(\"lamp75\");            var b151=document.getElementById(\"lamp151\");
          var b76=document.getElementById(\"lamp76\");            var b152=document.getElementById(\"lamp152\");
          var b153=document.getElementById(\"lamp153\");
          var b154=document.getElementById(\"lamp154\");
          var b155=document.getElementById(\"lamp155\");
          
          var b156=document.getElementById(\"lamp156\");
          var b157=document.getElementById(\"lamp157\");
          var b158=document.getElementById(\"lamp158\");
          var b159=document.getElementById(\"lamp159\");
          var b160=document.getElementById(\"lamp160\");
          var b161=document.getElementById(\"lamp161\");
          var b162=document.getElementById(\"lamp162\");
          
          var b163=document.getElementById(\"lamp163\");
          var b164=document.getElementById(\"lamp164\");
          var b165=document.getElementById(\"lamp165\");
          var b166=document.getElementById(\"lamp166\");
          var b167=document.getElementById(\"lamp167\");
          var b168=document.getElementById(\"lamp168\");
          var b169=document.getElementById(\"lamp169\");
          
          var b170=document.getElementById(\"lamp170\");
          var b171=document.getElementById(\"lamp171\");
          var b172=document.getElementById(\"lamp172\");
          var b173=document.getElementById(\"lamp173\");
          var b174=document.getElementById(\"lamp174\");
          var b175=document.getElementById(\"lamp175\");
          var b176=document.getElementById(\"lamp176\");
          
          var b177=document.getElementById(\"lamp177\");
          var b178=document.getElementById(\"lamp178\");
          var b179=document.getElementById(\"lamp179\");
          var b180=document.getElementById(\"lamp180\");
          var b181=document.getElementById(\"lamp181\");
          var b182=document.getElementById(\"lamp182\");
          var b183=document.getElementById(\"lamp183\");
          
          var b184=document.getElementById(\"lamp184\");
          var b185=document.getElementById(\"lamp185\");
          var b186=document.getElementById(\"lamp186\");
          var b187=document.getElementById(\"lamp187\");
          var b188=document.getElementById(\"lamp188\");
          var b189=document.getElementById(\"lamp189\");
          var b190=document.getElementById(\"lamp190\");
          
          var b191=document.getElementById(\"lamp191\");
          var b192=document.getElementById(\"lamp192\");
          var b193=document.getElementById(\"lamp193\");
          var b194=document.getElementById(\"lamp194\");
          var b195=document.getElementById(\"lamp195\");
          var b196=document.getElementById(\"lamp196\");
          var b197=document.getElementById(\"lamp197\");
          
          var b198=document.getElementById(\"lamp198\");
          var b199=document.getElementById(\"lamp199\");
          var b200=document.getElementById(\"lamp200\");
          var b201=document.getElementById(\"lamp201\");
          var b202=document.getElementById(\"lamp202\");
          var b203=document.getElementById(\"lamp203\");
          var b204=document.getElementById(\"lamp204\");
          var b205=document.getElementById(\"lamp205\");
          var b206=document.getElementById(\"lamp206\");
          var b207=document.getElementById(\"lamp207\");
          var b208=document.getElementById(\"lamp208\");
          var b209=document.getElementById(\"lamp209\");
          var b210=document.getElementById(\"lamp210\");
          var b211=document.getElementById(\"lamp211\");
          var b212=document.getElementById(\"lamp212\");
          
          
          if(document.getElementById(\"jnsper\").value=='1'){
            if((b1.checked==true)&&(b2.checked==true)&&(b3.checked==true)&&(b4.checked==true)&&(b5.checked==true)&&(b6.checked==true)&&(b7.checked==true)&&(b8.checked==true)&&(b9.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          
          }else if(document.getElementById(\"jnsper\").value=='2'){
            if((b10.checked==true)&&(b11.checked==true)&&(b12.checked==true)&&(b13.checked==true)&&(b14.checked==true)&&(b15.checked==true)&&(b16.checked==true)&&(b17.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='3'){
            if((b18.checked==true)&&(b19.checked==true)&&(b20.checked==true)&&(b21.checked==true)&&(b22.checked==true)&&(b23.checked==true)&&(b24.checked==true)&&(b25.checked==true)&&(b26.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='4'){
            if((b27.checked==true)&&(b28.checked==true)&&(b29.checked==true)&&(b30.checked==true)&&(b31.checked==true)&&(b32.checked==true)&&(b33.checked==true)&&(b34.checked==true)&&(b35.checked==true)&&(b36.checked==true)&&(b37.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='5'){
            if((b38.checked==true)&&(b39.checked==true)&&(b40.checked==true)&&(b41.checked==true)&&(b42.checked==true)&&(b43.checked==true)&&(b44.checked==true)&&(b45.checked==true)&&(b46.checked==true)&&(b47.checked==true)&&(b48.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='6'){
            if((b49.checked==true)&&(b50.checked==true)&&(b51.checked==true)&&(b52.checked==true)&&(b53.checked==true)&&(b54.checked==true)&&(b55.checked==true)&&(b56.checked==true)&&(b57.checked==true)&&(b58.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='7'){
            if((b59.checked==true)&&(b60.checked==true)&&(b61.checked==true)&&(b62.checked==true)&&(b63.checked==true)&&(b64.checked==true)&&(b65.checked==true)&&(b66.checked==true)&&(b67.checked==true)&&(b68.checked==true)&&(b69.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='8'){
            if((b70.checked==true)&&(b71.checked==true)&&(b72.checked==true)&&(b73.checked==true)&&(b74.checked==true)&&(b64.checked==true)&&(b75.checked==true)&&(b76.checked==true)&&(b77.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='9'){
            if((b78.checked==true)&&(b79.checked==true)&&(b80.checked==true)&&(b81.checked==true)&&(b82.checked==true)&&(b83.checked==true)&&(b84.checked==true)&&(b85.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='10'){
            if((b86.checked==true)&&(b87.checked==true)&&(b88.checked==true)&&(b89.checked==true)&&(b90.checked==true)&&(b91.checked==true)&&(b92.checked==true)&&(b93.checked==true)&&(b94.checked==true)&&(b95.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='11'){
            if((b96.checked==true)&&(b97.checked==true)&&(b98.checked==true)&&(b99.checked==true)&&(b100.checked==true)&&(b101.checked==true)&&(b102.checked==true)&&(b103.checked==true)&&(b104.checked==true)&&(b105.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='12'){
            if((b106.checked==true)&&(b107.checked==true)&&(b108.checked==true)&&(b109.checked==true)&&(b110.checked==true)&&(b111.checked==true)&&(b112.checked==true)&&(b113.checked==true)&&(b114.checked==true)&&(b115.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='13'){
            if((b116.checked==true)&&(b117.checked==true)&&(b118.checked==true)&&(b119.checked==true)&&(b120.checked==true)&&(b121.checked==true)&&(b122.checked==true)&&(b123.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='14'){
            if((b124.checked==true)&&(b125.checked==true)&&(b126.checked==true)&&(b127.checked==true)&&(b128.checked==true)&&(b129.checked==true)&&(b130.checked==true)&&(b131.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='21'){
            if((b132.checked==true)&&(b133.checked==true)&&(b134.checked==true)&&(b135.checked==true)&&(b136.checked==true)&&(b137.checked==true)&&(b138.checked==true)&&(b139.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }else if(document.getElementById(\"jnsper\").value=='22'){
            if((b140.checked==true)&&(b141.checked==true)&&(b142.checked==true)&&(b143.checked==true)&&(b144.checked==true)&&(b145.checked==true)&&(b146.checked==true)){
              return true;
            }else{
              alert(\"Lengkapi berkas\");
              return false;
            }
          }
          
          
        }
        
        
        
        function check_extension(file) {
          var extension = file.toString().substr((file.toString().lastIndexOf('.') +1)).toLowerCase();
          if (!/(jpg|png|jpeg|pdf)$/ig.test(extension)) {
          alert(\"Invalid file type: \"+extension+\".  Please use JPEG, JPG, PDF or PNG.\");
          $(\"#file\").val(\"\");
          }
        }
        function showInputfile(checkbox,file)
        {
          
          if (checkbox.checked)
          {
            $('#file_lamp'+file).removeAttr('hidden','hidden');
            $('#btn_'+file).removeAttr('hidden','hidden');
          }else{
            $('#file_lamp'+file).attr('hidden','hidden');
            $('#btn_'+file).attr('hidden','hidden');
          }
        }
        function gethide(){
          var f = document.getElementById('form-penerimaan');
          var els = f.elements;
          for (var i = 0, len = els.length; i < len; i++) {
            x = els[i];
            if (x.type == 'file'){
                if($('#file_lamp'+i).is(':visible')){
                if ($('#file_lamp'+i).get(0).files.length == 0) {
                  alert(\"Belum memilih file.\");
                  
                  return false;
                  
                }
              }
            }
          }
          
        }
        
        function upload(id_file,no,i){
          var form_data = new FormData();
          form_data.append('file', document.getElementById(id_file).files[0]);
             
             var jp='{$value['CPM_BERKAS_JNS_PEROLEHAN']}';
             var ssb_id='{$value['CPM_SSB_DOC_ID']}';
             var id_berkas='{$value['CPM_BERKAS_NOPEL']}';
             var a__ = i;
             $.ajax({
            url:'function/BPHTB/uploadberkas/upload.php?jp='+jp+'&ssb_id='+ssb_id+'&id_berkas='+id_berkas+'&no='+no+'&id_check='+a__,
            type:'POST',
            data: form_data,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend:function(){
             $('#uploaded_image_'+i).html(\"<label class='text-success'>Image Uploading...</label>\");
            },   
            success:function(data)
            {
             $('#uploaded_image_'+i).html(data);
             //$(e).attr('hidden','hidden');
            }
             });
         }  
    </script>
    <div id=\"main-content\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
        <input type=\"hidden\" name=\"process\" id=\"process\">
        <input type=\"hidden\" name=\"idssb\" id=\"idssb\" value=\"{$value['CPM_BERKAS_ID']}\">
    <table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
              <tr>
                <td colspan=\"2\"><strong><font size=\"+2\">Penerimaan Berkas Pelayanan BPHTB</font></strong><br /><hr><br /></td>
              </tr>
                  <tr><td colspan=\"2\"><h3>A. DATA OBJEK PAJAK</h3></td></tr>
                      <tr>
                        <td width=\"39%\"><label for=\"noPel\">Nomor Pelaporan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"noPel\" readonly id=\"noPel\" style=\"text-align:right\" value=\"{$value['CPM_BERKAS_NOPEL']}\" size=\"20\" maxlength=\"50\" placeholder=\"Nomor Pelayanan\"/>                                      
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                        <td width=\"60%\">";
// var_dump($value['CPM_BERKAS_TANGGAL'] );die;
                        $html .= "<input type=\"date\" name=\"tglMasuk\" id=\"tglMasuk\"  value=\"" . ($value['CPM_BERKAS_TANGGAL'] != '' ? date('Y-m-d', strtotime($value['CPM_BERKAS_TANGGAL'])) : date('Y-m-d', strtotime($today))) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"sptpd\">NOP *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"nop\" id=\"nop\" size=\"50\" " . (isset($_REQUEST['svcid']) ? "readonly" : "") . "  maxlength=\"22\" onblur=\"return iniAngka(event,this)\" onkeypress=\"return iniAngka(event,this)\" value=\"{$value['CPM_BERKAS_NOP']}\" placeholder=\"NOP\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"alamatOp\">Alamat Objek Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"alamatOp\" id=\"alamatOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_ALAMAT_OP']}\" placeholder=\"Alamat Objek Pajak\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"kelurahanOp\">Kelurahan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"kelurahanOp\" id=\"kelurahanOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_KELURAHAN_OP']}\" placeholder=\"Kelurahan Objek Pajak\" />
                        </td>
                      </tr>  
                      <tr>
                        <td width=\"39%\"><label for=\"kecamatanOp\">Kecamatan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"kecamatanOp\" id=\"kecamatanOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_KECAMATAN_OP']}\" placeholder=\"Kecamatan Objek Pajak\" />
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3>B. DATA WAJIB PAJAK</h3></td></tr>                                                                        
                      <tr>
                        <td width=\"39%\"><label for=\"npwp\">NPWP / KTP *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"npwp\" id=\"npwp\" size=\"50\" maxlength=\"50\" value=\"{$value['CPM_BERKAS_NPWP']}\" placeholder=\"NPWP / KTP\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"namaWp\">Nama Wajib Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"namaWp\" id=\"namaWp\" size=\"50\" maxlength=\"50\" value=\"{$value['CPM_BERKAS_NAMA_WP']}\" placeholder=\"Nama Wajib Pajak\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"telpWp\">Nomor Telp Wajib Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"telpWp\" id=\"telpWp\" size=\"50\" maxlength=\"20\" value=\"{$value['CPM_BERKAS_TELP_WP']}\" placeholder=\"Nomor Telp Wajib Pajak\" />
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3></h3></td></tr>
                      <tr>
                        <td width=\"39%\"><label for=\"telppnjl\">Nomor Telp Penjual *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"telppnjl\" id=\"telppnjl\" size=\"50\" maxlength=\"20\" value=\"{$value['CPM_BERKAS_TELP_OP']}\" placeholder=\"Nomor Telp Penjual\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"hargaTran\">Harga Transaksi *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"hargaTran\" style=\"text-align:right\" id=\"hargaTran\" size=\"50\" maxlength=\"12\" value=\"{$value['CPM_BERKAS_HARGA_TRAN']}\" placeholder=\"Harga Transaksi\" />
                        </td>
                      </tr>
                      <tr valign=\"top\">
                        <td width=\"39%\"><label for=\"jnsPerolehan\">Jenis Perolehan *</label></td>
                        <td width=\"60%\">
                          <!-- <input type=\"radio\" name=\"jnsPerolehan\" value=\"1\" {$jnsPerolehan[1]} onclick=\"javascript:showJnsPerolehan(this)\"/> SK<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"2\" {$jnsPerolehan[2]} onclick=\"javascript:showJnsPerolehan(this)\"/> JUAL-BELI<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"3\" {$jnsPerolehan[3]} onclick=\"javascript:showJnsPerolehan(this)\"/> HIBAH<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"4\" {$jnsPerolehan[4]} onclick=\"javascript:showJnsPerolehan(this)\"/> WARIS<br/> -->
              " . $jnsPerolehan . "
            </td>
                      </tr>
                      <tr>
                        <td width=\"39%\">Persyaratan Administrasi :</td>
                        <td width=\"60%\">
                        </td>
                      </tr>
                      <tr>
                                              <td width=\"39%\" style=\"color:red\">File Maximal 2 Mb</td>

                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan1\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">

                            <!-- MODUL JUAL BELI -->
                <table border='0'><tr>
                    <td width='50%'>
                    
                    <tr>
                      <td><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp901' id='file_lamp901'>
                        <input type='button' id='btn_901' name='btn_901' value='Upload'  onclick=\"upload('file_lamp901',901,  901);\">
                        <span id='uploaded_image_901'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li>  2. Upload Kartu keluarga  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp942' id='file_lamp942'>
                        <input type='button' id='btn_942' name='btn_942' value='Upload'  onclick=\"upload('file_lamp942',942,  942);\">
                        <span id='uploaded_image_942'>" . $berkas_lamp942 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN KTP pembeli yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp902' id='file_lamp902'>
                        <input type='button' id='btn_902' name='btn_902' value='Upload'  onclick=\"upload('file_lamp902',902,  902);\">
                        <span id='uploaded_image_902'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN KTP penjual yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp943' id='file_lamp943'>
                        <input type='button' id='btn_943' name='btn_943' value='Upload'  onclick=\"upload('file_lamp943',943,  943);\">
                        <span id='uploaded_image_943'>" . $berkas_lamp943 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 5. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp903' id='file_lamp903'><input type='button' id='btn_903' name='btn_903' value='Upload'  onclick=\"upload('file_lamp903',903,  903);\">
                        <span id='uploaded_image_903'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 6. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp904' id='file_lamp904'><input type='button' id='btn_904' name='btn_904' value='Upload'  onclick=\"upload('file_lamp904',904,  904);\">
                        <span id='uploaded_image_904'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 7. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp905' id='file_lamp905'><input type='button' id='btn_905' name='btn_905' value='Upload'  onclick=\"upload('file_lamp905',905,  905);\">
                        <span id='uploaded_image_905'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 8. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp906' id='file_lamp906'><input type='button' id='btn_906' name='btn_906' value='Upload'  onclick=\"upload('file_lamp906',906,  906);\">
                        <span id='uploaded_image_906'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 9. SCAN daftar harga (Pricelist) dalam hal pembelian dan pengembangan (perumahan/kavlingan)</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp907' id='file_lamp907'><input type='button' id='btn_907' name='btn_907' value='Upload'  onclick=\"upload('file_lamp907',907,  907);\">
                        <span id='uploaded_image_907'>" . $berkas_lamp907 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 10. SCAN surat keterangan jual beli tanah / Bukti transaksi dilegalisir (Kwitansi)<span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp908' id='file_lamp908'><input type='button' id='btn_908' name='btn_908' value='Upload'  onclick=\"upload('file_lamp908',908,  908);\">
                        <span id='uploaded_image_908'>" . $berkas_lamp908 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 11. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp909' id='file_lamp909'><input type='button' id='btn_909' name='btn_909' value='Upload'  onclick=\"upload('file_lamp909',909,  909);\">
                        <span id='uploaded_image_909'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp937' id='file_lamp937'><input type='button' id='btn_937' name='btn_937' value='Upload'  onclick=\"upload('file_lamp937',937,  937);\">
                        <span id='uploaded_image_937'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 12. Scan Akta (Akta Hibah, Akta Jual Beli, Akta Waris)</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp910' id='file_lamp910'><input type='button' id='btn_910' name='btn_910' value='Upload'  onclick=\"upload('file_lamp910',910,  910);\">
                        <span id='uploaded_image_910'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 13. SCAN Surat Akta Kematian </li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp913' id='file_lamp913'><input type='button' id='btn_913' name='btn_913' value='Upload'  onclick=\"upload('file_lamp913',913,  913);\">
                        <span id='uploaded_image_913'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>

                </table>
              </ul>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan2\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">

                            <!-- MODUL TUKAR MENUKAR -->
                <table border='0'>
                    <tr>
                      <td><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9012' id='file_lamp9012'>
                        <input type='button' id='btn_9012' name='btn_9012' value='Upload'  onclick=\"upload('file_lamp9012',901,  9012);\">
                        <span id='uploaded_image_9012'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9022' id='file_lamp9022'>
                        <input type='button' id='btn_9022' name='btn_9022' value='Upload'  onclick=\"upload('file_lamp9022',902,  9022);\">
                        <span id='uploaded_image_9022'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>
                  
                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9032' id='file_lamp9032'><input type='button' id='btn_9032' name='btn_9032' value='Upload'  onclick=\"upload('file_lamp9032',903,  9032);\">
                        <span id='uploaded_image_9032'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9042' id='file_lamp9042'>
                        <input type='button' id='btn_9042' name='btn_9042' value='Upload'  onclick=\"upload('file_lamp9042',904,  9042);\">
                        <span id='uploaded_image_9042'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9052' id='file_lamp9052'>
                        <input type='button' id='btn_9052' name='btn_9052' value='Upload'  onclick=\"upload('file_lamp9052',905,  9052);\">
                        <span id='uploaded_image_9052'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9062' id='file_lamp9062'>
                        <input type='button' id='btn_9062' name='btn_9062' value='Upload'  onclick=\"upload('file_lamp9062',906,  9062);\">
                        <span id='uploaded_image_9062'>" . $berkas_lamp9062 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 7. SCAN Surat Pernyataan Tukar Hak Atas Tanah dari yang mengalihkan hak <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9122' id='file_lamp9122'>
                        <input type='button' id='btn_9122' name='btn_9122' value='Upload'  onclick=\"upload('file_lamp9122',912,  9122);\">
                        <span id='uploaded_image_9122'>" . $berkas_lamp912 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 8. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>

                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9092' id='file_lamp9092'>
                        <input type='button' id='btn_9092' name='btn_9092' value='Upload'  onclick=\"upload('file_lamp9092',909,  9092);\">
                        <span id='uploaded_image_9092'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9372' id='file_lamp9372'>
                        <input type='button' id='btn_9372' name='btn_9372' value='Upload'  onclick=\"upload('file_lamp9372',937,  9372);\">
                        <span id='uploaded_image_9372'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9102' id='file_lamp9102'>
                        <input type='button' id='btn_9102' name='btn_9102' value='Upload'  onclick=\"upload('file_lamp9102',910,  9102);\">
                        <span id='uploaded_image_9102'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9132' id='file_lamp9132'>
                        <input type='button' id='btn_9132' name='btn_9132' value='Upload'  onclick=\"upload('file_lamp9132',913,  9132);\">
                        <span id='uploaded_image_9132'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                </table>
                            </ul>           
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan3\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">

                            <!-- MODUL HIBAH -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9013' id='file_lamp9013'><input type='button' id='btn_9013' name='btn_9013' value='Upload'  onclick=\"upload('file_lamp9013',901,  9013);\">
                        <span id='uploaded_image_9013'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9023' id='file_lamp9023'>
                        <input type='button' id='btn_9023' name='btn_9023' value='Upload'  onclick=\"upload('file_lamp9023',902,  9023);\">
                        <span id='uploaded_image_9023'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9033' id='file_lamp9033'>
                        <input type='button' id='btn_9033' name='btn_9033' value='Upload'  onclick=\"upload('file_lamp9033',903,  9033);\">
                        <span id='uploaded_image_9033'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9043' id='file_lamp9043'>
                        <input type='button' id='btn_9043' name='btn_9043' value='Upload'  onclick=\"upload('file_lamp9043',904,  9043);\">
                        <span id='uploaded_image_9043'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9053' id='file_lamp9053'>
                        <input type='button' id='btn_9053' name='btn_9053' value='Upload'  onclick=\"upload('file_lamp9053',905,  9053);\">
                        <span id='uploaded_image_9053'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9063' id='file_lamp9063'>
                        <input type='button' id='btn_9063' name='btn_9063' value='Upload'  onclick=\"upload('file_lamp9063',906,  9063);\">
                        <span id='uploaded_image_9063'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 7. SCAN KTP Pemberi dan Penerima Hibah yang masih berlaku <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9113' id='file_lamp9113'>
                        <input type='button' id='btn_9113' name='btn_9113' value='Upload'  onclick=\"upload('file_lamp9113',911,  9113);\">
                        <span id='uploaded_image_9113'>" . $berkas_lamp911 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 8. SCAN Pertanyaan Hibah/Surat keterangan Hibah  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp914' id='file_lamp9143'>
                        <input type='button' id='btn_9143' name='btn_9143' value='Upload'  onclick=\"upload('file_lamp9143',914,  9143);\">
                        <span id='uploaded_image_9143'>" . $berkas_lamp914 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9093' id='file_lamp9093'>
                        <input type='button' id='btn_9093' name='btn_9093' value='Upload'  onclick=\"upload('file_lamp9093',909,  9093);\">
                        <span id='uploaded_image_9093'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9373' id='file_lamp9373'>
                        <input type='button' id='btn_9373' name='btn_9373' value='Upload'  onclick=\"upload('file_lamp9373',937,  9373);\">
                        <span id='uploaded_image_9373'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 10. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9103' id='file_lamp9103'>
                        <input type='button' id='btn_9103' name='btn_9103' value='Upload'  onclick=\"upload('file_lamp9103',910,  9103);\">
                        <span id='uploaded_image_9103'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 11. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9133' id='file_lamp9133'>
                        <input type='button' id='btn_9133' name='btn_9133' value='Upload'  onclick=\"upload('file_lamp9133',913,  9133);\">
                        <span id='uploaded_image_9133'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                  
                </table>    
                            </ul>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan4\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">

                                    <!-- HIBAH WASIAT -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9014' id='file_lamp9014'>
                        <input type='button' id='btn_9014' name='btn_9014' value='Upload'  onclick=\"upload('file_lamp9014',901,  9014);\">
                        <span id='uploaded_image_9014'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>
                  <tr>    

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9024' id='file_lamp9024'>
                        <input type='button' id='btn_9024' name='btn_9024' value='Upload'  onclick=\"upload('file_lamp9024',902,  9024);\">
                        <span id='uploaded_image_9024'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>  
                  
                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9034' id='file_lamp9034'>
                        <input type='button' id='btn_9034' name='btn_9034' value='Upload'  onclick=\"upload('file_lamp9034',903,  9034);\">
                        <span id='uploaded_image_9034'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9044' id='file_lamp9044'>
                        <input type='button' id='btn_9044' name='btn_9044' value='Upload'  onclick=\"upload('file_lamp9044',904,  9044);\">
                        <span id='uploaded_image_9044'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9054' id='file_lamp9054'>
                        <input type='button' id='btn_9054' name='btn_9054' value='Upload'  onclick=\"upload('file_lamp9054',905,  9054);\">
                        <span id='uploaded_image_9054'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9064' id='file_lamp9064'>
                        <input type='button' id='btn_9064' name='btn_9064' value='Upload'  onclick=\"upload('file_lamp9064',906,  9064);\">
                        <span id='uploaded_image_9064'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 7. SCAN KTP Para ahli Waris/penerima Hibah Wasiat <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9164' id='file_lamp9164'>
                        <input type='button' id='btn_9164' name='btn_9164' value='Upload'  onclick=\"upload('file_lamp9164',916,  9164);\">
                        <span id='uploaded_image_9164'>" . $berkas_lamp916 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 8. SCAN Surat/Keterangan Kematian <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9174' id='file_lamp9174'>
                        <input type='button' id='btn_9174' name='btn_9174' value='Upload'  onclick=\"upload('file_lamp9174',917,  9174);\">
                        <span id='uploaded_image_9174'>" . $berkas_lamp917 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. SCAN Surat Pernyataan Hibah <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9184' id='file_lamp9184'>
                        <input type='button' id='btn_9184' name='btn_9184' value='Upload'  onclick=\"upload('file_lamp9184',918,  9184);\">
                        <span id='uploaded_image_9184'>" . $berkas_lamp918 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. SCAN Surat Kuasa hibah dalam hal Dikuasakan <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9194' id='file_lamp9194'>
                        <input type='button' id='btn_9194' name='btn_9194' value='Upload'  onclick=\"upload('file_lamp9194',919,  9194);\">
                        <span id='uploaded_image_9194'>" . $berkas_lamp919
    . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 11. SCAN Kartu Keluarga <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9204' id='file_lamp9204'>
                        <input type='button' id='btn_9204' name='btn_9204' value='Upload'  onclick=\"upload('file_lamp9204',920,  9204);\">
                        <span id='uploaded_image_9204'>" . $berkas_lamp920 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 12. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                       </td>
                    </tr>
                    
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9094' id='file_lamp9094'>
                        <input type='button' id='btn_9094' name='btn_9094' value='Upload'  onclick=\"upload('file_lamp9094',909,  9094);\">
                        <span id='uploaded_image_9094'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9374' id='file_lamp9374'>
                        <input type='button' id='btn_9374' name='btn_9374' value='Upload'  onclick=\"upload('file_lamp9374',937,  9374);\">
                        <span id='uploaded_image_9374'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 13. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9104' id='file_lamp9104'>
                        <input type='button' id='btn_9104' name='btn_9104' value='Upload'  onclick=\"upload('file_lamp9104',910,  9104);\">
                        <span id='uploaded_image_9104'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 14. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9134' id='file_lamp9134'>
                        <input type='button' id='btn_9134' name='btn_9134' value='Upload'  onclick=\"upload('file_lamp9134',913,  9134);\">
                        <span id='uploaded_image_9134'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                </table>
              </ul>
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan5\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                             <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                             <!-- MODUL WARIS -->
                <table border='0'>
                
                
                    
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9015' id='file_lamp9015'>
                        <input type='button' id='btn_9015' name='btn_9015' value='Upload'  onclick=\"upload('file_lamp9015',901,  9015);\">
                        <span id='uploaded_image_9015'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9025' id='file_lamp9025'><input type='button' id='btn_9025' name='btn_9025' value='Upload'  onclick=\"upload('file_lamp9025',902,  9025);\">
                        <span id='uploaded_image_9025'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9035' id='file_lamp9035'>
                        <input type='button' id='btn_9035' name='btn_9035' value='Upload'  onclick=\"upload('file_lamp9035',903,  9035);\">
                        <span id='uploaded_image_9035'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9045' id='file_lamp9045'>
                        <input type='button' id='btn_9045' name='btn_9045' value='Upload'  onclick=\"upload('file_lamp9045',904,  9045);\">
                        <span id='uploaded_image_9045'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9055' id='file_lamp9055'>
                        <input type='button' id='btn_9055' name='btn_9055' value='Upload'  onclick=\"upload('file_lamp9055',905,  9055);\">
                        <span id='uploaded_image_9055'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9065' id='file_lamp9065'>
                        <input type='button' id='btn_9065' name='btn_9065' value='Upload'  onclick=\"upload('file_lamp9065',906,  9065);\">
                        <span id='uploaded_image_9065'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 7. SCAN KTP Para ahli Waris/penerima Hibah Wasiat <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9165' id='file_lamp9165'>
                        <input type='button' id='btn_9165' name='btn_9165' value='Upload'  onclick=\"upload('file_lamp9165',916,  9165);\">
                        <span id='uploaded_image_9165'>" . $berkas_lamp916 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 8. SCAN Surat/Keterangan Kematian <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9175' id='file_lamp9175'>
                        <input type='button' id='btn_9175' name='btn_9175' value='Upload'  onclick=\"upload('file_lamp9175',917,  9175);\">
                        <span id='uploaded_image_9175'>" . $berkas_lamp917 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. SCAN Surat Pernyataan Waris <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9235' id='file_lamp9235'>
                        <input type='button' id='btn_9235' name='btn_9235' value='Upload'  onclick=\"upload('file_lamp9235',923,  9235);\">
                        <span id='uploaded_image_9235'>" . $berkas_lamp923 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. SCAN Kartu Keluarga <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9205' id='file_lamp9205'>
                        <input type='button' id='btn_9205' name='btn_9205' value='Upload'  onclick=\"upload('file_lamp9205',920,  9205);\">
                        <span id='uploaded_image_9205'>" . $berkas_lamp920 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 11. SCAN Surat Kuasa Waris dalam hal Dikuasakan <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9255' id='file_lamp9255'>
                        <input type='button' id='btn_9255' name='btn_9255' value='Upload'  onclick=\"upload('file_lamp9255',925,  9255);\">
                        <span id='uploaded_image_9255'>" . $berkas_lamp925 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 12. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9095' id='file_lamp9095'>
                        <input type='button' id='btn_9095' name='btn_9095' value='Upload'  onclick=\"upload('file_lamp9095',909,  9095);\">
                        <span id='uploaded_image_9095'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9375' id='file_lamp9375'>
                        <input type='button' id='btn_9375' name='btn_9375' value='Upload'  onclick=\"upload('file_lamp9375',937,  9375);\">
                        <span id='uploaded_image_9375'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 13. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9105' id='file_lamp9105'>
                        <input type='button' id='btn_9105' name='btn_9105' value='Upload'  onclick=\"upload('file_lamp9105',910,  9105);\">
                        <span id='uploaded_image_9105'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 14. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9135' id='file_lamp9135'>
                        <input type='button' id='btn_9135' name='btn_9135' value='Upload'  onclick=\"upload('file_lamp9135',913,  9135);\">
                        <span id='uploaded_image_9135'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                </table>
              </ul>
                        </td>
                      </tr>
                      <!-- MODUL PEMASUKAN DALAM PERSEROAN -->
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan6\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9016' id='file_lamp9016'>
                        <input type='button' id='btn_9016' name='btn_9016' value='Upload'  onclick=\"upload('file_lamp9016',901,  9016);\">
                        <span id='uploaded_image_9016'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>


                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9026' id='file_lamp9026'>
                        <input type='button' id='btn_9026' name='btn_9026' value='Upload'  onclick=\"upload('file_lamp9026',902,  9026);\">
                        <span id='uploaded_image_9026'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9036' id='file_lamp9036'>
                        <input type='button' id='btn_9036' name='btn_9036' value='Upload'  onclick=\"upload('file_lamp9036',903,  9036);\">
                        <span id='uploaded_image_9036'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9046' id='file_lamp9046'>
                        <input type='button' id='btn_9046' name='btn_9046' value='Upload'  onclick=\"upload('file_lamp9046',904,  9046);\">
                        <span id='uploaded_image_9046'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9056' id='file_lamp9056'>
                        <input type='button' id='btn_9056' name='btn_9056' value='Upload'  onclick=\"upload('file_lamp9056',905,  9056);\">
                        <span id='uploaded_image_9056'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9066' id='file_lamp9066'>
                        <input type='button' id='btn_9066' name='btn_9066' value='Upload'  onclick=\"upload('file_lamp9066',906,  9066);\">
                        <span id='uploaded_image_9066'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 7. Scan Akta Pendirian Perusahaan yang terbaru <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9266' id='file_lamp9266'>
                        <input type='button' id='btn_9266' name='btn_9266' value='Upload'  onclick=\"upload('file_lamp9266',926,  9266);\">
                        <span id='uploaded_image_9266'>" . $berkas_lamp926 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 8. SCAN NPWP Perusahaan <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9276' id='file_lamp9276'>
                        <input type='button' id='btn_9276' name='btn_9276' value='Upload'  onclick=\"upload('file_lamp9276',927,  9276);\">
                        <span id='uploaded_image_9276'>" . $berkas_lamp927 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 9. SCAN Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9286' id='file_lamp9286'>
                        <input type='button' id='btn_9286' name='btn_9286' value='Upload'  onclick=\"upload('file_lamp9286',928,  9286);\">
                        <span id='uploaded_image_9286'>" . $berkas_lamp928 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>

                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9096' id='file_lamp9096'>
                        <input type='button' id='btn_9096' name='btn_9096' value='Upload'  onclick=\"upload('file_lamp9096',909,  9096);\">
                        <span id='uploaded_image_9096'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9376' id='file_lamp9376'>
                        <input type='button' id='btn_9376' name='btn_9376' value='Upload'  onclick=\"upload('file_lamp9376',937,  9376);\">
                        <span id='uploaded_image_9376'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                  
                    <tr>
                      <td><li> 11. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp910' id='file_lamp9106'>
                        <input type='button' id='btn_9106' name='btn_9106' value='Upload'  onclick=\"upload('file_lamp9106',910,  9106);\">
                        <span id='uploaded_image_9106'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 12. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9136' id='file_lamp9136'>
                        <input type='button' id='btn_9136' name='btn_9136' value='Upload'  onclick=\"upload('file_lamp9136',913,  9136);\">
                        <span id='uploaded_image_9136'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                  </table>
              </ul>                                                 
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan7\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL APHB -->
                <table border='0'><tr>
                  <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9017' id='file_lamp9017'>
                        <input type='button' id='btn_9017' name='btn_9017' value='Upload'  onclick=\"upload('file_lamp9017',901,  9017);\">
                        <span id='uploaded_image_9017'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9027' id='file_lamp9027'>
                        <input type='button' id='btn_9027' name='btn_9027' value='Upload'  onclick=\"upload('file_lamp9027',902,  9027);\">
                        <span id='uploaded_image_9027'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9037' id='file_lamp9037'>
                        <input type='button' id='btn_9037' name='btn_9037' value='Upload'  onclick=\"upload('file_lamp9037',903,  9037);\">
                        <span id='uploaded_image_9037'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9047' id='file_lamp9047'>
                        <input type='button' id='btn_9047' name='btn_9047' value='Upload'  onclick=\"upload('file_lamp9047',904,  9047);\">
                        <span id='uploaded_image_9047'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9057' id='file_lamp9057'>
                        <input type='button' id='btn_9057' name='btn_9057' value='Upload'  onclick=\"upload('file_lamp9057',905,  9057);\">
                        <span id='uploaded_image_9057'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9067' id='file_lamp9067'>
                        <input type='button' id='btn_9067' name='btn_9067' value='Upload'  onclick=\"upload('file_lamp9067',906,  9067);\">
                        <span id='uploaded_image_9067'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 7. SCAN KTP Para ahli Waris <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9217' id='file_lamp9217'>
                        <input type='button' id='btn_9217' name='btn_9217' value='Upload'  onclick=\"upload('file_lamp9217',921,  9217);\">
                        <span id='uploaded_image_9217'>" . $berkas_lamp921 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 8. SCAN Surat/Keterangan Kematian <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9177' id='file_lamp9177'>
                        <input type='button' id='btn_9177' name='btn_9177' value='Upload'  onclick=\"upload('file_lamp9177',917,  9177);\">
                        <span id='uploaded_image_9177'>" . $berkas_lamp917 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. SCAN Surat Pernyataan Waris <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9237' id='file_lamp9237'>
                        <input type='button' id='btn_9237' name='btn_9237' value='Upload'  onclick=\"upload('file_lamp9237',923,  9237);\">
                        <span id='uploaded_image_9237'>" . $berkas_lamp923 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>

                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9097' id='file_lamp9097'>
                        <input type='button' id='btn_9097' name='btn_9097' value='Upload'  onclick=\"upload('file_lamp9097',909,  9097);\">
                        <span id='uploaded_image_9097'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9377' id='file_lamp9377'>
                        <input type='button' id='btn_9377' name='btn_9377' value='Upload'  onclick=\"upload('file_lamp9377',937,  9377);\">
                        <span id='uploaded_image_9377'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 11. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9107' id='file_lamp9107'>
                        <input type='button' id='btn_9107' name='btn_9107' value='Upload'  onclick=\"upload('file_lamp9107',910,  9107);\">
                        <span id='uploaded_image_9107'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 12. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9137' id='file_lamp9137'>
                        <input type='button' id='btn_9137' name='btn_9137' value='Upload'  onclick=\"upload('file_lamp9137',913,  9137);\">
                        <span id='uploaded_image_9137'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                </table>
              </ul>
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan8\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL LELANG -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9018' id='file_lamp9018'>
                        <input type='button' id='btn_9018' name='btn_9018' value='Upload'  onclick=\"upload('file_lamp9018',901,  9018);\">
                        <span id='uploaded_image_9018'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9028' id='file_lamp9028'>
                        <input type='button' id='btn_9028' name='btn_9028' value='Upload'  onclick=\"upload('file_lamp9028',902,  9028);\">
                        <span id='uploaded_image_9028'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9038' id='file_lamp9038'>
                        <input type='button' id='btn_9038' name='btn_9038' value='Upload'  onclick=\"upload('file_lamp9038',903,  9038);\">
                        <span id='uploaded_image_9038'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9048' id='file_lamp9048'>
                        <input type='button' id='btn_9048' name='btn_9048' value='Upload'  onclick=\"upload('file_lamp9048',904,  9048);\">
                        <span id='uploaded_image_9048'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9058' id='file_lamp9058'>
                        <input type='button' id='btn_9058' name='btn_9058' value='Upload'  onclick=\"upload('file_lamp9058',905,  9058);\">
                        <span id='uploaded_image_9058'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9068' id='file_lamp9068'>
                        <input type='button' id='btn_9068' name='btn_9068' value='Upload'  onclick=\"upload('file_lamp9068',906,  9068);\">
                        <span id='uploaded_image_9068'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>

                    
                    <tr>
                      <td><li> 7. Fotocopy Kwitansi lelang/Risalah Lelang <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp929' id='file_lamp9298'>
                        <input type='button' id='btn_9298' name='btn_9298' value='Upload'  onclick=\"upload('file_lamp9298',929,  9298);\">
                        <span id='uploaded_image_9298'>" . $berkas_lamp929 . "</span>
                       </td>
                    </tr>

                  
                    <tr>
                      <td><li> 8. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9108' id='file_lamp9108'>
                        <input type='button' id='btn_9108' name='btn_9108' value='Upload'  onclick=\"upload('file_lamp9108',910,  9108);\">
                        <span id='uploaded_image_9108'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9138' id='file_lamp9138'>
                        <input type='button' id='btn_9138' name='btn_9138' value='Upload'  onclick=\"upload('file_lamp9138',913,  9138);\">
                        <span id='uploaded_image_9138'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                  
                </table>
              </ul>                                                 
                        </td>                                                     
                      </tr>                                                   
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan9\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL PUTUSAN HAKIM -->
                  <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9019' id='file_lamp9019'>
                        <input type='button' id='btn_9019' name='btn_9019' value='Upload'  onclick=\"upload('file_lamp9019',901,  9019);\">
                        <span id='uploaded_image_9019'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9029' id='file_lamp9029'>
                        <input type='button' id='btn_9029' name='btn_9029' value='Upload'  onclick=\"upload('file_lamp9029',902,  9029);\">
                        <span id='uploaded_image_9029'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9039' id='file_lamp9039'>
                        <input type='button' id='btn_9039' name='btn_9039' value='Upload'  onclick=\"upload('file_lamp9039',903,  9039);\">
                        <span id='uploaded_image_9039'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9049' id='file_lamp9049'>
                        <input type='button' id='btn_9049' name='btn_9049' value='Upload'  onclick=\"upload('file_lamp9049',904,  9049);\">
                        <span id='uploaded_image_9049'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9059' id='file_lamp9059'>
                        <input type='button' id='btn_9059' name='btn_9059' value='Upload'  onclick=\"upload('file_lamp9059',905,  9059);\">
                        <span id='uploaded_image_9059'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9069' id='file_lamp9069'>
                        <input type='button' id='btn_9069' name='btn_9069' value='Upload'  onclick=\"upload('file_lamp9069',906,  9069);\">
                        <span id='uploaded_image_9069'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>

                    
                    <tr>
                      <td><li> 7. Fotocopy Keputusan Hakim/Pengadilan</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9309' id='file_lamp9309'>
                        <input type='button' id='btn_9309' name='btn_9309' value='Upload'  onclick=\"upload('file_lamp9309',930,  9309);\">
                        <span id='uploaded_image_9309'>" . $berkas_lamp930 . "</span>
                       </td>
                    </tr>

                  
                    <tr>
                      <td><li> 8. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9109' id='file_lamp9109'>
                        <input type='button' id='btn_9109' name='btn_9109' value='Upload'  onclick=\"upload('file_lamp9109',910,  9109);\">
                        <span id='uploaded_image_9109'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9139' id='file_lamp9139'>
                        <input type='button' id='btn_9139' name='btn_9139' value='Upload'  onclick=\"upload('file_lamp9139',913,  9139);\">
                        <span id='uploaded_image_9139'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>

                    </table>
              </ul>                                                 
                        </td>                                                     
                      </tr>                                                       
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan10\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL PENGGABUNGAN USAHA -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90110' id='file_lamp90110'>
                        <input type='button' id='btn_90110' name='btn_90110' value='Upload'  onclick=\"upload('file_lamp90110',901,  90110);\">
                        <span id='uploaded_image_90110'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90210' id='file_lamp90210'>
                        <input type='button' id='btn_90210' name='btn_90210' value='Upload'  onclick=\"upload('file_lamp90210',902,  90210);\">
                        <span id='uploaded_image_90210'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90310' id='file_lamp90310'>
                        <input type='button' id='btn_90310' name='btn_90310' value='Upload'  onclick=\"upload('file_lamp90310',903,  90310);\">
                        <span id='uploaded_image_90310'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90410' id='file_lamp90410'>
                        <input type='button' id='btn_90410' name='btn_90410' value='Upload'  onclick=\"upload('file_lamp90410',904,  90410);\">
                        <span id='uploaded_image_90410'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90510' id='file_lamp90510'>
                        <input type='button' id='btn_90510' name='btn_90510' value='Upload'  onclick=\"upload('file_lamp90510',905,  90510);\">
                        <span id='uploaded_image_90510'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90610' id='file_lamp90610'>
                        <input type='button' id='btn_90610' name='btn_90610' value='Upload'  onclick=\"upload('file_lamp90610',906,  90610);\">
                        <span id='uploaded_image_90610'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 7. SCAN Keputusan Hakim/Pengadilan</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93110' id='file_lamp93110'>
                        <input type='button' id='btn_93110' name='btn_93110' value='Upload'  onclick=\"upload('file_lamp93110',931,  93110);\">
                        <span id='uploaded_image_93110'>" . $berkas_lamp931 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 8. Scan Akta Pendirian Perusahaan yang terbaru</li></td>
                       <td>

                        <input onchange='check_extension(this.value)' type='file' name='file_lamp92610' id='file_lamp92610'>
                        <input type='button' id='btn_92610' name='btn_92610' value='Upload'  onclick=\"upload('file_lamp92610',926,  92610);\">
                        <span id='uploaded_image_92610'>" . $berkas_lamp926 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. SCAN NPWP Perusahaan</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp92710' id='file_lamp92710'>
                        <input type='button' id='btn_92610' name='btn_92710' value='Upload'  onclick=\"upload('file_lamp92710',927,  92710);\">
                        <span id='uploaded_image_92710'>" . $berkas_lamp927 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 10. SCAN Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</li></td>
                      <td>

                        <input onchange='check_extension(this.value)' type='file' name='file_lamp92780' id='file_lamp92810'>
                        <input type='button' id='btn_92810' name='btn_92810' value='Upload'  onclick=\"upload('file_lamp92810',928,  92780);\">
                        <span id='uploaded_image_92780'>" . $berkas_lamp928 . "</span>

                     </td>

                    <tr>
                      <td><li> 11. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>

                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90910' id='file_lamp90910'>
                        <input type='button' id='btn_90910' name='btn_90910' value='Upload'  onclick=\"upload('file_lamp90910',909,  90910);\">
                        <span id='uploaded_image_90910'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93710' id='file_lamp93710'>
                        <input type='button' id='btn_93710' name='btn_93710' value='Upload'  onclick=\"upload('file_lamp93710',937,  93710);\">
                        <span id='uploaded_image_93710'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                  
                    <tr>
                      <td><li> 12. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91010' id='file_lamp91010'>
                        <input type='button' id='btn_91010' name='btn_91010' value='Upload'  onclick=\"upload('file_lamp91010',910,  91010);\">
                        <span id='uploaded_image_91010'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 13. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp913' id='file_lamp91310'>
                        <input type='button' id='btn_91310' name='btn_91310' value='Upload'  onclick=\"upload('file_lamp91310',913,  91310);\">
                        <span id='uploaded_image_91310'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                    </table>
              </ul>                                                 
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan11\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">

                            <!-- PELEBURAN USAHA -->
                <table border='0'>
                    
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90111' id='file_lamp90111'>
                        <input type='button' id='btn_90111' name='btn_90111' value='Upload'  onclick=\"upload('file_lamp90111',901,  90111);\">
                        <span id='uploaded_image_90111'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90211' id='file_lamp90211'>
                        <input type='button' id='btn_90211' name='btn_90211' value='Upload'  onclick=\"upload('file_lamp90211',902,  90211);\">
                        <span id='uploaded_image_90211'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90311' id='file_lamp90311'>
                        <input type='button' id='btn_90311' name='btn_90311' value='Upload'  onclick=\"upload('file_lamp90311',903,  90311);\">
                        <span id='uploaded_image_90311'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90411' id='file_lamp90411'>
                        <input type='button' id='btn_90411' name='btn_90411' value='Upload'  onclick=\"upload('file_lamp90411',904,  90411);\">
                        <span id='uploaded_image_90411'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90511' id='file_lamp90511'>
                        <input type='button' id='btn_90511' name='btn_90511' value='Upload'  onclick=\"upload('file_lamp90511',905,  90511);\">
                        <span id='uploaded_image_90511'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90611' id='file_lamp90611'>
                        <input type='button' id='btn_90611' name='btn_90611' value='Upload'  onclick=\"upload('file_lamp90611',906,  90611);\">
                        <span id='uploaded_image_90611'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 7. Scan Akta Pendirian Perusahaan yang terbaru</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp92611' id='file_lamp92611'>
                        <input type='button' id='btn_92611' name='btn_92611' value='Upload'  onclick=\"upload('file_lamp92611',926,  92611);\">
                        <span id='uploaded_image_92611'>" . $berkas_lamp926 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 8. SCAN NPWP Perusahaan</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp92711' id='file_lamp92711'>
                        <input type='button' id='btn_92711' name='btn_92711' value='Upload'  onclick=\"upload('file_lamp92711',927,  92711);\">
                        <span id='uploaded_image_92711'>" . $berkas_lamp927 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 9. SCAN Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp92811' id='file_lamp92811'>
                        <input type='button' id='btn_92811' name='btn_92811' value='Upload'  onclick=\"upload('file_lamp92811',928,  92811);\">
                        <span id='uploaded_image_92811'>" . $berkas_lamp928 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 10. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>
                    
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp9011' id='file_lamp9011'>
                        <input type='button' id='btn_9011' name='btn_9011' value='Upload'  onclick=\"upload('file_lamp9011',909,  9011);\">
                        <span id='uploaded_image_9011'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93711' id='file_lamp93711'><input type='button' id='btn_93711' name='btn_93711' value='Upload'  onclick=\"upload('file_lamp93711',937,  93711);\">
                        <span id='uploaded_image_93711'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                  
                    <tr>
                      <td><li> 11. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91011' id='file_lamp91011'>
                        <input type='button' id='btn_91011' name='btn_91011' value='Upload'  onclick=\"upload('file_lamp91011',910,  91011);\">
                        <span id='uploaded_image_91011'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 12. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91311' id='file_lamp91311'>
                        <input type='button' id='btn_91311' name='btn_91311' value='Upload'  onclick=\"upload('file_lamp91311',913,  91311);\">
                        <span id='uploaded_image_91311'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr></table>
              </ul>                                                
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan12\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- PEMEKARAN USAHA -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90112' id='file_lamp90112'>
                        <input type='button' id='btn_90112' name='btn_90112' value='Upload'  onclick=\"upload('file_lamp90112',901,  90112);\">
                        <span id='uploaded_image_90112'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>


                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90212' id='file_lamp90212'>
                        <input type='button' id='btn_90212' name='btn_90212' value='Upload'  onclick=\"upload('file_lamp90212',902,  90212);\">
                        <span id='uploaded_image_90212'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90312' id='file_lamp90312'>
                        <input type='button' id='btn_90312' name='btn_90312' value='Upload'  onclick=\"upload('file_lamp90312',903,  90312);\">
                        <span id='uploaded_image_90312'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90412' id='file_lamp90412'>
                        <input type='button' id='btn_90412' name='btn_90412' value='Upload'  onclick=\"upload('file_lamp90412',904,  90412);\">
                        <span id='uploaded_image_90412'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90512' id='file_lamp90512'>
                        <input type='button' id='btn_90512' name='btn_90512' value='Upload'  onclick=\"upload('file_lamp90512',905,  90512);\">
                        <span id='uploaded_image_90512'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90612' id='file_lamp90612'>
                        <input type='button' id='btn_90612' name='btn_90612' value='Upload'  onclick=\"upload('file_lamp90612',906,  90612);\">
                        <span id='uploaded_image_90612'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 7. Scan Akta Pendirian Perusahaan yang terbaru</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp92612' id='file_lamp92612'>
                        <input type='button' id='btn_92612' name='btn_92612' value='Upload'  onclick=\"upload('file_lamp92612',926,  92612);\">
                        <span id='uploaded_image_92612'>" . $berkas_lamp926 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 8. SCAN NPWP Perusahaan</li></td>
                       <td>

                        <input onchange='check_extension(this.value)' type='file' name='file_lamp92712' id='file_lamp92712'>
                        <input type='button' id='btn_92712' name='btn_92712' value='Upload'  onclick=\"upload('file_lamp92712',927,  92712);\">
                        <span id='uploaded_image_92712'>" . $berkas_lamp927 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 9. SCAN Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp928' id='file_lamp92812'>
                        <input type='button' id='btn_92812' name='btn_92812' value='Upload'  onclick=\"upload('file_lamp92812',928,  92812);\">
                        <span id='uploaded_image_92812'>" . $berkas_lamp928 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td><li> 10. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90912' id='file_lamp90912'>
                        <input type='button' id='btn_90912' name='btn_90912' value='Upload'  onclick=\"upload('file_lamp90912',909,  90912);\">
                        <span id='uploaded_image_90912'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93712' id='file_lamp93712'>
                        <input type='button' id='btn_93712' name='btn_93721' value='Upload'  onclick=\"upload('file_lamp93712',937,  93712);\">
                        <span id='uploaded_image_93712'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                  
                    <tr>
                      <td><li> 11. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91012' id='file_lamp91012'>
                        <input type='button' id='btn_91012' name='btn_91012' value='Upload'  onclick=\"upload('file_lamp91012',910,  91012);\">
                        <span id='uploaded_image_91012'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 12. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91312' id='file_lamp91312'>
                        <input type='button' id='btn_91312' name='btn_91312' value='Upload'  onclick=\"upload('file_lamp91312',913,  91312);\">
                        <span id='uploaded_image_91312'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr></table>
              </ul>                                                 
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan13\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL HADIAH -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90113' id='file_lamp90113'>
                        <input type='button' id='btn_90113' name='btn_90113' value='Upload'  onclick=\"upload('file_lamp90113',901,  90113);\">
                        <span id='uploaded_image_90113'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>


                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90213' id='file_lamp90213'>
                        <input type='button' id='btn_90213' name='btn_90213' value='Upload'  onclick=\"upload('file_lamp90213',902,  90213);\">
                        <span id='uploaded_image_90213'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90313' id='file_lamp90313'>
                        <input type='button' id='btn_90313' name='btn_90313' value='Upload'  onclick=\"upload('file_lamp90313',903,  90313);\">
                        <span id='uploaded_image_90313'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90413' id='file_lamp90413'>
                        <input type='button' id='btn_90413' name='btn_90413' value='Upload'  onclick=\"upload('file_lamp90413',904,  90413);\">
                        <span id='uploaded_image_90413'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90513' id='file_lamp90513'>
                        <input type='button' id='btn_90513' name='btn_90513' value='Upload'  onclick=\"upload('file_lamp90513',905,  90513);\">
                        <span id='uploaded_image_90513'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90613' id='file_lamp90613'>
                        <input type='button' id='btn_90613' name='btn_90613' value='Upload'  onclick=\"upload('file_lamp90613',906,  90613);\">
                        <span id='uploaded_image_90613'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 7. SCAN Surat Pernyataan Hadiah dari yang mengalihkan hak</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93513' id='file_lamp93513'>
                        <input type='button' id='btn_93513' name='btn_93513' value='Upload'  onclick=\"upload('file_lamp93513',935,  93513);\">
                        <span id='uploaded_image_93513'>" . $berkas_lamp935 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 8. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90913' id='file_lamp90913'>
                        <input type='button' id='btn_90913' name='btn_90913' value='Upload'  onclick=\"upload('file_lamp90913',909,  90913);\">
                        <span id='uploaded_image_90913'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93713' id='file_lamp93713'>
                        <input type='button' id='btn_93713' name='btn_93713' value='Upload'  onclick=\"upload('file_lamp93713',937,  93713);\">
                        <span id='uploaded_image_93713'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91013' id='file_lamp91013'>
                        <input type='button' id='btn_91013' name='btn_91013' value='Upload'  onclick=\"upload('file_lamp91013',910,  91013);\">
                        <span id='uploaded_image_91013'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91313' id='file_lamp91313'>
                        <input type='button' id='btn_91313' name='btn_91313' value='Upload'  onclick=\"upload('file_lamp91313',913,  91313);\">
                        <span id='uploaded_image_91313'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                </table>
              </ul>                                                 
                        </td>                                                     
                      </tr>                                                      
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan14\">
                <td width=\"100%\" colspan=\"2\" valign=\"top\">
                    <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL KPR -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90114' id='file_lamp90114'>
                        <input type='button' id='btn_90114' name='btn_90114' value='Upload'  onclick=\"upload('file_lamp90114',901,  90114);\">
                        <span id='uploaded_image_90114'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90214' id='file_lamp90214'>
                        <input type='button' id='btn_90214' name='btn_90214' value='Upload'  onclick=\"upload('file_lamp90214',902,  90214);\">
                        <span id='uploaded_image_90214'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90314' id='file_lamp90314'>
                        <input type='button' id='btn_90314' name='btn_90314' value='Upload'  onclick=\"upload('file_lamp90314',903,  90314);\">
                        <span id='uploaded_image_90314'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90414' id='file_lamp90414'>
                        <input type='button' id='btn_90414' name='btn_90414' value='Upload'  onclick=\"upload('file_lamp90414',904,  90414);\">
                        <span id='uploaded_image_90414'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90514' id='file_lamp90514'>
                        <input type='button' id='btn_90514' name='btn_90514' value='Upload'  onclick=\"upload('file_lamp90514',905,  90514);\">
                        <span id='uploaded_image_90514'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90614' id='file_lamp90614'>
                        <input type='button' id='btn_90614' name='btn_90614' value='Upload'  onclick=\"upload('file_lamp90614',906,  90614);\">
                        <span id='uploaded_image_90614'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    
                    
                    <tr>
                      <td><li> 7. SCAN Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93214' id='file_lamp93214'>
                        <input type='button' id='btn_93214' name='btn_93214' value='Upload'  onclick=\"upload('file_lamp93214',932,  93214);\">
                        <span id='uploaded_image_93214'>" . $berkas_lamp932 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 8. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90914' id='file_lamp90914'>
                        <input type='button' id='btn_90914' name='btn_90914' value='Upload'  onclick=\"upload('file_lamp90914',909,  90914);\">
                        <span id='uploaded_image_90914'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93714' id='file_lamp93714'>
                        <input type='button' id='btn_93714' name='btn_93714' value='Upload'  onclick=\"upload('file_lamp93714',937,  93714);\">
                        <span id='uploaded_image_93714'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 9. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91014' id='file_lamp91014'>
                        <input type='button' id='btn_91014' name='btn_91014' value='Upload'  onclick=\"upload('file_lamp91014',910,  91014);\">
                        <span id='uploaded_image_91014'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91314' id='file_lamp91314'>
                        <input type='button' id='btn_91314' name='btn_91314' value='Upload'  onclick=\"upload('file_lamp91314',913,  91314);\">
                        <span id='uploaded_image_91314'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                </table>
					</ul>                                                 
				</td>                                                     
			</tr>                                                        
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan15\">
                <td width=\"100%\" colspan=\"2\" valign=\"top\">
                    <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL KPR -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90115' id='file_lamp90115'>
                        <input type='button' id='btn_90115' name='btn_90115' value='Upload'  onclick=\"upload('file_lamp90115',901,  90115);\">
                        <span id='uploaded_image_90115'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90215' id='file_lamp90215'>
                        <input type='button' id='btn_90215' name='btn_90215' value='Upload'  onclick=\"upload('file_lamp90215',902,  90215);\">
                        <span id='uploaded_image_90215'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90315' id='file_lamp90315'>
                        <input type='button' id='btn_90315' name='btn_90315' value='Upload'  onclick=\"upload('file_lamp90315',903,  90315);\">
                        <span id='uploaded_image_90315'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90415' id='file_lamp90415'>
                        <input type='button' id='btn_90415' name='btn_90415' value='Upload'  onclick=\"upload('file_lamp90415',904,  90415);\">
                        <span id='uploaded_image_90415'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90515' id='file_lamp90515'>
                        <input type='button' id='btn_90515' name='btn_90515' value='Upload'  onclick=\"upload('file_lamp90515',905,  90515);\">
                        <span id='uploaded_image_90515'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90615' id='file_lamp90615'>
                        <input type='button' id='btn_90615' name='btn_90615' value='Upload'  onclick=\"upload('file_lamp90615',906,  90615);\">
                        <span id='uploaded_image_90615'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    
                    
                    <tr>
                      <td><li> 7. SCAN Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93215' id='file_lamp93215'>
                        <input type='button' id='btn_93215' name='btn_93215' value='Upload'  onclick=\"upload('file_lamp93215',932,  93215);\">
                        <span id='uploaded_image_93215'>" . $berkas_lamp532 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 8. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90915' id='file_lamp90915'>
                        <input type='button' id='btn_90915' name='btn_90915' value='Upload'  onclick=\"upload('file_lamp90915',909,  90915);\">
                        <span id='uploaded_image_90915'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93715' id='file_lamp93715'>
                        <input type='button' id='btn_93715' name='btn_93715' value='Upload'  onclick=\"upload('file_lamp93715',937,  93715);\">
                        <span id='uploaded_image_93715'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 9. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91015' id='file_lamp91015'>
                        <input type='button' id='btn_91015' name='btn_91015' value='Upload'  onclick=\"upload('file_lamp91015',910,  91015);\">
                        <span id='uploaded_image_91015'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91315' id='file_lamp91315'>
                        <input type='button' id='btn_91315' name='btn_91315' value='Upload'  onclick=\"upload('file_lamp91315',913,  91315);\">
                        <span id='uploaded_image_91315'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                </table>
					</ul>                                                 
				</td>                                                     
			</tr>                                                     
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan21\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL Pemberian hak baru sebagai kelanjutan pelepasan hak -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90121' id='file_lamp90121'>
                        <input type='button' id='btn_90121' name='btn_90121' value='Upload'  onclick=\"upload('file_lamp90121',901,  90121);\">
                        <span id='uploaded_image_90121'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90221' id='file_lamp90221'>
                        <input type='button' id='btn_90221' name='btn_90221' value='Upload'  onclick=\"upload('file_lamp90221',902,  90221);\">
                        <span id='uploaded_image_90221'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90321' id='file_lamp90321'>
                        <input type='button' id='btn_90321' name='btn_90321' value='Upload'  onclick=\"upload('file_lamp90321',903,  90321);\">
                        <span id='uploaded_image_90321'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90421' id='file_lamp90421'>
                        <input type='button' id='btn_90421' name='btn_90421' value='Upload'  onclick=\"upload('file_lamp90421',904,  90421);\">
                        <span id='uploaded_image_90421'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90521' id='file_lamp90521'>
                        <input type='button' id='btn_90521' name='btn_90521' value='Upload'  onclick=\"upload('file_lamp90521',905,  90521);\">
                        <span id='uploaded_image_90521'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                     
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90621' id='file_lamp90621'>
                        <input type='button' id='btn_90621' name='btn_90621' value='Upload'  onclick=\"upload('file_lamp90621',906,  90621);\">
                        <span id='uploaded_image_90621'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>

                    <tr>    
                      <td><li>7. Surat Pelepasan Hak Atas Tanah dari BPN </li></td>
                      <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93321' id='file_lamp93321'>
                        <input type='button' id='btn_93321' name='btn_93321' value='Upload'  onclick=\"upload('file_lamp93321',933, 93321);\">
                        <span id='uploaded_image_93321'>" . $berkas_lamp933 . "</span></td>
                    </tr>

                    <tr>
                      <td><li> 8. Foto objek (minimal 2 arah) <span style=\"color:red\">*Wajib</span></li></td>
                       <td></td>
                    </tr>

                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90921' id='file_lamp90921'>
                        <input type='button' id='btn_90921' name='btn_90921' value='Upload'  onclick=\"upload('file_lamp90921',909,  90921);\">
                        <span id='uploaded_image_90921'>" . $berkas_lamp909 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90921' id='file_lamp93721'>
                        <input type='button' id='btn_93721' name='btn_93721' value='Upload'  onclick=\"upload('file_lamp93721',937,  93721);\">
                        <span id='uploaded_image_93721'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 9. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp910'21 id='file_lamp91021'>
                        <input type='button' id='btn_91021' name='btn_91021' value='Upload'  onclick=\"upload('file_lamp91021',910,  91021);\">
                        <span id='uploaded_image_91021'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91321' id='file_lamp91321'>
                        <input type='button' id='btn_91321' name='btn_91321' value='Upload'  onclick=\"upload('file_lamp91321',913,  91321);\">
                        <span id='uploaded_image_91321'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                    </table>
              </ul>                                                 
                        </td>                                                     
                      </tr>                                                       
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan22\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                            <!-- MODUL Pemberian hak baru diluar pelepasan hak -->
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. SCAN NPWP/ Surat Pernyataan tidak memiliki NPWP <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90122' id='file_lamp90122'>
                        <input type='button' id='btn_90122' name='btn_90122' value='Upload'  onclick=\"upload('file_lamp90122',901,  90122);\">
                        <span id='uploaded_image_90122'>" . $berkas_lamp901 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SCAN KTP WP yang masih berlaku/Keterangan domisil dilegalisir  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90222' id='file_lamp90222'>
                        <input type='button' id='btn_90222' name='btn_90222' value='Upload'  onclick=\"upload('file_lamp90222',902,  90222);\">
                        <span id='uploaded_image_90222'>" . $berkas_lamp902 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. SCAN Surat kuasa dari WP yang bermaterai dan SCAN KTP penerima kuasa yang masih berlaku dalam hal dikuasakan dilegalisir</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90322' id='file_lamp90322'>
                        <input type='button' id='btn_90322' name='btn_90322' value='Upload'  onclick=\"upload('file_lamp90322',903,  90322);\">
                        <span id='uploaded_image_90322'>" . $berkas_lamp903 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. SCAN SPPT tahun berjalan / SKNJOP  <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90422' id='file_lamp90422'>
                        <input type='button' id='btn_90422' name='btn_90422' value='Upload'  onclick=\"upload('file_lamp90422',904,  90422);\">
                        <span id='uploaded_image_90422'>" . $berkas_lamp904 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. SCAN Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90522' id='file_lamp90522'>
                        <input type='button' id='btn_90522' name='btn_90522' value='Upload'  onclick=\"upload('file_lamp90522',905,  90522);\">
                        <span id='uploaded_image_90522'>" . $berkas_lamp905 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 6. SCAN Seporadik / sertifikat dilegalisir <span style=\"color:red\">*Wajib</span></li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp90622' id='file_lamp90622'>
                        <input type='button' id='btn_90622' name='btn_90622' value='Upload'  onclick=\"upload('file_lamp90622',906,  90622);\">
                        <span id='uploaded_image_90622'>" . $berkas_lamp906 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 7. SCAN Surat Pelepasan Hak Atas Tanah atau sejenisnya dari BPN</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91522' id='file_lamp91522'>
                        <input type='button' id='btn_91522' name='btn_90622' value='Upload'  onclick=\"upload('file_lamp91522',915,  91522);\">
                        <span id='uploaded_image_91522'>" . $berkas_lamp915 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 8. Foto denah koordinat objek (google maps)</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp922' id='file_lamp922'>
                        <input type='button' id='btn_922' name='btn_922' value='Upload'  onclick=\"upload('file_lamp922',922,  922);\">
                        <span id='uploaded_image_922'>" . $berkas_lamp922 . "</span>
                       </td>
                    </tr>
                    
                    <tr>
                      <td><li> 9. Foto lokasi objek</li></td>
                       <td></td>
                    </tr>
                    <tr>
                      <td>- Foto 1</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp936' id='file_lamp936'>
                        <input type='button' id='btn_936' name='btn_936' value='Upload'  onclick=\"upload('file_lamp936',936,  936);\">
                        <span id='uploaded_image_936'>" . $berkas_lamp936 . "</span>
                       </td>
                    </tr>
                    <tr>
                      <td>- Foto 2</td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp93722' id='file_lamp93722'>
                        <input type='button' id='btn_93722' name='btn_97622' value='Upload'  onclick=\"upload('file_lamp93722',937,  93722);\">
                        <span id='uploaded_image_93722'>" . $berkas_lamp937 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 10. Scan Akta</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91022' id='file_lamp91022'>
                        <input type='button' id='btn_91022' name='btn_91022' value='Upload'  onclick=\"upload('file_lamp91022',910,  91022);\">
                        <span id='uploaded_image_91022'>" . $berkas_lamp910 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 11. SCAN Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp913' id='file_lamp91322'>
                        <input type='button' id='btn_91322' name='btn_91322' value='Upload'  onclick=\"upload('file_lamp91322',913,  91322);\">
                        <span id='uploaded_image_91322'>" . $berkas_lamp913 . "</span>
                       </td>
                     </tr>
                   </table>
              </ul>                                                 
                        </td>                                                     
                      </tr>                 
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan30\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                <table border='0'><tr>
                    <td width='50%'>
                      <li>1. Formulir penyampaian SSPD BPHTB</li></td><td width='50%'>                                                                            <input onchange='check_extension(this.value)' type='file' name='file_lamp147' id='file_lamp147'><input type='button' id='btn_147' name='btn_147' value='Upload'  onclick=\"upload('file_lamp147',1,147);\"><span id='uploaded_image_147'>" . $berkas_lamp1 . "</span></td>
                    </tr>   
                    <tr>    
                    <td>    
                      <li>2. SSPD-BPHTB </li></td><td>                                                                                      <input onchange='check_extension(this.value)' type='file' name='file_lamp148' id='file_lamp148'><input type='button' id='btn_148' name='btn_148' value='Upload'  onclick=\"upload('file_lamp148',2,  148);\"><span   id='uploaded_image_148'>" . $berkas_lamp2 . "</span></td>
                    </tr>   
                    <tr>    
                    <td>    
                      <li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili</li></td><td>                                                                           <input onchange='check_extension(this.value)' type='file' name='file_lamp149' id='file_lamp149'><input type='button' id='btn_149' name='btn_149' value='Upload'  onclick=\"upload('file_lamp149',3,149);\"><span   id='uploaded_image_149'>" . $berkas_lamp3 . "</span></td>
                    </tr>   
                    <tr>    
                    <td>    
                      <li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</li></td><td>                                                              <input onchange='check_extension(this.value)' type='file' name='file_lamp150' id='file_lamp150'><input type='button' id='btn_150' name='btn_150' value='Upload'  onclick=\"upload('file_lamp150',4,  150);\"><span   id='uploaded_image_150'>" . $berkas_lamp4 . "</span></td>
                    </tr>   
                    <tr>    
                    <td>    
                      <li>5. Fotocopy SPPT yang sedang berjalan</li></td><td>                                                                                     <input onchange='check_extension(this.value)' type='file' name='file_lamp151' id='file_lamp151'><input type='button' id='btn_151' name='btn_151' value='Upload'  onclick=\"upload('file_lamp151',5,  151);\"><span   id='uploaded_image_151'>" . $berkas_lamp5 . "</span></td>
                    </tr>   
                    <tr>    
                    <td>    
                      <li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009</li></td><td>                                                                            <input onchange='check_extension(this.value)' type='file' name='file_lamp152' id='file_lamp152'><input type='button' id='btn_152' name='btn_152' value='Upload'  onclick=\"upload('file_lamp152',6,  152);\"><span   id='uploaded_image_152'>" . $berkas_lamp6 . "</span></td>
                    </tr>   
                    <tr>    
                    <td>    
                      <li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</li></td><td>                                                             <input onchange='check_extension(this.value)' type='file' name='file_lamp153' id='file_lamp153'><input type='button' id='btn_153' name='btn_153' value='Upload'  onclick=\"upload('file_lamp153',7,  153);\"><span   id='uploaded_image_153'>" . $berkas_lamp7 . "</span></td>
                    </tr>   
                    <tr>    
                    <td>    
                      <li>8. Daftar harga/Pricelist dalam hal pembelian dan pengembangan</li></td><td>                                                                        <input onchange='check_extension(this.value)' type='file' name='file_lamp154' id='file_lamp154'><input type='button' id='btn_154' name='btn_154' value='Upload'  onclick=\"upload('file_lamp154',8,  154);\"><span   id='uploaded_image_154'>" . $berkas_lamp8 . "</span></td>
                    </tr>   
                    <tr>    
                    <td>    
                      <li>9. Fotocopy Bukti transaksi/rincian pembayaran</li></td><td>                                                                                <input onchange='check_extension(this.value)' type='file' name='file_lamp155' id='file_lamp155'><input type='button' id='btn_155' name='btn_155' value='Upload'  onclick=\"upload('file_lamp155',9,  155);\"><span   id='uploaded_image_155'>" . $berkas_lamp9 . "</span></td>
                  </tr>
                </table>
              </ul>
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan31\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                             <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                <table border='0'><tr>
                  <td width='50%'>
                    <li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp156' id='file_lamp156' >                                        <input type='button' id='btn_156' name='btn_156' value='Upload'  onclick=\"upload('file_lamp156',1, 156);\"><span  id='uploaded_image_156'>" . $berkas_lamp1 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp157' id='file_lamp157' >                                                                                                     <input type='button' id='btn_157' name='btn_157' value='Upload'  onclick=\"upload('file_lamp157',2, 157);\"><span  id='uploaded_image_157'>" . $berkas_lamp2 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp158' id='file_lamp158' >                                                         <input type='button' id='btn_158' name='btn_158' value='Upload'  onclick=\"upload('file_lamp158',3, 158);\"><span  id='uploaded_image_158'>" . $berkas_lamp3 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp159' id='file_lamp159' >    <input type='button' id='btn_159' name='btn_159' value='Upload'  onclick=\"upload('file_lamp159',4, 159);\"><span  id='uploaded_image_159'>" . $berkas_lamp4 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp160' id='file_lamp160' >                                                                             <input type='button' id='btn_160' name='btn_160' value='Upload'  onclick=\"upload('file_lamp160',5, 160);\"><span  id='uploaded_image_160'>" . $berkas_lamp5 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp161' id='file_lamp161' >                                                            <input type='button' id='btn_161' name='btn_161' value='Upload'  onclick=\"upload('file_lamp161',6, 161);\"><span  id='uploaded_image_161'>" . $berkas_lamp6 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp162' id='file_lamp162' >                             <input type='button' id='btn_162' name='btn_162' value='Upload'  onclick=\"upload('file_lamp162',7, 162);\"><span  id='uploaded_image_162'>" . $berkas_lamp7 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>8. Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp163' id='file_lamp163' >                                                           <input type='button' id='btn_163' name='btn_163' value='Upload'  onclick=\"upload('file_lamp163',13, 163);\"><span  id='uploaded_image_163'>" . $berkas_lamp13 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>9. Fotocopy Surat/Keterangan Kematian </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp164' id='file_lamp164' >                                                                           <input type='button' id='btn_164' name='btn_164' value='Upload'  onclick=\"upload('file_lamp164',14, 164);\"><span  id='uploaded_image_164'>" . $berkas_lamp14 . "</span></td>
                  </tr>
                  <tr>
                  <td>  
                    <li>10. Fotocopy Surat Pernyataan Waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp165' id='file_lamp165' >                                                                              <input type='button' id='btn_165' name='btn_165' value='Upload'  onclick=\"upload('file_lamp165',15, 165);\"><span  id='uploaded_image_165'>" . $berkas_lamp15 . "</span></td>
                  </tr>
                  <tr>
                  <td>  
                    <li>11. Fotocopy Surat Kuasa Waris dalam hal Dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp166' id='file_lamp166' >                                                              <input type='button' id='btn_166' name='btn_166' value='Upload'  onclick=\"upload('file_lamp166',16, 166);\"><span  id='uploaded_image_166'>" . $berkas_lamp16 . "</span></td>
                </tr></table>
              </ul>
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan23\">
                            <!-- JENIS PEROLEHAN PTSL -->

                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                <table border='0'>
                    <tr>
                      <td width=\"50%\"><li> 1. Bukti cek BPHTB terhutang</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp938' id='file_lamp938'>
                        <input type='button' id='btn_938' name='btn_938' value='Upload'  onclick=\"upload('file_lamp938',938,  938);\">
                        <span id='uploaded_image_938'>" . $berkas_lamp938 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 2. SPPT PBB</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp939' id='file_lamp939'>
                        <input type='button' id='btn_939' name='btn_939' value='Upload'  onclick=\"upload('file_lamp939',939,  939);\">
                        <span id='uploaded_image_939'>" . $berkas_lamp939 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 3. KTP Pembeli</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp940' id='file_lamp940'>
                        <input type='button' id='btn_940' name='btn_940' value='Upload'  onclick=\"upload('file_lamp940',940,  940);\">
                        <span id='uploaded_image_940'>" . $berkas_lamp940 . "</span>
                       </td>
                    </tr>

                    <tr>
                      <td><li> 4. Sertifikat program PTSL</li></td>
                       <td>
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp941' id='file_lamp941'>
                        <input type='button' id='btn_941' name='btn_941' value='Upload'  onclick=\"upload('file_lamp941',941,  941);\">
                        <span id='uploaded_image_941'>" . $berkas_lamp941 . "</span>
                       </td>
                    </tr> 

                    <tr>
                      <td><li> 5. Dokumen Pendukung Lainnya</li></td>
                      <td width='50%'> 
                        <input onchange='check_extension(this.value)' type='file' name='file_lamp91323' id='file_lamp91323'>
                        <input type='button' id='btn_91323' name='btn_91323' value='Upload'  onclick=\"upload('file_lamp91323',913,  91323);\">
                        <span id='uploaded_image_91323'>" . $berkas_lamp913 . "</span>
                       </td>
                    </tr>
                </table>
                            </ul>
                        </td>
                      </tr>
            <tr class=\"jnsPerolehan\" id=\"jnsPerolehan33\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
                <table border='0'><tr>
                  <td width='50%'>  
                    <li>1. Formulir penyampaian SSPD BPHTB</li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp176' id='file_lamp176' >                                        <input type='button' id='btn_176' name='btn_176' value='Upload'  onclick=\"upload('file_lamp176',1, 176);\"><span  id='uploaded_image_176'>" . $berkas_lamp1 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp177' id='file_lamp177' >                                                                                                    <input type='button' id='btn_177' name='btn_177' value='Upload'  onclick=\"upload('file_lamp177',2, 177);\"><span  id='uploaded_image_177'>" . $berkas_lamp2 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp178' id='file_lamp178' >                                                        <input type='button' id='btn_178' name='btn_178' value='Upload'  onclick=\"upload('file_lamp178',3, 61);\"><span  id='uploaded_image_178'>" . $berkas_lamp3 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp179' id='file_lamp179' >   <input type='button' id='btn_179' name='btn_179' value='Upload'  onclick=\"upload('file_lamp179',4, 179);\"><span  id='uploaded_image_179'>" . $berkas_lamp4 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp180' id='file_lamp180' >                                                                            <input type='button' id='btn_180' name='btn_180' value='Upload'  onclick=\"upload('file_lamp180',5, 180);\"><span  id='uploaded_image_180'>" . $berkas_lamp5 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp181' id='file_lamp181' >                                                           <input type='button' id='btn_181' name='btn_181' value='Upload'  onclick=\"upload('file_lamp181',6, 181);\"><span  id='uploaded_image_181'>" . $berkas_lamp6 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp182' id='file_lamp182' >                            <input type='button' id='btn_182' name='btn_182' value='Upload'  onclick=\"upload('file_lamp182',7, 182);\"><span  id='uploaded_image_182'>" . $berkas_lamp7 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>8. Fotocopy KTP para ahli waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp183' id='file_lamp183' >                                                                                <input type='button' id='btn_183' name='btn_183' value='Upload'  onclick=\"upload('file_lamp183',20, 183);\"><span  id='uploaded_image_183'>" . $berkas_lamp20 . "</span></td>
                  </tr>   
                  <tr>    
                  <td>  
                    <li>9. Fotocopy Surat/keterangan Kematian </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp184' id='file_lamp184' >                                                                          <input type='button' id='btn_184' name='btn_184' value='Upload'  onclick=\"upload('file_lamp184',21, 184);\"><span  id='uploaded_image_184'>" . $berkas_lamp21 . "</span></td>
                  </tr>
                  <tr>
                  <td>  
                    <li>10. Fotocopy Surat Pernyataan waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp185' id='file_lamp185' >                                                                             <input type='button' id='btn_185' name='btn_185' value='Upload'  onclick=\"upload('file_lamp185',22, 185);\"><span  id='uploaded_image_185'>" . $berkas_lamp22 . "</span></td>
                  </tr>
                  
                  
                  <!-- <tr>
                  <td>  
                    <li> Fotocopy Surat Pernyataan waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp186' id='file_lamp186' >                                                                             <input type='button' id='btn_186' name='btn_186' value='Upload'  onclick=\"upload('file_lamp186',23, 186);\"><span  id='uploaded_image_186'>" . $berkas_lamp23 . "</span></td>
                </tr>-->
                </table>
              </ul>
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3>&nbsp;</h3></td></tr>                                                                          
                      <tr>
                        <td width=\"100%\" colspan=\"2\" valign=\"top\" align=\"center\">";
  $html .= (isset($_REQUEST['svcid'])) ? "<input type=\"button\" name=\"btn-save\" id=\"btn-simpan\" value=\"Update\" onclick='return checkfile();' />&nbsp;" : "<input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Simpan\" onclick='return checkfile();' />&nbsp;";

  $html .= "<input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "") . "\"' />
                </td>
                      </tr>
                </table>
                </td>
              </tr>
              <tr>
                <td colspan=\"2\">&nbsp;</td>
              </tr>                        
              <tr>
                <td colspan=\"2\" align=\"center\" valign=\"middle\"></td>
            </tr>
            <tr>
              <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
            </tr>
      </table>
    </form></div>";
  return $html;
}

function save($status)
{
  global $data, $DBLink, $uname;
  $lampiran = implode(";", $_POST['lampiran']);
  $jumSyarat = array(1 => 6, 2 => 6, 3 => 9, 4 => 9, 5 => 6, 6 => 6, 7 => 9, 8 => 9, 9 => 6, 10 => 6, 11 => 9, 12 => 9, 13 => 6, 14 => 6, 21 => 9, 22 => 9);
  $status = (count($_POST['lampiran']) == $jumSyarat[$_POST['jnsPerolehan']]) ? 1 : 0;
  $jp = @isset($_REQUEST['jnsPerolehan']) ? $_REQUEST['jnsPerolehan'] : "";
  for ($i = 1; $i <= 146; $i++) {
    if ($_REQUEST['file_lamp' . $i] != "") {

      echo $_REQUEST['file_lamp' . $i];
    }
  }
  //exit;
  $qry = sprintf("INSERT INTO cppmod_ssb_berkas (
            CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_LAMPIRAN,
            CPM_BERKAS_PETUGAS,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP, 
             CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,CPM_BERKAS_NAMA_WP,
            CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL,CPM_BERKAS_STATUS, 
            CPM_BERKAS_HARGA_TRAN, CPM_BERKAS_TELP_WP,CPM_BERKAS_TELP_OP
            ) VALUES ('%s','%s','%s',
                    '%s','%s','%s',                    
                    '%s','%s','%s',
                    '%s','%s',{$status},
                    '%s','%s','%s')", mysqli_escape_string($DBLink, $_POST['nop']), mysqli_escape_string($DBLink, $_POST['tglMasuk']), $lampiran, mysqli_escape_string($DBLink, $_SESSION['username']), mysqli_escape_string($DBLink, $_POST['alamatOp']), mysqli_escape_string($DBLink, $_POST['kelurahanOp']), mysqli_escape_string($DBLink, $_POST['kecamatanOp']), mysqli_escape_string($DBLink, $_POST['npwp']), mysqli_escape_string($DBLink, $_POST['namaWp']), mysqli_escape_string($DBLink, $_POST['jnsPerolehan']), mysqli_escape_string($DBLink, $_POST['noPel']), mysqli_escape_string($DBLink, $_POST['hargaTran']), mysqli_escape_string($DBLink, $_POST['telpWp']), mysqli_escape_string($DBLink, $_POST['telppnjl']));

  $res = mysqli_query($DBLink, $qry);
  if ($res) {
    echo 'Data berhasil disimpan...!';
    $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
    echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
              </script>";
  } else {
    $err = mysqli_error($DBLink);
    echo strpos(strtolower($err), "duplicate") === false ? $err : "Ada kesalahan! dokumen sudah pernah diinput! data sudah tersedia dan silakan periksa pada tabel.";
  }
}

function update($status)
{
  global $data, $DBLink, $uname;

  $lampiran = implode(";", $_POST['lampiran']);
  $jumSyarat = array(1 => 6, 2 => 6, 3 => 9, 4 => 9, 5 => 6, 6 => 6, 7 => 9, 8 => 9, 9 => 6, 10 => 6, 11 => 9, 12 => 9, 13 => 6, 14 => 6, 21 => 9, 22 => 9);
  $status = (count($_POST['lampiran']) == $jumSyarat[$_POST['jnsPerolehan']]) ? 1 : 0;
  $tgl_berkas = date('d-m-Y', strtotime( $_POST['tglMasuk']));
  $qry = sprintf("UPDATE cppmod_ssb_berkas SET        
            CPM_BERKAS_NOPEL = '" . mysqli_escape_string($DBLink, $_POST['noPel']) . "',
            CPM_BERKAS_LAMPIRAN ='{$lampiran}',
            CPM_BERKAS_PETUGAS = '" . mysqli_escape_string($DBLink, $_SESSION['username']) . "',
            CPM_BERKAS_TANGGAL = '{$tgl_berkas}',
                
            CPM_BERKAS_NOP = '" . mysqli_escape_string($DBLink, $_POST['nop']) . "',
            CPM_BERKAS_ALAMAT_OP = '" . mysqli_escape_string($DBLink, $_POST['alamatOp']) . "',
            CPM_BERKAS_KELURAHAN_OP = '" . mysqli_escape_string($DBLink, $_POST['kelurahanOp']) . "', 
            CPM_BERKAS_KECAMATAN_OP = '" . mysqli_escape_string($DBLink, $_POST['kecamatanOp']) . "',
            CPM_BERKAS_TELP_OP = '" . mysqli_escape_string($DBLink, $_POST['telppnjl']) . "',
            CPM_BERKAS_NPWP = '" . mysqli_escape_string($DBLink, $_POST['npwp']) . "',
            CPM_BERKAS_NAMA_WP = '" . mysqli_escape_string($DBLink, $_POST['namaWp']) . "',  
            
            CPM_BERKAS_HARGA_TRAN = '" . mysqli_escape_string($DBLink, $_POST['hargaTran']) . "',
            CPM_BERKAS_TELP_WP = '" . mysqli_escape_string($DBLink, $_POST['telpWp']) . "',

            CPM_BERKAS_STATUS = '{$status}'
            WHERE CPM_BERKAS_ID = '" . mysqli_escape_string($DBLink, $_POST['idssb']) . "'");

  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }

  if ($res) {
    echo 'Data berhasil diupdate...!';
    $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
    echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
              </script>";
  } else {
    echo mysqli_error($DBLink);
  }
}

$appConfig = $User->GetAppConfig($application);
$arConfig = $User->GetModuleConfig($m);
$save = $_REQUEST['process'];

if ($save == 'Simpan') {
  save();
} elseif ($save == 'Update') {
  update();
} else {
  $svcid = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";

  echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
  echo formPenerimaan($value);
}
