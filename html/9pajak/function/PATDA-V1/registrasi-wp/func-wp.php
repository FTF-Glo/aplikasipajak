<?php
$DIR = "PATDA-V1";
$modul = "registrasi-wp";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-wp.php");
$wp = new WajibPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$value = $wp->getDataWP();

$readonly_cty = false;
if (empty($value['CPM_KOTA_WP']) && (!isset($value['CPM_ID']) || $value['CPM_ID'] == '')) {
    $value['CPM_KOTA_WP'] = 'Pringsewu';
    $readonly_cty = true;
}

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
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-wp.js"></script>

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

<form class="cmxform" id="form-wp" method="post" enctype="multipart/form-data" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-wp.php?param=<?php echo base64_encode($json->encode(array("a" => $wp->_a, "m" => $wp->_m))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="WP[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>WAJIB PAJAK </b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Id <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_USER]" id="CPM_USER" class="form-control" value="<?php echo $value['CPM_USER'] ?>" readonly placeholder="by sistem">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Jenis WP <b class="isi">*</b></label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_WP]" class="CPM_JENIS_WP" value="1" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_WP'] == 1 ? 'checked' : 'checked') ?>> WP Pribadi</label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_WP]" class="CPM_JENIS_WP" value="2" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_WP'] == 2 ? 'checked' : '') ?>> WP Badan</label>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label>NPWPD <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" value="<?php echo $value['CPM_NPWPD'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "readonly" : "readonly"; ?> placeholder="by sistem">
                </div>
            </div>
        </div>
        <div class="row WB" <?php echo $attr_wb ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Jabatan <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_JABATAN]" id="CPM_JABATAN" class="form-control" value="<?php echo (isset($value['CPM_JABATAN']) ? $value['CPM_JABATAN'] : '') ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Jabatan" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Surat Izin Tempat Usaha <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_SURAT_IZIN]" id="CPM_SURAT_IZIN" class="form-control" value="<?php echo $value['CPM_SURAT_IZIN'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Surat Izin" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>No Surat Izin Tempat Usaha <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_NO_SURAT_IZIN]" id="CPM_NO_SURAT_IZIN" class="form-control" value="<?php echo $value['CPM_NO_SURAT_IZIN'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="No Surat Izin" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tgl Surat Izin Tempat Usaha <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_TGL_SURAT_IZIN]" id="CPM_TGL_SURAT_IZIN" class="form-control datepicker" size="10" value="<?php echo $value['CPM_TGL_SURAT_IZIN'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="dd/mm/yyyy" autocomplete="off" />
                </div>
            </div>
        </div>
        <div class="row WP" <?php echo $attr_wp ?>>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Kewarganegaraan <b class="isi">*</b></label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_KEWARGANEGARAAN]" class="CPM_JENIS_KEWARGANEGARAAN" value="WNI" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_KEWARGANEGARAAN'] == 'WNI' ? 'checked' : 'checked') ?>> WNI</label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_KEWARGANEGARAAN]" class="CPM_JENIS_KEWARGANEGARAAN" value="WNA" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_KEWARGANEGARAAN'] == 'WNA' ? 'checked' : '') ?>> WNA</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Tanda Bukti <b class="isi">*</b></label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_TANDABUKTI]" class="CPM_JENIS_TANDABUKTI" value="KTP" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_TANDABUKTI'] == 'KTP' ? 'checked' : 'checked') ?>> KTP</label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_TANDABUKTI]" class="CPM_JENIS_TANDABUKTI" value="SIM" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_TANDABUKTI'] == 'SIM' ? 'checked' : '') ?>> SIM</label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_TANDABUKTI]" class="CPM_JENIS_TANDABUKTI" value="PASPOR" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_TANDABUKTI'] == 'PASPOR' ? 'checked' : '') ?>> PASPOR</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <span id="file_tandabukti"><?php
                                                if (empty($value['CPM_FILE_TANDABUKTI'])) {
                                                    echo '<label>Pilih file Gambar atau PDF</label>';
                                                } else {
                                                    echo "&nbsp; &nbsp;<a href=\"image/tandabukti/{$value['CPM_FILE_TANDABUKTI']}\" title=\"{$value['CPM_FILE_TANDABUKTI']}\" target=\"_blank\"><i class=\"fa fa-eye\"></i> Lihat File {$value['CPM_JENIS_TANDABUKTI']}</a>";
                                                }
                                                ?></span>
                    <input type="hidden" name="WP[CPM_FILE_TANDABUKTI]" value="<?php echo (isset($value['CPM_FILE_TANDABUKTI']) ? $value['CPM_FILE_TANDABUKTI'] : '') ?>" />
                    <input type="file" name="FILE_TANDABUKTI" class="form-control" style="border:none" xaccept="image/*,application/pdf" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Nomor Tanda Bukti <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_NO_TANDABUKTI]" id="CPM_NO_TANDABUKTI" class="form-control" value="<?php echo $value['CPM_NO_TANDABUKTI'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nomor <?php echo $value['CPM_JENIS_TANDABUKTI'] == '' ? 'KTP' : $value['CPM_JENIS_TANDABUKTI'] ?>" />
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Tanggal Tanda Bukti <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_TGL_TANDABUKTI]" id="CPM_TGL_TANDABUKTI" class="form-control datepicker" size="10" value="<?php echo $value['CPM_TGL_TANDABUKTI'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="dd/mm/yyyy" autocomplete="off" />
                </div>
            </div>
        </div>
        <hr / <?php echo $attr_wp ?>>
        <div class="row WP" <?php echo $attr_wp ?>>
            <div class="col-md-4">
                <label>Kartu Keluarga</label>
                <div class="form-group">
                    <span id="file_kk"><?php
                                        if (empty($value['CPM_FILE_KK'])) {
                                            echo 'Pilih file Gambar atau PDF';
                                        } else {
                                            echo "<a href=\"image/tandabukti/{$value['CPM_FILE_KK']}\" title=\"{$value['CPM_FILE_KK']}\" target=\"_blank\"><i class=\"fa fa-eye\"></i> Lihat File KK</a>";
                                        }
                                        ?></span>
                    <input type="hidden" name="WP[CPM_FILE_KK]" value="<?php echo isset($value['CPM_FILE_KK']) ? $value['CPM_FILE_KK'] : '' ?>" />
                    <input type="file" name="FILE_KK" class="form-control" style="border:none" xaccept="image/*,application/pdf" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nomor Kartu Keluarga <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_NO_KK]" id="CPM_NO_KK" class="form-control" value="<?php echo $value['CPM_NO_KK'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nomor Kartu Keluarga" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tanggal Kartu Keluarga <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_TGL_KK]" id="CPM_TGL_KK" size="10" class="form-control datepicker" value="<?php echo $value['CPM_TGL_KK'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="dd/mm/yyyy" autocomplete="off" />
                </div>
            </div>
        </div>
        <hr / <?php echo $attr_wp ?>>
        <div class="row WP" <?php echo $attr_wp ?>>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Pekerjaan/Usaha <b class="isi">*</b></label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="Pegawai Negeri" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_PEKERJAAN'] == 'Pegawai Negeri' ? 'checked' : 'checked') ?>> Pegawai Negeri</label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="Pegawai Swasta" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_PEKERJAAN'] == 'Pegawai Swasta' ? 'checked' : '') ?>> Pegawai Swasta</label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="ABRI" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_PEKERJAAN'] == 'ABRI' ? 'checked' : '') ?>> ABRI</label>
                    <label><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="Pemilik Usaha" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php echo ($value['CPM_JENIS_PEKERJAAN'] == 'Pemilik Usaha' ? 'checked' : '') ?>> Pemilik Usaha</label>
                    <label style="display: block"><input type="radio" name="WP[CPM_JENIS_PEKERJAAN]" class="CPM_JENIS_PEKERJAAN" value="Lainnya" <?php echo ($wp->_i == 1 || $wp->_i == "") ? ($edit ? "onclick='javascript:return true'" : "") : "onclick='javascript:return true'"; ?> <?php
                                                                                                                                                                                                                                                                                            $ajobs = array('Pegawai Negeri', 'Pegawai Swasta', 'ABRI', 'Pemilik Usaha');
                                                                                                                                                                                                                                                                                            echo ($value['CPM_JENIS_PEKERJAAN'] != '' && !in_array($value['CPM_JENIS_PEKERJAAN'], $ajobs)) ? 'checked' : '';
                                                                                                                                                                                                                                                                                            ?>> Lainnya</label>
                    <input type="text" name="WP[CPM_JENIS_PEKERJAAN1" id="CPM_JENIS_PEKERJAAN1" <?php echo ($value['CPM_JENIS_PEKERJAAN'] == '' || in_array($value['CPM_JENIS_PEKERJAAN'], $ajobs)) ? 'style="display:none"' : '' ?> value="<?php echo !in_array($value['CPM_JENIS_PEKERJAAN'], $ajobs) ? $value['CPM_JENIS_PEKERJAAN'] : '' ?>">

                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nama Instansi Tempat Pekerjaan atau Usahaa <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_NAMA_USAHA]" id="CPM_NAMA_USAHA" class="form-control" value="<?php echo $value['CPM_NAMA_USAHA'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Instansi Tempat Pekerjaan" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Alamat Instansi atau Usaha <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_ALAMAT_USAHA]" id="CPM_ALAMAT_USAHA" class="form-control" value="<?php echo $value['CPM_ALAMAT_USAHA'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Alamat Instansi atau Usaha" />
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nomor NPWP</label>
                    <input type="text" name="WP[CPM_NO_NPWP]" id="CPM_NO_NPWP" class="form-control" value="<?php echo isset($value['CPM_NO_NPWP']) ? $value['CPM_NO_NPWP'] : ''; ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="No NPWP" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <span id="file_npwp"><?php
                                            if (empty($value['CPM_FILE_NPWP'])) {
                                                echo '<label>Pilih file Gambar atau PDF</label>';
                                            } else {
                                                echo "<a href=\"image/tandabukti/{$value['CPM_FILE_NPWP']}\" title=\"{$value['CPM_FILE_NPWP']}\" target=\"_blank\"><i class=\"fa fa-eye\"></i> Lihat File NPWP</a>";
                                            }
                                            ?></span>
                    <input type="hidden" name="WP[CPM_FILE_NPWP]" value="<?php echo isset($value['CPM_FILE_NPWP']) ? $value['CPM_FILE_NPWP'] : '' ?>" />
                    <input type="file" name="FILE_NPWP" class="form-control" style="border:none" xaccept="image/*,application/pdf" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label class="WB" <?php echo $attr_wb ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>Nama Pemilik <b class="isi">*</b></label>
                    <label <?php echo $attr_wp ?>>Nama Lengkap <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_NAMA_WP]" id="CPM_NAMA_WP" class="form-control" value="<?php echo $value['CPM_NAMA_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Lengkap" />
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label>No Telepon <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_TELEPON_WP]" id="CPM_TELEPON_WP" class="form-control" value="<?php echo $value['CPM_TELEPON_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="No Telepon" />
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Asal WP <b class="isi">*</b></label>
                    <label style="display: block"><input type="radio" name="WP[CPM_LUAR_DAERAH]" class="CPM_LUAR_DAERAH" value="0" <?php echo (isset($value['CPM_LUAR_DAERAH']) ? ($value['CPM_LUAR_DAERAH'] == 0 ? 'checked' : '') : 'checked') ?> <?php echo (!empty($value['CPM_USER']) ? "onclick='javascript:return true'" : '') ?>> Dalam Daerah</label>
                    <label style="display: block"><input type="radio" name="WP[CPM_LUAR_DAERAH]" class="CPM_LUAR_DAERAH" value="1" <?php echo (isset($value['CPM_LUAR_DAERAH']) ? ($value['CPM_LUAR_DAERAH'] == 1 ? 'checked' : '') : '') ?> <?php echo (!empty($value['CPM_USER']) ? "onclick='javascript:return true'" : '') ?>> Luar Daerah</label>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Kecamatan WP <b class="isi">*</b></label>
                    <div class="DK" <?php echo (!isset($value['CPM_LUAR_DAERAH']) || $value['CPM_LUAR_DAERAH'] == 0 ? '' : 'style="display: none;"') ?>>
                        <select name="WP[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" class="form-control" data-kel="<?php echo $value['CPM_KECAMATAN_WP'] ?>">
                            <option></option>
                            <?php
                            if (count($kecamatan) > 0) {
                                foreach ($kecamatan as $kec) {
                                    echo '<option value="' . $kec->CPM_KEC_ID . '" ' . ($value['CPM_KECAMATAN_WP'] == $kec->CPM_KECAMATAN ? 'selected' : '') . '>' . $kec->CPM_KECAMATAN . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="LK" <?php echo (!isset($value['CPM_LUAR_DAERAH']) || $value['CPM_LUAR_DAERAH'] == 1 ? '' : 'style="display: none;"') ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>
                        <input type="text" name="WP[CPM_KECAMATAN_WP1]" id="CPM_KECAMATAN_WP1" class="form-control" value="<?php echo $value['CPM_KECAMATAN_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Kecamatan" />
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Kelurahan WP <b class="isi">*</b></label>
                    <div class="DK" <?php echo (!isset($value['CPM_LUAR_DAERAH']) || $value['CPM_LUAR_DAERAH'] == 0 ? '' : 'style="display: none;"') ?>>
                        <select name="WP[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" class="form-control">
                            <option value="<?php echo $value['CPM_KELURAHAN_WP'] ?>"><?php echo $value['CPM_KELURAHAN_WP'] ?></option>
                        </select>
                    </div>
                    <div class="LK" <?php echo (!isset($value['CPM_LUAR_DAERAH']) || $value['CPM_LUAR_DAERAH'] == 1 ? '' : 'style="display: none;"') ?> <?php echo (empty($value['CPM_USER']) ? 'style="display: none;"' : '') ?>>
                        <input type="text" name="WP[CPM_KELURAHAN_WP1]" id="CPM_KELURAHAN_WP1" class="form-control" value="<?php echo $value['CPM_KELURAHAN_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Kelurahan" />
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>RT/RW WP <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_RTRW_WP]" id="CPM_RTRW_WP" class="form-control" value="<?php echo $value['CPM_RTRW_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="RT/RW" />
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Kota/Kabupaten WP <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_KOTA_WP]" id="CPM_KOTA_WP" class="form-control" value="<?php echo $value['CPM_KOTA_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Kota/Kabupaten" />
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Kode Pos WP <b class="isi">*</b></label>
                    <input type="text" name="WP[CPM_KODEPOS_WP]" id="CPM_KODEPOS_WP" class="form-control" value="<?php echo $value['CPM_KODEPOS_WP'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Kode Pos" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Alamat WP <b class="isi">*</b></label>
                    <textarea name="WP[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" rows="3" style="min-width: 100%" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Alamat"><?php echo $value['CPM_ALAMAT_WP'] ?></textarea>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label><b>Jenis Pajak</b> <b class="isi">*</b></label>
                    <div class="alert alert-warning">
                        <div class="row">
                            <?php
                            foreach ($wp->arr_pajak as $a => $b) {
                                echo "
                                    <div class=\"col-md-4\">
                                        <label style='padding:2px; margin: 10px'><input type=\"checkbox\" name=\"WP[CPM_JENIS_PAJAK][]\" id=\"CPM_JENIS_PAJAK\" value=\"{$a}\" {$radio_jns_pajak[$a]}> {$b}</label>
                                    </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr/>
        
        <?php
        if ($wp->_i == 1 || $wp->_i == "") {
            echo "<div class=\"row\">
                    <div class=\"col-md-6\">
                        <div class=\"form-group\">
                            <label>" . ($wp->_i == "" ? "" : "New") . " Password</label>
                            <input type=\"password\" name=\"WP[NPASSWORD]\" id=\"NPASSWORD\" class=\"form-control\">
                        </div>
                    </div>
                    <div class=\"col-md-6\">
                        <div class=\"form-group\">
                            <label>Confirm " . ($wp->_i == "" ? "" : "New") . " Password</label>
                            <input type=\"password\" name=\"WP[CNPASSWORD]\" id=\"CNPASSWORD\" class=\"form-control\">
                        </div>
                    </div>
                </div>";
        }
        ?>

        <div class="row button-area">
            <div class="col-md-12" align="center">

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
            </div>
        </div>
    </div>
</form>