<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Registrasi' . DIRECTORY_SEPARATOR . 'configurePBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/user-central.php");

$DBLink = null;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
  $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
$path = "image/";
echo "<script language=\"javascript\" src=\"view/PBB/configure/mod-configure.js\" type=\"text/javascript\"></script>\n";

function getConfigValue($id, $key)
{
  global $DBLink;
  $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
  $res = mysqli_query($DBLink, $qry);
  if (mysqli_errno($DBLink)) {
    echo "<br>" . mysqli_error($DBLink);
  }
  while ($row = mysqli_fetch_assoc($res)) {
    return $row['CTR_AC_VALUE'];
  }
}

function getConfigModValue($id, $key)
{
  global $DBLink;
  $qry = "select * from central_module_config where CTR_CFG_MID = '" . $id . "' and CTR_CFG_MKEY = '$key'";
  $res = mysqli_query($DBLink, $qry);
  if (mysqli_errno($DBLink)) {
    echo "<br>" . mysqli_error($DBLink);
  }
  while ($row = mysqli_fetch_assoc($res)) {
    return $row['CTR_CFG_MVALUE'];
  }
}

function insertToTable($id, $key, $value)
{
  global $DBLink;
  $qry = "INSERT INTO central_app_config (CTR_AC_AID,CTR_AC_KEY,CTR_AC_VALUE) VALUES ('$id','$key','$value') ON DUPLICATE KEY UPDATE CTR_AC_AID='$id', CTR_AC_KEY = '$key', CTR_AC_VALUE ='$value'"; //echo $qry; exit;
  $res = mysqli_query($DBLink, $qry);
  if (mysqli_errno($DBLink)) {
    echo "<br>" . mysqli_error($DBLink);
  }
}

function insertToTableMod($id, $key, $value)
{
  global $DBLink;
  $qry = "INSERT INTO central_module_config (CTR_CFG_MID,CTR_CFG_MKEY,CTR_CFG_MVALUE) VALUES ('$id','$key','$value') ON DUPLICATE KEY UPDATE CTR_CFG_MID='$id', CTR_CFG_MKEY = '$key', CTR_CFG_MVALUE ='$value'"; //echo $qry; exit;
  $res = mysqli_query($DBLink, $qry);
  if (mysqli_errno($DBLink)) {
    echo "<br>" . mysqli_error($DBLink);
  }
}

