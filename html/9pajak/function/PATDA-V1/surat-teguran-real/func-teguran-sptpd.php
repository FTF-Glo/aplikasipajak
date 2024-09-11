<?php
$DIR = "PATDA-V1";
$modul = "surat-teguran-real";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-teguran.php");
$lapor = new Teguran();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$DATA = $lapor->get_pajak();

$DATA['pajak']['CPM_AUTHOR'] = $DATA['pajak']['CPM_AUTHOR'] == "" ? $data->uname : $DATA['pajak']['CPM_AUTHOR'];

$edit = ($lapor->_id != "") ? true : false;
$readonly = ($edit) ? "readonly" : "";
?>

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-teguran.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}"; ?>/main.js"></script>

<form class="cmxform" id="form-lapor" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-teguran.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="tipe_teguran" id="tipe_teguran" value="sptpd">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="a" id="a" value="<?php echo $lapor->_a; ?>">

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>SURAT TEGURAN SPTPD</b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jenis Pajak <b class="isi">*</b></label>
                    <select name="PAJAK[CPM_JENIS_PAJAK]" id="CPM_JENIS_PAJAK" class="form-control" style="width: 90%; display: inline-block">
                        <?php
                        foreach ($lapor->arr_pajak as $x => $y) {
                            $tbl = $lapor->arr_pajak_table[$x];
                            echo ($x == $DATA['pajak']['CPM_JENIS_PAJAK']) ? "<option value='{$x}' data-table='{$tbl}' selected>{$y}</option>" : "<option value='{$x}' data-table='{$tbl}'>{$y}</option>";
                        }
                        ?>
                    </select>
                    <label id="load-tarif"></label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>NPWPD <b class="isi">*</b></label>
                    <!--<input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($DATA['pajak']['CPM_NPWPD']) ?>">
                        <input type="button" value="Cari" class="button" id="btn-search-npwpd">
                        -->
                    <input type="hidden" id="TBLJNSPJK">
                    <select name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" class="form-control"></select>
                    <label id="load-search-npwpd"></label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Usaha <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" class="form-control" value="<?php echo $DATA['pajak']['CPM_NAMA_OP'] ?>" readonly placeholder="Nama Usaha">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Pemilik / Pimpinan <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" class="form-control" value="<?php echo $DATA['pajak']['CPM_NAMA_WP'] ?>" readonly placeholder="Pemilik / Pimpinan">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Alamat <b class="isi">*</b></label>
                    <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" class="form-control" style="min-width: 100%" rows="3" readonly placeholder="Alamat"><?php echo $DATA['pajak']['CPM_ALAMAT_OP'] ?></textarea>
                </div>
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>No Teguran <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_NO_SURAT]" id="CPM_NO_SURAT" class="form-control" maxlength="25" value="<?php echo $DATA['pajak']['CPM_NO_SURAT'] ?>" placeholder="No Teguran"></label>
                    <label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Sifat <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_SIFAT]" id="CPM_SIFAT" class="form-control" maxlength="25" value="<?php echo $DATA['pajak']['CPM_SIFAT'] ?>" placeholder="Sifat">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Lampiran <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_LAMPIRAN]" id="CPM_LAMPIRAN" class="form-control" maxlength="25" value="<?php echo $DATA['pajak']['CPM_LAMPIRAN'] ?>" placeholder="Lampiran">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tahun Pajak <b class="isi">*</b></label>
                    <select name="PAJAK[CPM_TAHUN_PAJAK]" id="CPM_TAHUN_PAJAK" class="form-control">
                        <?php
                        echo "<option value=\"\"> Pilih Tahun</option>";
                        for ($th = date("Y"); $th >= date("Y") - 5; $th--) {
                            echo ($th == $DATA['pajak']['CPM_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Bulan Pajak <b class="isi">*</b></label>
                    <select name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK" class="form-control">
                        <?php
                        echo "<option value=\"\"> Pilih Bulan</option>";
                        foreach ($lapor->arr_bulan as $x => $y) {
                            $x = str_pad($x, 2, 0, STR_PAD_LEFT);
                            echo ($x == $DATA['pajak']['CPM_MASA_PAJAK']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row button-area">
            <div class="col-md-12" align="center">
                <?php
                if ($edit) {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">
                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">
                          <input type=\"button\" class=\"btn-print\" action=\"print_teguran\" value=\"Cetak\">";
                } else {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                }
                ?>
            </div>
        </div>
    </div>
</form>
<div id="modalDialog"></div>