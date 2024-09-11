<?php
$DIR = "PATDA-V1";
$modul = "kegiatan";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-kegiatan.php");
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
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-kegiatan.js?kl"></script>

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
    <input type="hidden" name="kode_kegiatan" value="<?php echo $value['kode_kegiatan']; ?>">
    <table class="main" width="900">
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>Tambah Daftar Pelaksana Kegiatan</b></td>
        </tr>

        <tr class="WP" <?php echo $attr_wp ?>>
            <td width="200">Nama Pelaksana Kegiatan <b class="isi">*</b></td>
            <td>:
                <input type="text" name="nama_kegiatan" id="nama_kegiatan" style="width: 200px;" value="<?php echo $value['nama_kegiatan'] ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?> placeholder="Nama Pellaksana <?php echo $value['CPM_JENIS_TANDABUKTI'] == '' ? 'Kegiatan' : $value['CPM_JENIS_TANDABUKTI'] ?>" />
            </td>
        </tr>

        <tr class="button-area">
            <td align="center" colspan="2">

                <?php
                if ($wp->_i == 1 || $wp->_i == "") { #aktif
                    echo "<input type=\"reset\" value=\"Reset\">";
                    if ($edit == false) {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                    } else {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Simpan Perubahan\">";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus Kegiatan\">";
                        //echo "<input type=\"button\" class=\"btn-submit\" action=\"blok\" value=\"Blok Wajib Pajak\">";
                        //  echo "<input type=\"button\" class=\"btn-print\" action=\"print_bukti_registrasi\" value=\"Cetak bukti Registrasi\">";
                        //  echo "<input type=\"button\" class=\"btn-print\" action=\"print_form_daftar\" value=\"Cetak Form Pendaftaran\">";
                    }
                } elseif ($wp->_i == 3) {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"aktivasi\" value=\"Konfirmasi aktivasi\">";
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus Wajib Pajak\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>