function insertData($imgB)
{
  global $DBLink, $img;
  $a = getConfigValue($_REQUEST['a'], 'pbbApp');
  if (!$img) $img = $_FILES["logo_file"]["name"];
  insertToTable($a, 'cetak_date', $_REQUEST['cetak_date']);
  insertToTable($a, 'expired_days', $_REQUEST['expired_days']);
  insertToTable($a, 'jumlah_verifikasi', $_REQUEST['jumlah_verifikasi']);
  insertToTable($a, 'KODE_KOTA', $_REQUEST['KODE_KOTA']);
  insertToTable($a, 'KODE_PROVINSI', $_REQUEST['KODE_PROVINSI']);
  insertToTable($a, 'NAMA_KOTA', strtoupper($_REQUEST['NAMA_KOTA']));
  insertToTable($a, 'kota', ucfirst(strtolower($_REQUEST['NAMA_KOTA'])));
  insertToTable($a, 'NAMA_PROVINSI', $_REQUEST['NAMA_PROVINSI']);
  insertToTable($a, 'minimum_njoptkp', $_REQUEST['minimum_njoptkp']);
  insertToTable($a, 'minimum_sppt_pbb_terhutang', $_REQUEST['minimum_sppt_pbb_terhutang']);
  insertToTable($a, 'NAMA_PEJABAT_SK', $_REQUEST['NAMA_PEJABAT_SK']);
  insertToTable($a, 'PEJABAT_SK', $_REQUEST['PEJABAT_SK']);
  insertToTable($a, 'PEJABAT_SK_NIP', $_REQUEST['PEJABAT_SK_NIP']);
  insertToTable($a, 'NAMA_PEJABAT_SK2', $_REQUEST['NAMA_PEJABAT_SK2']);
  insertToTable($a, 'PEJABAT_SK2', $_REQUEST['PEJABAT_SK2']);
  insertToTable($a, 'NAMA_PEJABAT_SK2_JABATAN', $_REQUEST['NAMA_PEJABAT_SK2_JABATAN']);
  insertToTable($a, 'NAMA_PEJABAT_SK2_NIP', $_REQUEST['NAMA_PEJABAT_SK2_NIP']);
  insertToTable($a, 'KABID_JABATAN', $_REQUEST['KABID_JABATAN']);
  insertToTable($a, 'KABID_NAMA', $_REQUEST['KABID_NAMA']);
  insertToTable($a, 'KABID_NIP', $_REQUEST['KABID_NIP']);
  insertToTable($a, 'C_HEADER_DISPOSISI', $_REQUEST['C_HEADER_DISPOSISI']);
  insertToTable($a, 'C_ALAMAT_DISPOSISI', $_REQUEST['C_ALAMAT_DISPOSISI']);
  insertToTable($a, 'KANWIL', $_REQUEST['KANWIL']);
  insertToTable($a, 'KPP', $_REQUEST['KPP']);
  insertToTable($a, 'C_HEADER_SK', $_REQUEST['C_HEADER_SK']);
  insertToTable($a, 'C_JUDUL_SK', $_REQUEST['C_JUDUL_SK']);
  insertToTable($a, 'C_KABKOT', $_REQUEST['C_KABKOT']);
  insertToTable($a, 'C_HEADER_FORM_PENERIMAAN', $_REQUEST['C_HEADER_FORM_PENERIMAAN']);
  insertToTable($a, 'PRINTER_NAME', $_REQUEST['PRINTER_NAME']);
  insertToTable($a, 'susulan_start', $_REQUEST['susulan_start']);
  insertToTable($a, 'susulan_end', $_REQUEST['susulan_end']);
  insertToTable($a, 'tahun_tagihan', $_REQUEST['tahun_tagihan']);
  insertToTable($a, 'TEMPAT_PEMBAYARAN', $_REQUEST['TEMPAT_PEMBAYARAN']);
  insertToTable($a, 'NAMA_KOTA_PENGESAHAN', $_REQUEST['NAMA_KOTA_PENGESAHAN']);
  insertToTable($a, 'LABEL_KELURAHAN', $_REQUEST['LABEL_KELURAHAN']);
  insertToTable($a, 'FORMAT_NOMOR_LHP', $_REQUEST['FORMAT_NOMOR_LHP']);
  insertToTable($a, 'MAP_URL', $_REQUEST['MAP_URL']);
  insertToTable($a, 'NOMOR_LHP_OTOMATIS', isset($_REQUEST['NOMOR_LHP_OTOMATIS']) ? '1' : '0');
  insertToTable($a, 'NOMOR_SK_OTOMATIS', isset($_REQUEST['NOMOR_SK_OTOMATIS']) ? '1' : '0');
  insertToTable($a, 'NOMOR_SK_FORMAT', $_REQUEST['NOMOR_SK_FORMAT']);
  //if ($img[0]!='') insertToTable ($a,'FILE_LOGO',$img[0]);
  if ($img[0] != '') insertToTable($a, 'LOGO_CETAK_PDF', $img[0]);
  if ($img[1] != '') insertToTable($a, 'LOGO_CETAK_SK_WALKOT', $img[1]);
  if ($img[2] != '') insertToTable($a, 'LOGO_CETAK_SK', $img[2]);

  insertToTable($a, 'KASUBID_PBB', $_REQUEST['KASUBID_PBB']);
  insertToTable($a, 'NIP_KASUBID_PBB', $_REQUEST['NIP_KASUBID_PBB']);
  insertToTable($a, 'GOL_KASUBID_PBB', $_REQUEST['GOL_KASUBID_PBB']);
  insertToTable($a, 'KASUBID_PELAYANAN', $_REQUEST['KASUBID_PELAYANAN']);
  insertToTable($a, 'NIP_KASUBID_PELAYANAN', $_REQUEST['NIP_KASUBID_PELAYANAN']);
  insertToTable($a, 'GOL_KASUBID_PELAYANAN', $_REQUEST['GOL_KASUBID_PELAYANAN']);
  insertToTable($a, 'KASUBID_KTU', $_REQUEST['KASUBID_KTU']);
  insertToTable($a, 'NIP_KASUBID_KTU', $_REQUEST['NIP_KASUBID_KTU']);
  insertToTable($a, 'GOL_KASUBID_KTU', $_REQUEST['GOL_KASUBID_KTU']);
}

