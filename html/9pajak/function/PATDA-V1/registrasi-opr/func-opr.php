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
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-opr.js"></script>

<form class="cmxform" id="form-opr" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-opr.php?param=<?php echo base64_encode($json->encode(array("a" => $opr->_a, "m" => $opr->_m))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="OPR[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">
    <input type="hidden" name="OPR[CPM_ROLE_PREV]" value="<?php echo $value['CPM_ROLE']; ?>">

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>PETUGAS PAJAK</b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>User Name <b class="isi">*</b></label>
                    <input type="text" name="OPR[CPM_USER]" id="CPM_USER" class="form-control" value="<?php echo $value['CPM_USER'] ?>" <?php echo $readonly ?>>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nama Petugas <b class="isi">*</b></label>
                    <input type="text" name="OPR[CPM_NAMA]" id="CPM_NAMA" class="form-control" value="<?php echo $value['CPM_NAMA'] ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>NIP <b class="isi">*</b></label>
                    <input type="text" name="OPR[CPM_NIP]" id="CPM_NIP" class="form-control" value="<?php echo $value['CPM_NIP'] ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Role Petugas <b class="isi">*</b></label>
                        <?php
                        foreach ($opr->arr_role as $a => $b) {
                            if ($opr->_i == 1 || $opr->_i == "") {
                                $radio_role[$a] = $value['CPM_ROLE'] == $a ? "checked" : "";
                            } else {
                                $radio_role[$a] = $value['CPM_ROLE'] == $a ? "checked" : "disabled";
                            }
                            echo "
                            <div class=\"form-check\" style=\"margin-left: 20px;\">
                                <input class=\"form-check-input\" type=\"radio\" name=\"OPR[CPM_ROLE]\" id=\"CPM_ROLE{$a}\" value=\"{$a}\" {$radio_role[$a]}>
                                <label class=\"form-check-label\" for=\"CPM_ROLE{$a}\">
                                    {$b}
                                </label>
                            </div>";
                        }
                        ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>New Password <b class="isi">*</b></label>
                    <input type="password" name="OPR[NPASSWORD]" id="NPASSWORD" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Confirm New Password <b class="isi">*</b></label>
                    <input type="password" name="OPR[CNPASSWORD]" id="CNPASSWORD" class="form-control">
                </div>
            </div>
        </div>
        <div class="row button-area">
            <div class="col-md-12" align="center">
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
            </div>
        </div>
    </div>
</form>