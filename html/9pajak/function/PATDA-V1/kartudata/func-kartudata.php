<?php
$DIR = "PATDA-V1";
$modul = "kartudata";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-kartudata.php");
$wp = new KartuData();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$value = $wp->getDataWP();
if ($wp->_i == 1 || $wp->_i == "") {
    $arr_pajak = explode(";",$value['CPM_JENIS_PAJAK']);
    $radio_jns_pajak[1] = in_array(1, $arr_pajak)? "checked" : "";
    $radio_jns_pajak[2] = in_array(2, $arr_pajak)? "checked" : "";
    $radio_jns_pajak[3] = in_array(3, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[4] = in_array(4, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[5] = in_array(5, $arr_pajak)? "checked" : "";
    $radio_jns_pajak[6] = in_array(6, $arr_pajak)? "checked" : "";
    $radio_jns_pajak[7] = in_array(7, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[8] = in_array(8, $arr_pajak) ? "checked" : "";
    $radio_jns_pajak[9] = in_array(9, $arr_pajak) ? "checked" : "";
} else {
    $arr_pajak = explode(";",$value['CPM_JENIS_PAJAK']);
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
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-wp.js"></script>
<script type="text/javascript">

</script>

<form class="cmxform" id="form-wp" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-wp.php?param=<?php echo base64_encode($json->encode(array("a" => $wp->_a, "m" => $wp->_m))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="WP[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">
    <table class="main" width="900">        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>WAJIB PAJAK</b></td>
        </tr>
        <tr>
            <td>User Name <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_USER]" id="CPM_USER" style="width: 200px;" value="<?php echo $value['CPM_USER'] ?>" <?php echo $readonly ?>></td>
        </tr>
        <tr>
            <td width="200">NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="WP[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($value['CPM_NPWPD']) ?>" <?php echo ($wp->_i == 1 || $wp->_i == "") ? "" : "readonly"; ?>></td>
        </tr>
        <tr>
            <td colspan="2">
                <p><b>Jenis Pajak</b> <b class="isi">*</b></p></br>
                <table width="100%" border="0" align="center" class="header">
                    <?php
                    foreach ($wp->arr_pajak as $a => $b) {
                        echo "<tr>
                                <td width=\"80\">
                                <input type=\"checkbox\" name=\"WP[CPM_JENIS_PAJAK][]\" id=\"CPM_JENIS_PAJAK\" value=\"{$a}\" {$radio_jns_pajak[$a]}> {$b}
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
