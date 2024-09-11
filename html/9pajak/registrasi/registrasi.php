<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$DIR = "PATDA-V1";
$modul = "registrasi-wp";
require_once("../inc/payment/json.php");
require_once("../function/PATDA-V1/class-pajak-rwp.php");
require_once("../function/PATDA-V1/registrasi-wp/tes/class-wp.php");
$wp = new WajibPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$value = $wp->getDataWP();

$readonly_cty = false;


$kecamatan = $wp->get_list_kecamatan();

if ($wp->_i == 1 || $wp->_i == "") {
    $arr_pajak = explode(";", $value['CPM_JENIS_PAJAK']);
    $radio_jns_pajak[1] = in_array(1, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[2] = in_array(2, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[3] = in_array(3, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[4] = in_array(4, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[5] = in_array(5, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[6] = in_array(6, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[7] = in_array(7, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[8] = in_array(8, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[9] = in_array(9, $arr_pajak) ? "checked" : "";
} else {
    $arr_pajak = explode(";", $value['CPM_JENIS_PAJAK']);
    $radio_jns_pajak[1] = in_array(1, $arr_pajak) ? "checked" : "disabled";
    $radio_jns_pajak[2] = in_array(2, $arr_pajak) ? "checked" : "disabled";
    $radio_jns_pajak[3] = in_array(3, $arr_pajak) ? "checked" : "disabled";
    $radio_jns_pajak[4] = in_array(4, $arr_pajak) ? "checked" : "disabled";
    $radio_jns_pajak[5] = in_array(5, $arr_pajak) ? "checked" : "disabled";
    $radio_jns_pajak[6] = in_array(6, $arr_pajak) ? "checked" : "disabled";
    $radio_jns_pajak[7] = in_array(7, $arr_pajak) ? "checked" : "disabled";
    $radio_jns_pajak[8] = in_array(8, $arr_pajak) ? "checked" : "disabled";
    $radio_jns_pajak[9] = in_array(9, $arr_pajak) ? "checked" : "disabled";
}

$edit = ($wp->_id != "") ? true : false;
$readonly = ($edit) ? "readonly" : "";
?>

<link href="../inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="../inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">

<link href="../style/default/jquery/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css"/>
<link href="../inc/pnotify/pnotify.custom.min.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="../inc/js/jquery-ui.js"></script>
<script language="javascript" src="../inc/js/jquery.number.js"></script>
<script language="javascript" src="../inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="../inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="../function/<?php echo "{$DIR}/{$modul}"; ?>/func-wp.js"></script>

<?php
$attr_wp = "";
if ($value['CPM_JENIS_WP'] == '2') {
    $attr_wp = 'style="display: none;"';
}
$attr_wb = "";
if ($value['CPM_JENIS_WP'] == '1') {
    $attr_wb = 'style="display: none;"';
}

?>

<form class="cmxform" id="form-wp" method="post" enctype="multipart/form-data" action="../function/<?php echo "{$DIR}/{$modul}"; ?>/svc-wp.php?param=<?php echo base64_encode($json->encode(array("a" => $wp->_a, "m" => $wp->_m))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="WP[CPM_AUTHOR]" value="Register Sendiri">
    <input type="hidden" name="WP[CPM_STATUS]" value="3">
    <input type="hidden" name="WP[CTR_U_BLOCKED]" value="1">
    <table class="main" width="900">
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>REGISTRASI USER WAJIB PAJAK</b></td>
        </tr>
        <tr>
            <td>Id <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_USER]" id="CPM_USER" style="width: 200px;" value="<?php echo $value['CPM_USER'] ?>" readonly placeholder="by sistem"></td>
        </tr>
        <tr>
            <td width="200">Jenis WP <b class="isi">*</b></td>
            <td>:
                <label><input type="radio" name="WP[CPM_JENIS_WP]" class="CPM_JENIS_WP" value="1" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_WP'] == 1 ? 'checked' : 'checked') ?>> WP Pribadi</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label><input type="radio" name="WP[CPM_JENIS_WP]" class="CPM_JENIS_WP" value="2" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_WP'] == 2 ? 'checked' : '') ?>> WP Badan</label>
            </td>
        </tr>
        <tr>
            <td width="200">NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo $value['CPM_NPWPD'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "readonly" : "readonly"; ?> placeholder="by sistem"></td>
        </tr>
        <tr class="WB" <?php echo $attr_wb ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>
            <td width="200">Jabatan <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_JABATAN]" id="CPM_JABATAN" style="width: 200px;" value="<?php echo (isset($value['CPM_JABATAN']) ? $value['CPM_JABATAN'] : '') ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Jabatan" /></td>
        </tr>
        <tr class="WB" <?php echo $attr_wb ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>
            <td width="200">Surat Izin Tempat Usaha <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_SURAT_IZIN]" id="CPM_SURAT_IZIN" style="width: 200px;" value="<?php echo $value['CPM_SURAT_IZIN'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Surat Izin" /></td>
        </tr>
        <tr class="WB" <?php echo $attr_wb ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>
            <td width="200">No Surat Izin Tempat Usaha <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_NO_SURAT_IZIN]" id="CPM_NO_SURAT_IZIN" style="width: 200px;" value="<?php echo $value['CPM_NO_SURAT_IZIN'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="No Surat Izin" /></td>
        </tr>
        <tr class="WB" <?php echo $attr_wb ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>
            <td width="200">Tgl Surat Izin Tempat Usaha <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_TGL_SURAT_IZIN]" id="CPM_TGL_SURAT_IZIN" class="datepicker" size="10" value="<?php echo $value['CPM_TGL_SURAT_IZIN'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="dd/mm/yyyy" autocomplete="off" /></td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td>Kewarganegaraan <b class="isi">*</b></td>
            <td>:
                <label><input type="radio" name="WP[CPM_JENIS_KEWARGANEGARAAN]" class="CPM_JENIS_KEWARGANEGARAAN" value="WNI" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_KEWARGANEGARAAN'] == 'WNI' ? 'checked' : 'checked') ?>> WNI</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label><input type="radio" name="WP[CPM_JENIS_KEWARGANEGARAAN]" class="CPM_JENIS_KEWARGANEGARAAN" value="WNA" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_KEWARGANEGARAAN'] == 'WNA' ? 'checked' : '') ?>> WNA</label>
            </td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td>Tanda Bukti <b class="isi">*</b></td>
            <td>:
                <label><input type="radio" name="WP[CPM_JENIS_TANDABUKTI]" class="CPM_JENIS_TANDABUKTI" value="KTP" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_TANDABUKTI'] == 'KTP' ? 'checked' : 'checked') ?>> KTP</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label><input type="radio" name="WP[CPM_JENIS_TANDABUKTI]" class="CPM_JENIS_TANDABUKTI" value="SIM" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_TANDABUKTI'] == 'SIM' ? 'checked' : '') ?>> SIM</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label><input type="radio" name="WP[CPM_JENIS_TANDABUKTI]" class="CPM_JENIS_TANDABUKTI" value="PASPOR" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_TANDABUKTI'] == 'PASPOR' ? 'checked' : '') ?>> PASPOR</label>
            </td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td></td>
            <td>
                <span id="file_tandabukti"><?php
                                            if (empty($value['CPM_FILE_TANDABUKTI'])) {
                                                echo 'Pilih file Gambar atau PDF';
                                            } else {
                                                echo "&nbsp; &nbsp;<a href=\"image/tandabukti/{$value['CPM_FILE_TANDABUKTI']}\" title=\"{$value['CPM_FILE_TANDABUKTI']}\" target=\"_blank\">Lihat File {$value['CPM_JENIS_TANDABUKTI']}</a>";
                                            }
                                            ?></span>
                <input type="hidden" name="WP[CPM_FILE_TANDABUKTI]" value="<?php echo (isset($value['CPM_FILE_TANDABUKTI']) ? $value['CPM_FILE_TANDABUKTI'] : '') ?>" />
                <input type="file" name="FILE_TANDABUKTI" style="border:none" xaccept="image/*,application/pdf" />
            </td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td width="200">Nomor Tanda Bukti <b class="isi">*</b></td>
            <td>:
                <input type="text" name="WP[CPM_NO_TANDABUKTI]" id="CPM_NO_TANDABUKTI" style="width: 200px;" value="<?php echo $value['CPM_NO_TANDABUKTI'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nomor <?php echo $value['CPM_JENIS_TANDABUKTI'] == '' ? 'KTP' : $value['CPM_JENIS_TANDABUKTI'] ?>" />
            </td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td width="200">Tanggal Tanda Bukti <b class="isi">*</b></td>
            <td>:
                <input type="text" name="WP[CPM_TGL_TANDABUKTI]" id="CPM_TGL_TANDABUKTI" class="datepicker" size="10" value="<?php echo $value['CPM_TGL_TANDABUKTI'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="dd/mm/yyyy" autocomplete="off" />
            </td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td>Kartu Keluarga</td>
            <td>:
                <span id="file_kk"><?php
                                    if (empty($value['CPM_FILE_KK'])) {
                                        echo 'Pilih file Gambar atau PDF';
                                    } else {
                                        echo "<a href=\"image/tandabukti/{$value['CPM_FILE_KK']}\" title=\"{$value['CPM_FILE_KK']}\" target=\"_blank\">Lihat File KK</a>";
                                    }
                                    ?></span>
                <input type="hidden" name="WP[CPM_FILE_KK]" value="<?php echo isset($value['CPM_FILE_KK']) ? $value['CPM_FILE_KK'] : '' ?>" />
                <input type="file" name="FILE_KK" style="border:none" xaccept="image/*,application/pdf" />
            </td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td width="200">Nomor Kartu Keluarga <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_NO_KK]" id="CPM_NO_KK" style="width: 200px;" value="<?php echo $value['CPM_NO_KK'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nomor Kartu Keluarga" /></td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td width="200">Tanggal Kartu Keluarga <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_TGL_KK]" id="CPM_TGL_KK" size="10" class="datepicker" value="<?php echo $value['CPM_TGL_KK'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="dd/mm/yyyy" autocomplete="off" /></td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td valign="top">Pekerjaan/Usaha <b class="isi">*</b></td>
            <td>:
                <label><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="Pegawai Negeri" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_PEKERJAAN'] == 'Pegawai Negeri' ? 'checked' : 'checked') ?>> Pegawai Negeri</label><br>&nbsp;
                <label><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="Pegawai Swasta" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_PEKERJAAN'] == 'Pegawai Swasta' ? 'checked' : '') ?>> Pegawai Swasta</label><br>&nbsp;
                <label><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="ABRI" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_PEKERJAAN'] == 'ABRI' ? 'checked' : '') ?>> ABRI</label><br>&nbsp;
                <label><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="Pemilik Usaha" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_PEKERJAAN'] == 'Pemilik Usaha' ? 'checked' : '') ?>> Pemilik Usaha</label><br>&nbsp;
                <label><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="Lainnya" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php
                                                                                                                                                                                                                                                                $ajobs = array('Pegawai Negeri', 'Pegawai Swasta', 'ABRI', 'Pemilik Usaha');
                                                                                                                                                                                                                                                                echo ($value['CPM_JENIS_PEKERJAAN'] != '' && !in_array($value['CPM_JENIS_PEKERJAAN'], $ajobs)) ? 'checked' : '';
                                                                                                                                                                                                                                                                ?>> Lainnya</label>
                <input type="text" name="WP[CPM_JENIS_PEKERJAAN1" id="CPM_JENIS_PEKERJAAN1" <?php echo ($value['CPM_JENIS_PEKERJAAN'] == '' || in_array($value['CPM_JENIS_PEKERJAAN'], $ajobs)) ? 'style="display:none"' : '' ?> value="<?php echo !in_array($value['CPM_JENIS_PEKERJAAN'], $ajobs) ? $value['CPM_JENIS_PEKERJAAN'] : '' ?>">
            </td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td width="200">Nama Instansi Tempat Pekerjaan atau Usaha <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_NAMA_USAHA]" id="CPM_NAMA_USAHA" style="width: 200px;" value="<?php echo $value['CPM_NAMA_USAHA'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Instansi Tempat Pekerjaan" /></td>
        </tr>
        <tr class="WP" <?php echo $attr_wp ?>>
            <td width="200">Alamat Instansi atau Usaha <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_ALAMAT_USAHA]" id="CPM_ALAMAT_USAHA" style="width: 200px;" value="<?php echo $value['CPM_ALAMAT_USAHA'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Alamat Instansi atau Usaha" /></td>
        </tr>
        <tr>
            <td>Nomor NPWP</td>
            <td>: <input type="text" name="WP[CPM_NO_NPWP]" id="CPM_NO_NPWP" style="width: 200px;" value="<?php echo isset($value['CPM_NO_NPWP']) ? $value['CPM_NO_NPWP'] : ''; ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="No NPWP" /></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <span id="file_npwp"><?php
                                        if (empty($value['CPM_FILE_NPWP'])) {
                                            echo 'Pilih file Gambar atau PDF';
                                        } else {
                                            echo "&nbsp; &nbsp;<a href=\"image/tandabukti/{$value['CPM_FILE_NPWP']}\" title=\"{$value['CPM_FILE_NPWP']}\" target=\"_blank\">Lihat File NPWP</a>";
                                        }
                                        ?></span>
                <input type="hidden" name="WP[CPM_FILE_NPWP]" value="<?php echo isset($value['CPM_FILE_NPWP']) ? $value['CPM_FILE_NPWP'] : '' ?>" />
                <input type="file" name="FILE_NPWP" style="border:none" xaccept="image/*,application/pdf" />
            </td>
        </tr>
        <tr>
            <td class="WB" <?php echo $attr_wb ?> width="200" <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>Nama Pemilik <b class="isi">*</b></td>
            <td class="WP" <?php echo $attr_wp ?> width="200">Nama Lengkap <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $value['CPM_NAMA_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Lengkap" /></td>
        </tr>
        <tr>
            <td width="200">No Telepon <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_TELEPON_WP]" id="CPM_TELEPON_WP" style="width: 200px;" value="<?php echo $value['CPM_TELEPON_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="No Telepon" /></td>
        </tr>
        <tr>
            <td width="200">Asal WP <b class="isi">*</b></td>
            <td>:
                <label><input type="radio" name="WP[CPM_LUAR_DAERAH]" class="CPM_LUAR_DAERAH" value="0" <?php echo (isset($value['CPM_LUAR_DAERAH'])? ($value['CPM_LUAR_DAERAH'] == 0 ? 'checked' : '') : 'checked') ?> <?php echo (!empty($value['CPM_USER']) ? "onclick='javascript:return true'" : '') ?>>Dalam Daerah</label> <br>&nbsp;
                <label><input type="radio" name="WP[CPM_LUAR_DAERAH]" class="CPM_LUAR_DAERAH" value="1" <?php echo (isset($value['CPM_LUAR_DAERAH'])? ($value['CPM_LUAR_DAERAH'] == 1 ? 'checked' : '') : '') ?> <?php echo (!empty($value['CPM_USER']) ? "onclick='javascript:return true'" : '') ?>> Luar Daerah</label>
            </td>
        </tr>
        <tr>
            <td>Kecamatan WP <b class="isi">*</b></td>
            <td class="DK" <?php echo (!isset($value['CPM_LUAR_DAERAH']) || $value['CPM_LUAR_DAERAH'] == 0 ? '' : 'style="display: none;"') ?>>:
                <select name="WP[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" style="width: 200px;" data-kel="<?php echo $value['CPM_KECAMATAN_WP'] ?>">
                    <option></option>
                    <?php
                    if (count($kecamatan) > 0) {
                        foreach ($kecamatan as $kec) {
                            echo '<option value="' . $kec->CPM_KEC_ID . '" ' . ($value['CPM_KECAMATAN_WP'] == $kec->CPM_KECAMATAN ? 'selected' : '') . '>' . $kec->CPM_KECAMATAN . '</option>';
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="LK" <?php echo (!isset($value['CPM_LUAR_DAERAH']) || $value['CPM_LUAR_DAERAH'] == 1 ? '' : 'style="display: none;"') ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>:
                <input type="text" name="WP[CPM_KECAMATAN_WP1]" id="CPM_KECAMATAN_WP1" style="width: 200px;" value="<?php echo $value['CPM_KECAMATAN_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Kecamatan" />
            </td>
        </tr>
        <tr>
            <td>Kelurahan WP <b class="isi">*</b></td>
            <td class="DK" <?php echo (!isset($value['CPM_LUAR_DAERAH']) || $value['CPM_LUAR_DAERAH'] == 0 ? '' : 'style="display: none;"') ?>>:
                <select name="WP[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" style="width: 200px;">
                    <option value="<?php echo $value['CPM_KELURAHAN_WP'] ?>"><?php echo $value['CPM_KELURAHAN_WP'] ?></option>
                </select>
            </td>
            <td class="LK" <?php echo (!isset($value['CPM_LUAR_DAERAH']) || $value['CPM_LUAR_DAERAH'] == 1 ? '' : 'style="display: none;"') ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>:
                <input type="text" name="WP[CPM_KELURAHAN_WP1]" id="CPM_KELURAHAN_WP1" style="width: 200px;" value="<?php echo $value['CPM_KELURAHAN_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Kalurahan" />
            </td>
        </tr>
        <tr>
            <td>RT/RW WP <b class="isi">*</b></td>
            <td>:
                <input type="text" name="WP[CPM_RTRW_WP]" id="CPM_RTRW_WP" style="width: 200px;" value="<?php echo $value['CPM_RTRW_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="RT/RW" />
            </td>
        </tr>
        <tr>
            <td>Kota/Kabupaten WP <b class="isi">*</b></td>
            <td>:
                <input type="text" name="WP[CPM_KOTA_WP]" id="CPM_KOTA_WP" style="width: 200px;" value="<?php echo $value['CPM_KOTA_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Kota/Kabupaten" />
            </td>
        </tr>
        <tr>
            <td>Kode Pos WP <b class="isi">*</b></td>
            <td>:
                <input type="text" name="WP[CPM_KODEPOS_WP]" id="CPM_KODEPOS_WP" style="width: 200px;" value="<?php echo $value['CPM_KODEPOS_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Kode Pos" />
            </td>
        </tr>
        <!-- <tr>
            <td width="200">Kecamatan WP <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" style="width: 200px;" value="<?php //echo $value['CPM_KECAMATAN_WP'] 
                                                                                                                    ?>" <?php //echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; 
                                                                                                                        ?>></td>
        </tr>
        <tr>
            <td width="200">Kelurahan WP <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" style="width: 200px;" value="<?php //echo $value['CPM_KELURAHAN_WP'] 
                                                                                                                    ?>" <?php //echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; 
                                                                                                                        ?>></td>
        </tr> -->
        <tr valign="top">
            <td width="200">Alamat WP <b class="isi">*</b></td>
            <td>&nbsp; <textarea name="WP[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" style="width: 500px;" rows="3" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Alamat"><?php echo $value['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
        <tr>
            <td colspan="2">
                <p><b>Jenis Pajak</b> <b class="isi">*</b></p></br>
                <table width="100%" border="0" align="center" class="header">
                    <?php
                    foreach ($wp->arr_pajak as $a => $b) {
                        echo "<tr>
                                <td width=\"80\">
                                <label style='padding:2px'><input type=\"checkbox\" name=\"WP[CPM_JENIS_PAJAK][]\" id=\"CPM_JENIS_PAJAK\" value=\"{$a}\" {$radio_jns_pajak[$a]}> {$b}</label>
                                </td>
                            </tr>";
                    }
                    ?>
                </table>
            </td>
        </tr>
        <?php
        if ($wp->_i == 1 || $wp->_i == "") {
            echo "<tr>
                    <td>" . ($wp->_i == "" ? "" : "New") . " Password</td>
                    <td>: <input type=\"password\" name=\"WP[NPASSWORD]\" id=\"NPASSWORD\" style=\"width: 200px;\"></td>
                </tr>
                <tr>
                    <td>Confirm " . ($wp->_i == "" ? "" : "New") . " Password</td>
                    <td>: <input type=\"password\" name=\"WP[CNPASSWORD]\" id=\"CNPASSWORD\" style=\"width: 200px;\"></td>
                </tr>";
        }
        ?>

        <tr class="button-area">
            <td align="center" colspan="2">

                <?php
                if ($wp->_i == 1 || $wp->_i == "") { #aktif
                    echo "<input type=\"reset\" value=\"Reset\">";
                    if ($edit == false) {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                    } else {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Simpan Perubahan\">";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"blok\" value=\"Blok Wajib Pajak\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_bukti_registrasi\" value=\"Cetak bukti Registrasi\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_form_daftar\" value=\"Cetak Form Pendaftaran\">";
                    }
                } elseif ($wp->_i == 2) {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"aktivasi\" value=\"Konfirmasi aktivasi\">";
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus Wajib Pajak\">";
                } elseif ($wp->_i == 3) {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"aktivasi\" value=\"Konfirmasi aktivasi\">";
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus Wajib Pajak\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>
