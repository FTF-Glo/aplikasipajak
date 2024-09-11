<?php
$DIR = "PATDA-V1";
$modul = "tracking/mineral";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-tracking.php");

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>

<form class="cmxform" id="form-opr" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-crud-angkutan.php">
    <input type="hidden" name="f" id="function" value="create">
    <table class="main" width="900">        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>Data Angkutan</b></td>
        </tr>
        <tr>
            <td width="200">ID Angkutan <b class="isi">*</b></td>
            <td>: <input type="text" name="CPM_TRUCK_ID" id="CPM_TRUCK_ID" style="width: 200px;"></td>
        </tr>
        <tr>
            <td>Nomor Polisi <b class="isi">*</b></td>
            <td>: <input type="text" name="CPM_NOPOL" id="CPM_NOPOL" style="width: 200px;"></td>
        </tr>
        <tr>
            <td>Kapasitas Angkut (m<sup>3</sup>)<b class="isi">*</b></td>
            <td>: <input type="text" name="CPM_KAPASITAS_ANGKUT" id="CPM_KAPASITAS_ANGKUT" style="width: 200px;"></td>
        </tr>
        <tr class="button-area">
            <td align="center" colspan="2">
				<input type="submit" class="btn-submit" action="save" value="Simpan">
            </td>
        </tr>
    </table>
</form>