function insertDataMod()
{
  global $DBLink;
  $m = 'mPenagihan';

  insertToTableMod($m, 'KODE_BIDANG', $_REQUEST['KODE_BIDANG']);
  insertToTableMod($m, 'KODE_DISPENDA', $_REQUEST['KODE_DISPENDA']);
  insertToTableMod($m, 'LIMIT_TAHUN_PAJAK', $_REQUEST['LIMIT_TAHUN_PAJAK']);
  insertToTableMod($m, 'SP1', $_REQUEST['SP1']);
  insertToTableMod($m, 'SP2', $_REQUEST['SP2']);
  insertToTableMod($m, 'SP3', $_REQUEST['SP3']);

  $mLoket = 'mLkt';
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_OPBARU', $_REQUEST['FORMAT_NOMOR_BERKAS_OPBARU']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_PEMECAHAN', $_REQUEST['FORMAT_NOMOR_BERKAS_PEMECAHAN']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_PENGGABUNGAN', $_REQUEST['FORMAT_NOMOR_BERKAS_PENGGABUNGAN']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_MUTASI', $_REQUEST['FORMAT_NOMOR_BERKAS_MUTASI']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_PERUBAHAN', $_REQUEST['FORMAT_NOMOR_BERKAS_PERUBAHAN']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_PEMBATALAN', $_REQUEST['FORMAT_NOMOR_BERKAS_PEMBATALAN']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_SALINAN', $_REQUEST['FORMAT_NOMOR_BERKAS_SALINAN']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_PENGHAPUSAN', $_REQUEST['FORMAT_NOMOR_BERKAS_PENGHAPUSAN']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_PENGURANGAN', $_REQUEST['FORMAT_NOMOR_BERKAS_PENGURANGAN']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_KEBERATAN', $_REQUEST['FORMAT_NOMOR_BERKAS_KEBERATAN']);
  insertToTableMod($mLoket, 'FORMAT_NOMOR_BERKAS_SKNJOP', $_REQUEST['FORMAT_NOMOR_BERKAS_SKNJOP']);

  insertToTableMod($mLoket, 'TAMPILKAN_TGL_SELESAI', isset($_REQUEST['TAMPILKAN_TGL_SELESAI']) ? '1' : '0');
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_OPBARU', $_REQUEST['ESTIMASI_SELESAI_OPBARU']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_PEMECAHAN', $_REQUEST['ESTIMASI_SELESAI_PEMECAHAN']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_PENGGABUNGAN', $_REQUEST['ESTIMASI_SELESAI_PENGGABUNGAN']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_MUTASI', $_REQUEST['ESTIMASI_SELESAI_MUTASI']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_PERUBAHAN', $_REQUEST['ESTIMASI_SELESAI_PERUBAHAN']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_PEMBATALAN', $_REQUEST['ESTIMASI_SELESAI_PEMBATALAN']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_SALINAN', $_REQUEST['ESTIMASI_SELESAI_SALINAN']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_PENGHAPUSAN', $_REQUEST['ESTIMASI_SELESAI_PENGHAPUSAN']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_PENGURANGAN', $_REQUEST['ESTIMASI_SELESAI_PENGURANGAN']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_KEBERATAN', $_REQUEST['ESTIMASI_SELESAI_KEBERATAN']);
  insertToTableMod($mLoket, 'ESTIMASI_SELESAI_SKNJOP', $_REQUEST['ESTIMASI_SELESAI_SKNJOP']);
}

$a = getConfigValue($_REQUEST['a'], 'pbbApp');
$m = 'mPenagihan';
$mLoket = 'mLkt';
$img = @isset($_REQUEST['logo_file']["name"]) ? $_REQUEST['logo_file']["name"] : "";
print_r($img);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  foreach ($_FILES["logo_file"]["error"] as $key => $error) {
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    $temp = explode(".", $_FILES["logo_file"]["name"][$key]);
    $extension = end($temp);
    if ((($_FILES["logo_file"]["type"][$key] == "image/gif")
        || ($_FILES["logo_file"]["type"][$key] == "image/jpeg")
        || ($_FILES["logo_file"]["type"][$key] == "image/jpg")
        || ($_FILES["logo_file"]["type"][$key] == "image/pjpeg")
        || ($_FILES["logo_file"]["type"][$key] == "image/x-png")
        || ($_FILES["logo_file"]["type"][$key] == "image/png"))
      && ($_FILES["logo_file"]["size"][$key] < 200000)
      && in_array($extension, $allowedExts)
    ) {
      if (file_exists($path . $_FILES["logo_file"]["name"][$key])) {
        echo $_FILES["logo_file"]["name"][$key] . " File sudah ada. <br>";
      } else {
        if ($error == UPLOAD_ERR_OK) {
          $tmp_name = $_FILES["logo_file"]["tmp_name"][$key];
          $name = $_FILES["logo_file"]["name"][$key];
          move_uploaded_file($tmp_name, $path . $name);
          //echo "File $name berhasil diupload <br>";
        }
        insertData(true);
        insertDataMod();
      }
    } else {
      //echo "Invalid";
      insertData(false);
      insertDataMod();
    }
  }
}


