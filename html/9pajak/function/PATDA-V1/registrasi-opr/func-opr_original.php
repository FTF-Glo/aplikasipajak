<?php
$DIR = "PATDA-V1";
$modul = "registrasi-opr";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-opr.php");
$opr = new OperatorPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$value = $opr->getDataOpr();

$edit = ($opr->_id != "") ? true : false;
$readonly = ($edit) ? "readonly" : "";
?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-opr.js"></script>

<form class="cmxform" id="form-opr" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-opr.php?param=<?php echo base64_encode($json->encode(array("a" => $opr->_a, "m" => $opr->_m))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="OPR[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">
    <input type="hidden" name="OPR[CPM_ROLE_PREV]" value="<?php echo $value['CPM_ROLE']; ?>">
    <table class="main" width="900">        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PETUGAS PAJAK</b></td>
        </tr>
        <tr>
            <td width="200">User Name <b class="isi">*</b></td>
            <td>: <input type="text" name="OPR[CPM_USER]" id="CPM_USER" style="width: 200px;" value="<?php echo $value['CPM_USER'] ?>" <?php echo $readonly ?>></td>
        </tr>
        <tr>
            <td>Nama Petugas <b class="isi">*</b></td>
            <td>: <input type="text" name="OPR[CPM_NAMA]" id="CPM_NAMA" style="width: 200px;" value="<?php echo $value['CPM_NAMA'] ?>"></td>
        </tr>
        <tr>
            <td>NIP <b class="isi">*</b></td>
            <td>: <input type="text" name="OPR[CPM_NIP]" id="CPM_NIP" style="width: 200px;" value="<?php echo $value['CPM_NIP'] ?>"></td>
        </tr>
        <tr>
            <td colspan="2">
                <p><b>Role Petugas</b> <b class="isi">*</b></p></br>
                <table width="100%" border="0" align="center" class="header">
                    <?php
                    foreach ($opr->arr_role as $a => $b) {
                        if ($opr->_i == 1 || $opr->_i == "") {
                            $radio_role[$a] = $value['CPM_ROLE'] == $a ? "checked" : "";
                        } else {
                            $radio_role[$a] = $value['CPM_ROLE'] == $a ? "checked" : "disabled";
                        }
                        echo "<tr>
                                <td width=\"80\">
                                <input type=\"radio\" name=\"OPR[CPM_ROLE]\" id=\"CPM_ROLE\" value=\"{$a}\" {$radio_role[$a]}> {$b}
                                </td>
                            </tr>";
                    }
                    ?>                   
                </table>           
            </td>
        </tr>
        <tr>
            <td>New Password <b class="isi">*</b></td>
            <td>: <input type="password" name="OPR[NPASSWORD]" id="NPASSWORD" style="width: 200px;"></td>
        </tr>
        <tr>
            <td>Confirm New Password <b class="isi">*</b></td>
            <td>: <input type="password" name="OPR[CNPASSWORD]" id="CNPASSWORD" style="width: 200px;"></td>
        </tr>
        <tr class="button-area">
            <td align="center" colspan="2">

                <?php
                if ($opr->_i == 1 || $opr->_i == "") { #aktif
                    echo "<input type=\"reset\" value=\"Reset\">";
                    if ($edit == false) {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                    } else {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Simpan Perubahan\">";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"blok\" value=\"Blok Wajib Pajak\">";
                    }
                } elseif ($opr->_i == 2) {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"aktivasi\" value=\"Konfirmasi aktivasi\">";
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus Wajib Pajak\">";
                } elseif ($opr->_i == 3) {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"aktivasi\" value=\"Konfirmasi aktivasi\">";
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus Wajib Pajak\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>