?>

<div class="col-md-12">
  <div class="box box-default">
    <div class="box-header">
      <h3 class="box-title" style="border:unset">KONFIGURASI PBB</h3>
    </div>
    <div class="box-body">
      <form enctype="multipart/form-data" id="upload_form" action="" method="POST" onsubmit="return validateForm();">
        <!-- <tr>
    <td colspan="2" valign="top">Logo Kabupaten / Kota</td>
    <td width="3" align="center"  valign="top">:</td>
    <td width="300"><input type="file" id="logo_file1" name="logo_file[]"/><br /><i>
    Silahkan isi dengan file ber-extention<script>
document.write(extArray.join("  "));
</script></i>
<img width="200" height="auto" src="<?php echo  $path; ?><?php echo getConfigValue($a, 'FILE_LOGO') ?>"
    </td>
  </tr> -->
        <div class="row">
          <div class="col-md-4 mb15">
            <div class="form-group">
              <label for="">Logo untuk PDF:</label>
              <input type="file" id="logo_file2" name="logo_file[]" />
              <label>
                <i>
                  Silahkan isi dengan file ber-extention
                  <script>
                    document.write(extArray.join("  "));
                  </script>
                </i>
              </label>
            </div>
            <div class="form-group">
              <label for="">
                <img width="100" height="auto" src="<?php echo  $path; ?><?php echo getConfigValue($a, 'LOGO_CETAK_PDF') ?>">
              </label>
            </div>
          </div>
          <div class="col-md-4 mb15">
            <div class="form-group">
              <label for="">Logo untuk SK Walkot:</label>
              <input type="file" id="logo_file3" name="logo_file[]" />
              <label>
                <i>
                  Silahkan isi dengan file ber-extention
                  <script>
                    document.write(extArray.join("  "));
                  </script>
                </i>
              </label>
            </div>
            <div class="form-group">
              <label for="">
                <img width="100" height="auto" src="<?php echo  $path; ?><?php echo getConfigValue($a, 'LOGO_CETAK_SK_WALKOT') ?>">
              </label>
            </div>
          </div>
          <div class="col-md-4 mb15">
            <div class="form-group">
              <label>Logo untuk SK Kadin:</label>
              <input type="file" id="logo_file4" name="logo_file[]" />
              <label>
                <i>
                  Silahkan isi dengan file ber-extention
                  <script>
                    document.write(extArray.join("  "));
                  </script>
                </i>
              </label>
            </div>
            <div class="form-group">
              <label>
                <img width="100" height="auto" src="<?php echo  $path; ?><?php echo getConfigValue($a, 'LOGO_CETAK_SK') ?>">
              </label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group row">
              <label class="col-md-5 text-right">Tengat Waktu:</label>
              <input type="text" name="cetak_date" class="col-md-10 form-control" id="cetak_date" style="width:100px" value="<?php echo getConfigValue($a, 'cetak_date') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Expired Days:</label>
              <input type="text" name="expired_days" class="col-md-7 form-control" id="expired_days" style="width:70px" value="<?php echo getConfigValue($a, 'expired_days') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Jumlah Verifikasi:</label>
              <input type="text" name="jumlah_verifikasi" id="jumlah_verifikasi" class="col-md-7 form-control" style="width:40px" value="<?php echo getConfigValue($a, 'jumlah_verifikasi') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Kode Kabupaten/Kota (= 4 digit pertama NOP): </label>
              <input name="KODE_KOTA" type="text" class="col-md-7 form-control" id="KODE_KOTA" style="width:70px" value="<?php echo getConfigValue($a, 'KODE_KOTA') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Kode Propinsi (= 2 digit pertama NOP): </label>
              <input name="KODE_PROVINSI" class="col-md-7 form-control" type="text" id="KODE_PROVINSI" style="width:50px" value="<?php echo getConfigValue($a, 'KODE_PROVINSI') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Nama Kota: </label>
              <input name="NAMA_KOTA" type="text" class="col-md-7 form-control" id="NAMA_KOTA" style="width:300px" value="<?php echo getConfigValue($a, 'NAMA_KOTA') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Nama Kota Pada Bagian Pengesahan: </label>
              <input name="NAMA_KOTA_PENGESAHAN" class="col-md-7 form-control" type="text" id="NAMA_KOTA_PENGESAHAN" style="width:300px" value="<?php echo getConfigValue($a, 'NAMA_KOTA_PENGESAHAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Nama Propinsi: </label>
              <input name="NAMA_PROVINSI" type="text" class="col-md-7 form-control" id="NAMA_PROVINSI" style="width:300px" value="<?php echo getConfigValue($a, 'NAMA_PROVINSI') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Minimum NJOPTKP: </label>
              <input name="minimum_njoptkp" class="col-md-7 form-control" type="text" id="minimum_njoptkp" style="width:150px"  value="<?php echo getConfigValue($a, 'minimum_njoptkp') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Minimum SPPT PBB Terutang: </label>
              <input name="minimum_sppt_pbb_terhutang" type="text" id="minimum_sppt_pbb_terhutang" class="col-md-7 form-control" style="width:100px" value="<?php echo getConfigValue($a, 'minimum_sppt_pbb_terhutang') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Nama Walkot: </label>
              <input name="NAMA_PEJABAT_SK" type="text" id="NAMA_PEJABAT_SK" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'NAMA_PEJABAT_SK') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Jabatan Walkot: </label>
              <input name="PEJABAT_SK" type="text" id="PEJABAT_SK" class="col-md-7 form-control" style="width:400px" value="<?php echo getConfigValue($a, 'PEJABAT_SK') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">NIP Walkot: </label>
              <input name="PEJABAT_SK_NIP" type="text" id="PEJABAT_SK_NIP" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'PEJABAT_SK_NIP') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Nama Kadin: </label>
              <input name="NAMA_PEJABAT_SK2" type="text" id="NAMA_PEJABAT_SK2" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'NAMA_PEJABAT_SK2') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Jabatan Kadin: </label>
              <input name="PEJABAT_SK2" type="text" id="PEJABAT_SK2" class="col-md-7 form-control" style="width:400px" value="<?php echo getConfigValue($a, 'PEJABAT_SK2') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Jabatan Kadin 2: </label>
              <input name="NAMA_PEJABAT_SK2_JABATAN" type="text" id="NAMA_PEJABAT_SK2_JABATAN" class="col-md-7 form-control" style="width:400px" value="<?php echo getConfigValue($a, 'NAMA_PEJABAT_SK2_JABATAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">NIP Kadin: </label>
              <input name="NAMA_PEJABAT_SK2_NIP" type="text" id="NAMA_PEJABAT_SK2_NIP" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'NAMA_PEJABAT_SK2_NIP') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Jabatan KABID: </label>
              <input type="text" name="KABID_JABATAN" id="KABID_JABATAN" class="col-md-7 form-control" style="width:400px" value="<?php echo getConfigValue($a, 'KABID_JABATAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Nama KABID: </label>
              <input type="text" name="KABID_NAMA" id="KABID_NAMA" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'KABID_NAMA') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">NIP KABID: </label>
              <input type="text" name="KABID_NIP" id="KABID_NIP" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'KABID_NIP') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Kabupaten/Kota: </label>
              <input type="text" name="C_KABKOT" id="C_KABKOT" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'C_KABKOT') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Kantor Wilayah: </label>
              <input type="text" name="KANWIL" id="KANWIL" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'KANWIL') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">KPP: </label>
              <input type="text" name="KPP" id="KPP" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'KPP') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Header SK: </label>
              <div class="col-md-7" style="padding-left:0">
                <textarea name="C_HEADER_SK" class="form-control" id="C_HEADER_SK" rows="5"><?php echo getConfigValue($a, 'C_HEADER_SK') ?></textarea>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Judul SK: </label>
              <div class="col-md-7" style="padding-left:0">
                <textarea name="C_JUDUL_SK" class="form-control" id="C_JUDUL_SK" rows="5"><?php echo getConfigValue($a, 'C_JUDUL_SK') ?></textarea>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Header Disposisi: </label>
              <div class="col-md-7" style="padding-left:0">
                <textarea name="C_HEADER_DISPOSISI" class="form-control" id="C_HEADER_DISPOSISI" rows="5"><?php echo getConfigValue($a, 'C_HEADER_DISPOSISI') ?></textarea>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Alamat Disposisi:</label>
              <div class="col-md-7" style="padding-left:0">
                <textarea name="C_ALAMAT_DISPOSISI" class="form-control" id="C_ALAMAT_DISPOSISI" rows="5"><?php echo getConfigValue($a, 'C_ALAMAT_DISPOSISI') ?></textarea>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Header Form Penerimaan:</label>
              <div class="col-md-7" style="padding-left:0">
                <textarea name="C_HEADER_FORM_PENERIMAAN" class="form-control" id="C_HEADER_FORM_PENERIMAAN" rows="5"><?php echo getConfigValue($a, 'C_HEADER_FORM_PENERIMAAN') ?></textarea>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Nama Printer: </label>
              <input name="PRINTER_NAME" type="text" id="PRINTER_NAME" class="col-md-7 form-control" style="width:600px" value="<?php echo getConfigValue($a, 'PRINTER_NAME') ?>">
              <label class="col-md-5">&nbsp;</label>
              <span class="col-md-7"><i>Jika lebih dari satu pisahkan dengan titik koma (;)</i></span>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Periode Awal Penetapan Susulan: </label>
              <input name="susulan_start" type="text" id="susulan_start" class="col-md-7 form-control" style="width:50px" value="<?php echo getConfigValue($a, 'susulan_start') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Periode Akhir Penetapan Susulan: </label>
              <input name="susulan_end" type="text" id="susulan_end" class="col-md-7 form-control" style="width:50px" value="<?php echo getConfigValue($a, 'susulan_end') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Tahun Pajak: </label>
              <input name="tahun_tagihan" type="text" id="tahun_tagihan" class="col-md-7 form-control" style="width:70px" value="<?php echo getConfigValue($a, 'tahun_tagihan') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Tempat Pembayaran: </label>
              <input name="TEMPAT_PEMBAYARAN" type="text" id="TEMPAT_PEMBAYARAN" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'TEMPAT_PEMBAYARAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">URL Map: </label>
              <input name="MAP_URL" type="text" id="MAP_URL"class="col-md-7 form-control" style="width:600px" value="<?php echo getConfigValue($a, 'MAP_URL') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Label Kelurahan: </label>
              <input name="LABEL_KELURAHAN" type="text" id="LABEL_KELURAHAN" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'LABEL_KELURAHAN') ?>">
            </div>
            <div class="form-group row">
              <?php
              $penomoranLHPChecked = '';
              $penomoranLHPOtomatis = getConfigValue($a, 'NOMOR_LHP_OTOMATIS');
              if ($penomoranLHPOtomatis == '1') $penomoranLHPChecked = 'checked="true"';
              ?>
              <label class="col-md-5 text-right">Penomoran LHP otomatis melalui sistem ?</label>
              <input type="checkbox" name="NOMOR_LHP_OTOMATIS" type="text" id="NOMOR_LHP_OTOMATIS" <?php echo $penomoranLHPChecked; ?>> Ya</label>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Format Nomor LHP: </label>
              <input name="FORMAT_NOMOR_LHP" type="text" id="FORMAT_NOMOR_LHP" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'FORMAT_NOMOR_LHP') ?>">
              <i>Kosongkan bila penomoran LHP dilakukan secara manual</i>
            </div>
            <div class="form-group row">
              <?php
              $penomoranSKChecked = '';
              $penomoranSKOtomatis = getConfigValue($a, 'NOMOR_SK_OTOMATIS');
              if ($penomoranSKOtomatis == '1') $penomoranSKChecked = 'checked="true"';
              ?>
              <label class="col-md-5 text-right">Penomoran SK otomatis melalui sistem ?</label>
              <input type="checkbox" name="NOMOR_SK_OTOMATIS" type="text" id="NOMOR_SK_OTOMATIS" <?php echo $penomoranSKChecked; ?>> Ya</label>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Format Nomor SK: </label>
              <input name="NOMOR_SK_FORMAT" type="text" id="NOMOR_SK_FORMAT" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'NOMOR_SK_FORMAT') ?>"><i>Kosongkan bila penomoran SK dilakukan secara manual</i>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Konfigurasi Modul Penagihan</label>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Kode Bidang: </label>
              <input name="KODE_BIDANG" type="text" id="KODE_BIDANG" class="col-md-7 form-control" style="width:50px" value="<?php echo getConfigModValue($m, 'KODE_BIDANG') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Kode Dispenda: </label>
              <input name="KODE_DISPENDA" type="text" id="KODE_DISPENDA" class="col-md-7 form-control" style="width:50px" value="<?php echo getConfigModValue($m, 'KODE_DISPENDA') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Batas Tahun Pajak: </label>
              <input name="LIMIT_TAHUN_PAJAK" type="text" id="LIMIT_TAHUN_PAJAK" class="col-md-7 form-control" style="width:70px" value="<?php echo getConfigModValue($m, 'LIMIT_TAHUN_PAJAK') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">SP 1: </label>
              <input name="SP1" type="text" id="SP1" class="col-md-7 form-control" style="width:50px" value="<?php echo getConfigModValue($m, 'SP1') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">SP 2: </label>
              <input name="SP2" type="text" id="SP2" class="col-md-7 form-control" style="width:50px" value="<?php echo getConfigModValue($m, 'SP2') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">SP 3: </label>
              <input name="SP3" type="text" id="SP3" class="col-md-7 form-control" style="width:50px" value="<?php echo getConfigModValue($m, 'SP3') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right"><strong>Konfigurasi Nomor Penerimaan Pelayanan</strong></label>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">OP Baru: </label>
              <input name="FORMAT_NOMOR_BERKAS_OPBARU" type="text" id="FORMAT_NOMOR_BERKAS_OPBARU" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_OPBARU') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Pemecahan: </label>
              <input name="FORMAT_NOMOR_BERKAS_PEMECAHAN" type="text" id="FORMAT_NOMOR_BERKAS_PEMECAHAN" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_PEMECAHAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Penggabungan: </label>
              <input name="FORMAT_NOMOR_BERKAS_PENGGABUNGAN" type="text" id="FORMAT_NOMOR_BERKAS_PENGGABUNGAN" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_PENGGABUNGAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Mutasi: </label>
              <input name="FORMAT_NOMOR_BERKAS_MUTASI" type="text" id="FORMAT_NOMOR_BERKAS_MUTASI" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_MUTASI') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Perubahan Data: </label>
              <input name="FORMAT_NOMOR_BERKAS_PERUBAHAN" type="text" id="FORMAT_NOMOR_BERKAS_PERUBAHAN" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_PERUBAHAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Pembatalan: </label>
              <input name="FORMAT_NOMOR_BERKAS_PEMBATALAN" type="text" id="FORMAT_NOMOR_BERKAS_PEMBATALAN" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_PEMBATALAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Salinan: </label>
              <input name="FORMAT_NOMOR_BERKAS_SALINAN" type="text" id="FORMAT_NOMOR_BERKAS_SALINAN" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_SALINAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Penghapusan: </label>
              <input name="FORMAT_NOMOR_BERKAS_PENGHAPUSAN" type="text" id="FORMAT_NOMOR_BERKAS_PENGHAPUSAN" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_PENGHAPUSAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Pengurangan: </label>
              <input name="FORMAT_NOMOR_BERKAS_PENGURANGAN" type="text" id="FORMAT_NOMOR_BERKAS_PENGURANGAN" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_PENGURANGAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Keberatan: </label>
              <input name="FORMAT_NOMOR_BERKAS_KEBERATAN" type="text" id="FORMAT_NOMOR_BERKAS_KEBERATAN" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_KEBERATAN') ?>">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">SK NJOP: </label>
              <input name="FORMAT_NOMOR_BERKAS_SKNJOP" type="text" id="FORMAT_NOMOR_BERKAS_SKNJOP" class="col-md-7 form-control" style="width:300px" maxlength="25" value="<?php echo getConfigModValue($mLoket, 'FORMAT_NOMOR_BERKAS_SKNJOP') ?>">
            </div>
            <div class="form-group row">
              <?php
              $tglSelesaiChecked = '';
              $tampilkanTglSelesai = getConfigModValue($mLoket, 'TAMPILKAN_TGL_SELESAI');
              if ($tampilkanTglSelesai == '1') $tglSelesaiChecked = 'checked="true"';
              ?>
              <label class="col-md-5 text-right">Tampilkan tanggal selesai pada cetakan berkas penerimaan ? </label>
              <input type="checkbox" name="TAMPILKAN_TGL_SELESAI" type="text" id="TAMPILKAN_TGL_SELESAI" <?php echo $tglSelesaiChecked; ?>> Ya</label>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Estimasi lama pengerjaan</label>
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">OP Baru: </label>
              <input name="ESTIMASI_SELESAI_OPBARU" type="text" id="ESTIMASI_SELESAI_OPBARU" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_OPBARU') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Pemecahan: </label>
              <input name="ESTIMASI_SELESAI_PEMECAHAN" type="text" id="ESTIMASI_SELESAI_PEMECAHAN" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_PEMECAHAN') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Penggabungan: </label>
              <input name="ESTIMASI_SELESAI_PENGGABUNGAN" type="text" id="ESTIMASI_SELESAI_PENGGABUNGAN" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_PENGGABUNGAN') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Mutasi: </label>
              <input name="ESTIMASI_SELESAI_MUTASI" type="text" id="ESTIMASI_SELESAI_MUTASI" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_MUTASI') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Perubahan Data: </label>
              <input name="ESTIMASI_SELESAI_PERUBAHAN" type="text" id="ESTIMASI_SELESAI_PERUBAHAN" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_PERUBAHAN') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Pembatalan: </label>
              <input name="ESTIMASI_SELESAI_PEMBATALAN" type="text" id="ESTIMASI_SELESAI_PEMBATALAN" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_PEMBATALAN') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Salinan: </label>
              <input name="ESTIMASI_SELESAI_SALINAN" type="text" id="ESTIMASI_SELESAI_SALINAN" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_SALINAN') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Penghapusan: </label>
              <input name="ESTIMASI_SELESAI_PENGHAPUSAN" type="text" id="ESTIMASI_SELESAI_PENGHAPUSAN" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_PENGHAPUSAN') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Pengurangan: </label>
              <input name="ESTIMASI_SELESAI_PENGURANGAN" type="text" id="ESTIMASI_SELESAI_PENGURANGAN" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_PENGURANGAN') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">Keberatan: </label>
              <input name="ESTIMASI_SELESAI_KEBERATAN" type="text" id="ESTIMASI_SELESAI_KEBERATAN" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_KEBERATAN') ?>"> hari
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">SK NJOP: </label>
              <input name="ESTIMASI_SELESAI_SKNJOP" type="text" id="ESTIMASI_SELESAI_SKNJOP" class="col-md-7 form-control" style="width:40px" maxlength="2" value="<?php echo getConfigModValue($mLoket, 'ESTIMASI_SELESAI_SKNJOP') ?>"> hari
            </div>
            <hr>
            <div class="form-group row">
              <label class="col-md-5 text-right">KASUBID PBB</label>
              <input type="text" name="KASUBID_PBB" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'KASUBID_PBB') ?>" id="KASUBID_PBB">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">NIP KASUBID PBB</label>
              <input type="text" name="NIP_KASUBID_PBB" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'NIP_KASUBID_PBB') ?>" id="NIP_KASUBID_PBB">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">GOLONGAN KASUBID PBB</label>
              <input type="text" name="GOL_KASUBID_PBB" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'GOL_KASUBID_PBB') ?>" id="GOL_KASUBID_PBB">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">KASUBID PELAYANAN</label>
              <input type="text" name="KASUBID_PELAYANAN" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'KASUBID_PELAYANAN') ?>" id="KASUBID_PELAYANAN">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">NIP KASUBID PELAYANAN</label>
              <input type="text" name="NIP_KASUBID_PELAYANAN" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'NIP_KASUBID_PELAYANAN') ?>" id="NIP_KASUBID_PELAYANAN">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">GOLONGAN KASUBID PELAYANAN</label>
              <input type="text" name="GOL_KASUBID_PELAYANAN" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'GOL_KASUBID_PELAYANAN') ?>" id="GOL_KASUBID_PELAYANAN">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">KASUBID KTU</label>
              <input type="text" name="KASUBID_KTU" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'KASUBID_KTU') ?>" id="KASUBID_KTU">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">NIP KASUBID KTU</label>
              <input type="text" name="NIP_KASUBID_KTU" class="col-md-7 form-control" style="width:200px" value="<?php echo getConfigValue($a, 'NIP_KASUBID_KTU') ?>" id="NIP_KASUBID_KTU">
            </div>
            <div class="form-group row">
              <label class="col-md-5 text-right">GOLONGAN KASUBID KTU</label>
              <input type="text" name="GOL_KASUBID_KTU" class="col-md-7 form-control" style="width:300px" value="<?php echo getConfigValue($a, 'GOL_KASUBID_KTU') ?>" id="GOL_KASUBID_KTU">
            </div>
          </div>
        </div>
        <div class="box-tools text-right">
          <input type="submit" value="Submit" class="btn btn-primary btn-orange" />
          <input type="reset" name="Reset" id="button" class="btn btn-primary btn-blue" value="Reset">
        </div>
      </form>
    </div>
  </div>
</div>