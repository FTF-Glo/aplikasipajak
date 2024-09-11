<?php

$DIR = "PATDA-V1";
$modul = "reklame";
require_once("inc/payment/json.php");
require_once("inc/payment/sayit.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");
// global $sRootPath;
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
$list_tarif = $lapor->list_tarif();
// var_dump($list_tarif);exit;
// $payment_flag = $lapor->payment_flag($DATA['pajak']['CPM_ID']);
$list_type_masa = $lapor->get_type_masa();
$list_kawasan = $lapor->get_kawasan();
$list_jalan = $lapor->get_jalan();
$list_jalan_type = $lapor->get_jalan_type();
// var_dump($list_jalan);exit;

$list_sudut_pandang = $lapor->get_sudut_pandang();
$list_type_tinggi = $lapor->get_type_tinggi();

//$list_type_masa = array(1=>'Tahun', 4=>'Bulan'); //$lapor->get_type_masa();
//$list_kawasan = $list_tarif['lokasi'];
//$list_jalan = $lapor->get_jalan();
//$list_sudut_pandang = $lapor->get_sudut_pandang();
$list_rekening = $lapor->get_list_rekening();
// var_dump($list_rekening);exit;
$count = count($DATA['pajak_atr']);
// echo '<pre>',print_r($DATA['pajak_atr']),'</pre>';
// var_dump($DATA['pajak']['CPM_NO']);die;
// var_dump($npwpd);
if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $DATA = $lapor->get_previous_pajak($npwpd, $nop);
}


//list pejabat
$list_pejabat = $lapor->get_pejabat();
foreach ($list_pejabat as $list_pejabat) {
    $opt_pejabat .= "<option value=\"{$list_pejabat['CPM_KEY']}\">{$list_pejabat['CPM_NIP']} - {$list_pejabat['CPM_NAMA']}</option>";
}
// var_dump($list_pejabat);exit;

function getImage($kodelampiran, $nosptpd)
{
    global $DBLink;
    $berkas = '';
    $qry = "select * from patda_upload_file where CPM_NO_SPTPD = '$nosptpd' and CPM_KODE_LAMPIRAN = '$kodelampiran'";

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    $row = mysqli_num_rows($res);
    if ($row >= 1) {
        while ($row = mysqli_fetch_assoc($res)) {
            $berkas = "<a href ='function/PATDA-V1/pelayanan/upload/{$row['CPM_FILE_NAME']}' target='_blank'>Download/view</a>";
        }
    } else {
        $berkas = "-";
    }
    return $berkas;
}

function getIDBerkas($nosptpd)
{
    global $DBLink;
    $patdaberkas = '';
    $qry = "select * from patda_berkas where CPM_NO_SPTPD = '$nosptpd'";
    $res = mysqli_query($DBLink, $qry);
    while ($row = mysqli_fetch_assoc($res)) {
        $patdaberkas = $row['CPM_ID'];
    }

    return $patdaberkas;
}

?>
<style>
    #formGroup {
        position: relative;
    }

    .ui-datepicker-trigger {
        height: 25px;
    }
</style>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/<?php echo $DIR; ?>/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/js/terbilang.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js?v=<?= date('Hi') ?>"></script>
<script type="text/javascript" src="inc/<?php echo "{$DIR}"; ?>/select2/js/select2.min.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js?v=<?= date('Hi') ?>"></script>
<?php
// $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PATDA-V1'.DIRECTORY_SEPARATOR.'reklame'.DIRECTORY_SEPARATOR.'lapor', '', dirname(__FILE__))).'/';
// var_dump($sRootPath."/image/notif.mp3");die;
echo '<source src= "image/notif.mp3" type="audio/ogg">';

// print_r ($_SERVER['SERVER_NAME'], $_SERVER['SERVER_NAME']);
?>
<audio id="myAudio">
    <source src="<?= $sRootPath ?>image/notif.mp3" type="audio/ogg">
</audio>
<form class="cmxform" autocomplete="off" id="form-lapor" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>" enctype="multipart/form-data">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="CPM_TIPE_PAJAK" id="CPM_TIPE_PAJAK" value="2">
    <input type="hidden" name="param" id="param" value="<?php echo base64_encode("a=" . $lapor->_a . "&m=" . $lapor->_m . "&f=" . $lapor->_f) ?>">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" id="persen_terlambat_lap" value="<?php echo $persen_terlambat_lap ?>">
    <input type="hidden" id="type_terlambat_lap" value="<?php echo $lapor->id_pajak ?>">
    <input type="hidden" id="editable_terlambat_lap" value="<?php echo $editable_terlambat_lap ?>">
    <input type="hidden" name="PAJAK[CPM_VERSION]" value="<?php echo  $DATA['pajak']['CPM_VERSION']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo  $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_PROFIL]" value="<?php echo  $DATA['profil']['CPM_ID']; ?>">
    <!-- var_dump($DATA['profil']['CPM_ID']); -->
    <input type="hidden" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" value="<?php echo  $DATA['pajak']['CPM_TARIF_PAJAK']; ?>">
    <input type="hidden" name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK" value="<?php echo  $DATA['pajak']['CPM_MASA_PAJAK']; ?>" />
    <input type="hidden" name="PAJAK[CPM_MASA_PAJAK1]" value="<?php echo  $DATA['pajak']['CPM_MASA_PAJAK1']; ?>" />
    <input type="hidden" name="PAJAK[CPM_MASA_PAJAK2]" value="<?php echo  $DATA['pajak']['CPM_MASA_PAJAK2']; ?>" />
    <input type="hidden" name="PAJAK[CPM_JNS_MASA_PAJAK]" id="CPM_JNS_MASA_PAJAK" value="<?php echo  $DATA['pajak']['CPM_JNS_MASA_PAJAK']; ?>" />

    <?php if ($lapor->_s == 4) : ?>
        <input type="hidden" name="PAJAK[DITOLAK_TGL_LAPOR]" value="<?php echo $DATA['pajak']['CPM_TGL_LAPOR']; ?>">
        <input type="hidden" name="PAJAK[DITOLAK_TGL_INPUT]" value="<?php echo $DATA['pajak']['CPM_TGL_INPUT']; ?>">
    <?php endif; ?>

    <input type="hidden" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_ID'] ?>" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TOTAL'] ?>" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TARIF][]" id="CPM_ATR_TARIF" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TARIF'] ?>" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_JUMLAH_TAHUN][]" id="CPM_ATR_JUMLAH_TAHUN" value="
           <?php echo  isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_TAHUN']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_TAHUN'] : 0 ?>" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_JUMLAH_BULAN][]" id="CPM_ATR_JUMLAH_BULAN" value="
           <?php echo  isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_BULAN']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_BULAN'] : 0 ?>" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_JUMLAH_MINGGU][]" id="CPM_ATR_JUMLAH_MINGGU" value="
           <?php echo  isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_MINGGU']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_MINGGU'] : 0 ?>" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_JUMLAH_HARI][]" id="CPM_ATR_JUMLAH_HARI" value="
           <?php echo  isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'] : 0 ?>" />
    <span style="display:none" id="tarif-kawasan"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TARIF'] ?></span>
    <?php
    if ($lapor->_id != "") {
        echo "<div class=\"message-box\">";
        echo "<div style=\"margin-button:0px;\"><b>Versi Dokumen :</b> {$DATA['pajak']['CPM_VERSION']}</div>";
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> {$lapor->arr_status[$lapor->_s]}</div>";
        echo ($lapor->_s == 4) ? "<div style=\"margin:auto\"><b>Ditolak karena :</b> {$lapor->_info}</div>" : "";
        echo ($lapor->_s == 4 && $lapor->_flg == 1) ? "<div style=\"margin:auto\"><b>Keterangan : </b>Sudah dibuatkan versi " . ($DATA['pajak']['CPM_VERSION'] + 1) . " nya</div>" : "";
        echo "</div>";
    }

    if (in_array($s, array(2, 3))) {
        $defaultdate =  date('Y-m-d');
        $ttd_penanggung_jawab .= "
                <div class=\"row\">
                    <div class=\"col-md-12\">
                        <label class=\"lm-subtitle\">TTD Penangung Jawab</label>
                        <hr/>
                    </div>
                </div>
                <div class=\"row\">
                    <div class=\"col-md-6\">
                        <div class=\"form-group\">
                            <label>Mengetahui</label>
                            <select id=\"PEJABAT2\" class=\"form-control\" name=\"PAJAK[CPM_PEJABAT_MENGETAHUI]\">{$opt_pejabat}</select>
                        </div>
                    </div>
                    <div class=\"col-md-6\">
                        <div class=\"form-group\">
                            <label>Tanggal Pengesahan </label>
                            <input type='date' class=\"form-control\" name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' >
                        </div>
                    </div>
                </div>";
    }
    ?>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>PROFIL PAJAK REKLAME</b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?php if (!empty($npwpd)) : ?>
                        <label>NPWPD <b class="isi">*</b></label>
                        <input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" value="<?php echo Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) ?>" readonly>
                        <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                            <?php
                            if (empty($_SESSION['npwpd'])) :
                                $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f);
                            ?>
                                <input type="button" class="btn btn-sm btn-secondary lm-btn" style="margin-top: 10px;" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm ?>'">
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php
                        if (!empty($DATA['profil']['CPM_NPWPD']) && empty($DATA['pajak']['CPM_ID'])) {
                            $addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
                            $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';
                            echo '<input type="button" class="btn btn-sm btn-secondary lm-btn" style="margin-top: 10px;" value="Tambah NOP" onclick="location.href=\'' . $prm . '\'">';
                        } ?>
                    <?php else : ?>
                        <label>NPWPD <b class="isi">*</b></label>
                        <input type="hidden" id="TBLJNSPJK" value="REKLAME">
                        <select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" style="width:90%"></select>
                        <label id="loading"></label>
                    <?php endif; ?>

                    <?php if ($DATA['profil']['tapping'] == 1) { ?>
                        <?php if (!$DATA['pajak']['CPM_NO']) { ?>
                            <script>
                                if (confirm('Objek Pajak ini menggunakan Tappingbox, apakah akan melanjutkan pelaporan?')) {
                                    // do something if user clicks "OK"
                                } else {
                                    // do something if user clicks "Cancel"
                                    window.history.back(); // this will redirect the user to the previous page
                                }
                            </script>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Wajib Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" class="form-control" value="<?php echo  $DATA['profil']['CPM_NAMA_WP'] ?>" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Alamat Wajib Pajak <b class="isi">*</b></label>
                    <textarea name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" style="min-width: 100%" rows="3" readonly><?php echo  $DATA['profil']['CPM_ALAMAT_WP'] ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Kecamatan Wajib Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" class="form-control" value="<?php echo $DATA['profil']['CPM_KECAMATAN_WP'] ?>" readonly placeholder="Kecamatan Wajib Pajak">
                    <?php
                    if (
                        !empty($npwpd) &&
                        (empty($DATA['profil']['CPM_KECAMATAN_WP']) ||
                            empty($DATA['profil']['CPM_KELURAHAN_WP'])
                        )
                    ) :
                        $prm = 'main.php?param=' .
                            base64_encode('a=' . $a . '&m=mPatdaPelayananRegWP&mod=&f=fPatdaPelayananRegWp&id=' . $npwpd . '&s=1&i=1');
                    ?>
                        <a href="<?php echo $prm ?>" class="btn btn-primary lm-btn" style="margin-top: 10px" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)"><i class="fa fa-edit"></i> Ubah data WP</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Kelurahan Wajib Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" class="form-control" value="<?php echo $DATA['profil']['CPM_KELURAHAN_WP'] ?>" readonly placeholder="Kelurahan Wajib Pajak">

                </div>
            </div>
        </div>
    </div>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 lm-title subtitle" align="center">
                <b>LAPOR PAJAK REKLAME</b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php
                if (!empty($npwpd) && !empty($nop) && ($lapor->_id == "")) {
                    if (isset($get_previous)) {
                        $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f . '&npwpd=' . $npwpd . '&nop=' . $nop) . '#btn-get_previous';

                        if (empty($DATA['pajak']['CPM_TOTAL_PAJAK'])) {
                            echo '<center id="btn-get_previous">
                                    Data Pelaporan sebelumnya tidak tersedia
                                    <br/><br/>
                                </center>';
                        } else {
                            echo '<center>
                                    <input type="button" class="btn btn-primary lm-btn" value="Kosongkan pelaporan sebelumnya" onclick="location.href=\'' . $prm . '\'" id="btn-get_previous">
                                    <br/><br/>
                                </center>';
                        }
                    } else {

                        $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f . '&npwpd=' . $npwpd . '&nop=' . $nop . '&get_previous=1') . '#btn-get_previous';
                        echo '<center>
                                <input type="button" class="btn btn-primary lm-btn" value="Isi form dengan pelaporan sebelumnya" onclick="location.href=\'' . $prm . '\'" id="btn-get_previous">
                                <br/><br/>
                            </center>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="lm-subtitle">Data Pajak</div>
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>No Pelaporan Pajak <b class="isi">*</b></label>
                    <?php echo (!$DATA['pajak']['CPM_NO'] != "") ? "<input style=\"width:50%;\" type=\"text\" class=\"form-control\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "<span style=\"color:red; display: block\">Tidak Tersedia</span>" ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal Input <b class="isi">*</b></label>
                    <input type="text" class="form-control" name="PAJAK[SAVED_DATE]" id="SAVED_DATE1" value="<?php echo date("Y-m-d", strtotime(($DATA['pajak']['CPM_TGL_INPUT'] == "" ? date("Y-m-d") : $DATA['pajak']['CPM_TGL_INPUT']))) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Tanggal Pelaporan">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label> Tahun Pajak<b class="isi">*</b></label>
                    <select class="form-control" name="PAJAK[CPM_TAHUN_PAJAK]" tabindex="10" id="CPM_TAHUN_PAJAK" name="CPM_TAHUN_PAJAK">
                        <?php
                        if (in_array($lapor->_mod, array("pel", ""))) {
                            if (!in_array($lapor->_i, array(1, 3, ""))) {
                                echo "<option value='{$DATA['pajak']['CPM_TAHUN_PAJAK']}' selected>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</option>";
                            } else {
                                for ($th = date("Y"); $th <= date("Y"); $th++) {

                                    echo ($th != $DATA['pajak']['CPM_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                }
                            }
                        } else {
                            echo "<option value='{$DATA['pajak']['CPM_TAHUN_PAJAK']}' selected>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Masa Pajak <b class="isi">*</b></label>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AWAL][]" id='CPM_ATR_BATAS_AWAL' style="display: inline-block; width: 80%" readonly value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "class='datepicker form-control'" : "class='form-control' readonly"; ?> placeholder="Batas Awal" tabindex="11" title="Batas Awal">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                s/d
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AKHIR][]" id='CPM_ATR_BATAS_AKHIR' style="display: inline-block; width: 80%" readonly value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "class='datepicker form-control'" : "class='form-control' readonly"; ?> placeholder="Batas Akhir" tabindex="12" title="Batas Akhir">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="background-color: #e0f7fa; padding: 15px; border: 1px solid #007acc; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <div class="row">
                <div class="col-md-12">
                    <div class="lm-subtitle-md">Reklame</div>
                    <hr />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Pilih OP <b class="isi">*</b></label>
                        <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                            <select name="PAJAK_ATR[CPM_ATR_NOP][]" tabindex="14" id="CPM_NOP" class="CPM_NOP form-control">
                                <?php
                                if (count($DATA['list_nop']) == 0) echo "<option value=''>NOP Tidak tersedia</option>";
                                else echo (empty($nop)) ? "<option value='' selected disabled>Pilih NOP</option>" : "";

                                foreach ($DATA['list_nop'] as $list) {
                                    $alamat = !empty($list['CPM_ALAMAT_OP']) ? $list['CPM_ALAMAT_OP'] . ', ' : '';
                                    $kel = !empty($list['CPM_KELURAHAN']) ? $list['CPM_KELURAHAN'] . ', ' : '';
                                    echo "<option value='{$list['CPM_ID']}' " . ($nop == $list['CPM_NOP'] ? 'selected' : '') . ">{$list['CPM_NOP']} - {$list['CPM_NAMA_OP']} | {$alamat}{$kel}Kec. {$list['CPM_KECAMATAN']}</option>";
                                }

                                ?>
                            </select>
                        <?php else : ?>
                            <input class="form-control" type="text" value="<?php echo $DATA['pajak_atr'][0]['CPM_NOP'], ' - ', $DATA['pajak_atr'][0]['CPM_NAMA_OP'] ?>" readonly />
                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_NOP][]" id="CPM_NOP-'.$no.'" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_ID_PROFIL'] ?>" class="form-control" readonly />
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Pilih rekening <b class="isi">*</b></label>
                        <select class="form-control" tabindex="15" name="PAJAK_ATR[CPM_ATR_REKENING][]" id="CPM_ATR_REKENING">
                            <?php
                            if ($lapor->_s == "") echo "<option data-nmrek='' data-tarif='0' data-harga='0' value='' selected>Pilih Rekening</option>";
                            foreach ($list_rekening as $rek) {
                                $selected = $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek ? ' selected' : '';
                                $disabled = (empty($DATA['pajak']['CPM_ID']) || $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek) ? '' : ' disabled';
                                if (!in_array($lapor->_i, array(1, 3, ""))) {
                                    echo "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
                                    data-tinggi='{$rek->tarif3}'{$selected}{$disabled}>{$rek->kdrek} - {$rek->nmrek}</option>";
                                } else {
                                    echo "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
                                    data-tinggi='{$rek->tarif3}'{$selected}>{$rek->kdrek} - {$rek->nmrek}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nama rekening</label>
                        <span id="nama-rekening" style="display: block; width: 100%; text-align: center;"><?php echo $DATA['pajak_atr'][0]['nmrek'] ?></span><br /><span id="warning-rekening"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Jenis Waktu Pemakaian</label>
                        <select class="form-control" id="CPM_ATR_TYPE_MASA" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]">
                            <?php
                            $type_masa = $DATA['pajak_atr'][0]['CPM_ATR_TYPE_MASA'];
                            if (in_array($lapor->_mod, array("pel", ""))) {
                                if (!in_array($lapor->_i, array(1, 3, ""))) {
                                    echo "<option value='{$type_masa}' selected>{$list_type_masa[$type_masa]}</option>";
                                } else {
                                    foreach ($list_type_masa as $key => $val) {

                                        echo "<option value='{$key}' " . ($type_masa == $key ? 'selected' : '') . ">$val</option>";
                                    }
                                }
                            } else {
                                echo "<option value='{$type_masa}' selected>{$list_type_masa[$type_masa]}</option>";
                            }

                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Lokasi Reklame </label>
                        <select class="form-control" id="CPM_ATR_JALAN" name="PAJAK_ATR[CPM_ATR_JALAN][]" class="form-control">
                            <?php
                            $jln = $DATA['pajak_atr'][0]['CPM_ATR_JALAN'];
                            if (in_array($lapor->_mod, array("pel", ""))) {
                                if (!in_array($lapor->_i, array(1, 3, ""))) {
                                    echo "<option value='{$jln}' selected>{$jln}</option>";
                                } else {
                                    echo "<option value=''>Pilih Jalan</option>";
                                    foreach ($list_jalan as $kws) {

                                        echo "<option value='{$kws}' " . ($jln == $kws ? 'selected' : '') . ">$kws</option>";
                                    }
                                }
                            } else {
                                echo "<option value='{$jln}' selected>{$jln}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Pembayaran Melalui Pihak Ketiga</label>
                        <div id="formGroup">
                            <?php if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) {  ?>
                                <input name="PAJAK_ATR[CPM_CEK_PIHAK_KETIGA][]" type="checkbox" id="CPM_CEK_PIHAK_KETIGA" value="1" <?= ($DATA['pajak_atr'][0]['CPM_CEK_PIHAK_KETIGA'] == 'true') ? 'checked="checked"' : ''; ?>>
                            <?php } ?>
                            <input class="form-control" name="PAJAK_ATR [CPM_NILAI_PIHAK_KETIGA][]" placeholder="Nilai Pihak Ketiga" type="text" id="CPM_NILAI_PIHAK_KETIGA" style="width: 90%; display: inline-block" readonly="readonly" value="<?php echo $DATA['pajak_atr'][0]['CPM_NILAI_PIHAK_KETIGA'] ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Biaya Tarif Pajak</label>
                        <input name="PAJAK_ATR[CPM_ATR_BIAYA][]" placeholder="Biaya Tarif Pajak" type="text" class="form-control number" style="width: 100%" id="CPM_ATR_BIAYA" readonly value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BIAYA'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Harga Dasar Ketinggian</label>
                        <input name="PAJAK_ATR[CPM_ATR_HARGA_DASAR_TIN][]" placeholder="Biaya Harga Dasar" type="text" class="form-control number" style="width: 100%" id="CPM_ATR_HARGA_DASAR_TIN" readonly value="<?= $DATA['pajak_atr'][0]['CPM_ATR_HARGA_DASAR_TIN'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Judul reklame <b class="isi">*</b></label>
                        <textarea class="form-control" name="PAJAK_ATR[CPM_ATR_JUDUL][]" id="CPM_ATR_JUDUL" tabindex="22" rows="3" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Judul Reklame"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_JUDUL'] ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Lokasi <b class="isi">*</b></label>
                        <?php
                        $timestamp = strtotime($DATA['pajak']['CPM_TGL_JATUH_TEMPO']);
                        $formattedDate = date('Y-m-d', $timestamp);
                        $formattedDate = $formattedDate == '1970-01-01' ? 'YYYY-MM-DD' : $formattedDate;
                        ?>
                        <textarea class="form-control" name="PAJAK_ATR[CPM_ATR_LOKASI][]" id="CPM_ATR_LOKASI" tabindex="23" rows="3" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Lokasi"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_LOKASI'] ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tgl Jatuh Tempo <sup style="color:red; font-size:10px">(Tidak wajib)</sup></label>
                        <input type="date" name="PAJAK[CPM_TGL_JATUH_TEMPO]" id="CPM_TGL_JATUH_TEMPO" class="form-control" value="<?= $formattedDate ?>" placeholder="Masa Akhir" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="row" style="margin-top: 20px">
                        <div class="col-md-12">
                            <div class="lm-subtitle-md">Dimensi Reklame</div>
                            <hr />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Panjang <b class="isi">*</b></label>
                                <label id="load-type_1"></label>
                                <input name="PAJAK_ATR[CPM_ATR_PANJANG][]" type="text" class="form-control number" style="width: 100%" tabindex="18" id="CPM_ATR_PANJANG" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_PANJANG'] ?>" size="11" maxlength="11" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Panjang">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">

                                <label>Lebar <b class="isi">*</b></label>
                                <input name="PAJAK_ATR[CPM_ATR_LEBAR][]" tabindex="19" type="text" class="form-control number" style="width: 100%" id="CPM_ATR_LEBAR" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_LEBAR'] ?>" size="11" maxlength="11" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Lebar" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Muka <b class="isi">*</b></label>
                                <input name="PAJAK_ATR[CPM_ATR_MUKA][]" tabindex="19" type="text" class="form-control number" style="width: 100%" id="CPM_ATR_MUKA" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_MUKA'] ?>" size="11" maxlength="11" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Muka" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tinggi <b class="isi">*</b></label>
                                <input name="PAJAK_ATR[CPM_ATR_TINGGI][0]" class="form-control number" style="width: 100%" tabindex="3" type="text" id="CPM_ATR_TINGGI" size="11" maxlength="11" value="<?= $DATA['pajak_atr'][0]['CPM_ATR_TINGGI'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Tinggi" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="ID_JAM">Waktu Tayang <b class="isi">*</b></label>
                                <input class="form-control" name="PAJAK_ATR[CPM_ATR_JAM][]" tabindex="3" type="text" class="number" style="width: 100%" id="CPM_ATR_JAM" size="11" minlength="" maxlength="11" onkeypress="" value="<?= $DATA['pajak_atr'][0]['CPM_ATR_JAM'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Jam/Hari" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alkohol/Rokok</label>
                                <div style="display: block">
                                    <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][0]" class="CPM_ALKOHOL_ROKOK" value="1" <?= ($lapor->_id != "" && $lapor->_s != 1) ? 'onclick:javascript:return false"' : '' ?> <?= $DATA['pajak_atr'][0]['CPM_ATR_ALKOHOL_ROKOK'] == '1' ? 'checked' : '' ?> /> Ya</label> &nbsp;
                                    <label style="margin-left: 10px"><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][0]" class="CPM_ALKOHOL_ROKOK" value="0" <?= ($lapor->_id != "" && $lapor->_s != 1) ? 'onclick:javascript:return false"' : '' ?> <?= $DATA['pajak_atr'][0]['CPM_ATR_ALKOHOL_ROKOK'] == '0' ? 'checked' : '' ?> id="ForCheck2" /> Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Pilih Rumus</label>
                                <select class="form-control text-center" onChange="calculation()" name="PAJAK_ATR[RMS][]" id="RMS">
                                    <?php
                                    $rumus = $DATA['pajak_atr'][0]['CPM_RUMUS'];
                                    if ($rumus == 'RMS1') {
                                        $rms = 'RUMUS 1';
                                    } else {
                                        $rms = 'RUMUS 2';
                                    }
                                    if (in_array($lapor->_mod, array("pel", ""))) {
                                        if (!in_array($lapor->_i, array(1, 3, ""))) {
                                            echo "<option value='{$rumus}' selected>{$rms}</option>";
                                        } else {
                                            echo '<option value="">--PILIH RUMUS--</option>
                                        <option value="RMS1">RUMUS 1</option>
                                        <option value="RMS2">RUMUS 2</option>';
                                        }
                                    } else {
                                        echo "<option value='{$rumus}' selected>{$rms}</option>";
                                    }
                                    ?>

                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="row" style="margin-top: 20px">
                        <div class="col-md-12">
                            <div class="lm-subtitle-md">Qty</div>
                            <hr />
                        </div>
                    </div>
                    <label id="load-type_1"></label>
                    <div class="form-group">
                        <input name="PAJAK_ATR[CPM_ATR_JUMLAH][]" type="text" class="form-control number" style="width: 100%" tabindex="21" id="CPM_ATR_JUMLAH" value="<?php echo isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'] : 1 ?>" size="11" maxlength="11" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Jumlah">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="row" style="margin-top: 20px">
                        <div class="col-md-12">
                            <div class="lm-subtitle-md">Jangka Waktu</div>
                            <hr />
                        </div>
                    </div>
                    <div class="form-group">
                        <span id="jangka-waktu" style="display: block; padding: 10px; width: 100%; text-align: center;"><?php echo  $DATA['pajak']['CPM_MASA_PAJAK'] . " " . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12" id="area_perhitungan">
                    <?php
                    if ($lapor->_id != "" && $lapor->_s != 1) {
                        $html = "<div style='font-weight:bold;padding:5px;font-style:italic;width:100%!important;text-align:left;'>";
                        $html .= "<div style='border-bottom:2px #999 dashed;padding:5px'>";
                        $html .= "<table class=\"table\" width='100%'><tr><td>";
                        $html .= 'Luas Reklame : ' . number_format($DATA['pajak_atr'][0]['CPM_ATR_LEBAR'] * $DATA['pajak_atr'][0]['CPM_ATR_PANJANG'], 0) . " m<sup>2</sup> <br/>";
                        if ($DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == '4.1.1.4.12.1' || $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == '4.1.1.4.07.1' || $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == '4.1.1.4.08.1') {
                            $html .= 'Luas : ' . number_format($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'], 0) . " <br/>";
                        } else {
                            $html .= 'Tinggi : ' . number_format($DATA['pajak_atr'][0]['CPM_ATR_TINGGI'], 0) . " m <br/>";
                        }
                        $html .= 'Lama : ' . $DATA['pajak']['CPM_MASA_PAJAK'] . ' ' . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] . " <br/>";
                        $html .= 'Nilai Strategis : ' . number_format($DATA['pajak_atr'][0]['CPM_ATR_NILAI_STRATEGIS'], 0) . " <br/>";
                        $html .= "</td></tr></table>";
                        $html .= "</div>";
                        $html .= "<table class=\"table\" width='100%'><tr><td style='background:#CCC;font-size:12px!important'>";
                        $html .= $DATA['pajak_atr'][0]['CPM_ATR_PERHITUNGAN'];
                        $html .= "</td></tr></table>";
                        $html .= "</div>";

                        echo $html;
                    }
                    ?>
                </div>
            </div>

            <label id="perhitungan_1"></label>
        </div>

                <div class="atr_reklame">
                    <?php
                    $idx = 0;
                    if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) {
                        $readonly = '';
                    } else {
                        $readonly = ' readonly';
                    }
                    if (count($DATA['pajak_atr']) > 1) {
                        unset($DATA['pajak_atr'][0]);
                        $no = 2;
                        foreach ($DATA['pajak_atr'] as $atr) {
                            if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel") || ($lapor->_s == "7" && $lapor->_mod == "pel") || ($lapor->_s == "8" && $lapor->_mod == "pel")) {
                                // $hapus = '<button type="button" id="btn-hapus-' . $no . '" onclick="hapusDetail(' . $no . ')">Hapus</button>';
                            } else {
                                $hapus = '';
                            }

                            echo '<div class="row child" id="atr_rek-' . $no . '" style="background-color: #e0f7fa; margin:16px 0 0 0; padding: 15px; border: 1px solid #007acc; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="lm-subtitle-md">Reklame</div>
                                    <hr />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pilih OP <b class="isi">*</b></label>';

                                        if (empty($DATA['pajak']['CPM_ID'])) {
                                            echo '<select name="PAJAK_ATR[CPM_ATR_NOP][]" tabindex="14" id="CPM_NOP-' . $no . '" class="form-control"><option value="" disabled>Pilih NOP</option>';

                                            foreach ($DATA['list_nop'] as $list) {
                                                $alamat = !empty($list['CPM_ALAMAT_OP']) ? $list['CPM_ALAMAT_OP'] . ', ' : '';
                                                $kel = !empty($list['CPM_KELURAHAN']) ? $list['CPM_KELURAHAN'] . ', ' : '';
                                                echo "<option value='{$list['CPM_ID']}' " . ($atr['CPM_ATR_ID_PROFIL'] == $list['CPM_ID'] ? 'selected' : '') . ">{$list['CPM_NOP']} - {$list['CPM_NAMA_OP']} | {$alamat}{$kel}Kec. {$list['CPM_KECAMATAN']}</option>";
                                            }

                                            echo '</select>';
                                        } else {
                                            echo '<input type="text" class="form-control" value="', $atr['CPM_NOP'], ' - ', $atr['CPM_NAMA_OP'], '" readonly /><input type="hidden" name="PAJAK_ATR[CPM_ATR_NOP][]" id="CPM_NOP-' . $no . '" value="', $atr['CPM_ATR_ID_PROFIL'], '" readonly />';
                                        }

                                        echo '                                    
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">        
                                        <label>Pilih rekening <b class="isi">*</b></label>
                                        <select class="form-control" tabindex="' . ($idx + 1) . '" name="PAJAK_ATR[CPM_ATR_REKENING][]" onchange="rekDetail(' . $no . ')" id="CPM_ATR_REKENING-' . $no . '">';
                                foreach ($list_rekening as $rek) {
                                    $selected = $atr['CPM_ATR_REKENING'] == $rek->kdrek ? ' selected' : '';
                                    $disabled = (empty($DATA['pajak']['CPM_ID']) || $atr['CPM_ATR_REKENING'] == $rek->kdrek) ? '' : ' disabled';
                                    echo "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
                                            data-tinggi='{$rek->tarif3}'{$selected}{$disabled}>{$rek->kdrek} - {$rek->nmrek}</option>";
                                }
                                echo '</select>                     
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">     
                                        <label>Jenis Waktu Pemakaian</label>
                                        <select id="CPM_ATR_TYPE_MASA-' . $no . '" tabindex="' . ($idx + 2) . '" onchange="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]" class="form-control">';
                                        echo "<option value='{$atr['CPM_ATR_TYPE_MASA']}' selected>{$list_type_masa[$atr['CPM_ATR_TYPE_MASA']]}</option>";
                                        echo '</select>                        
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">      
                                        <label>Lokasi Reklame</label>
                                        <input name="PAJAK_ATR[CPM_ATR_JALAN][]" class="form-control" type="text" value="' . $atr['CPM_ATR_JALAN'] . '" id="CPM_ATR_JALAN-' . $no . '" readonly />                       
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">       
                                        <label>Biaya Tarif Pajak</label>
                                        <input name="PAJAK_ATR[CPM_ATR_BIAYA][]" class="form-control" placeholder="Biaya Tarif Pajak" type="text" class="number" value="' . $atr['CPM_ATR_BIAYA'] . '" id="CPM_ATR_BIAYA-' . $no . '" readonly />                 
                                    </div>
                                </div>                                
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">         
                                        <label>Judul reklame <b class="isi">*</b></label>
                                        <textarea name="PAJAK_ATR[CPM_ATR_JUDUL][]" id="CPM_ATR_JUDUL-' . $no . '" tabindex="' . ($idx + 8) . '" class="form-control" placeholder="Judul Reklame" ' . $readonly . '>' . $atr['CPM_ATR_JUDUL'] . '</textarea>                    
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">         
                                        <label>Lokasi <b class="isi">*</b></label>
                                        <textarea name="PAJAK_ATR[CPM_ATR_LOKASI][]" id="CPM_ATR_LOKASI-' . $no . '" tabindex="' . ($idx + 9) . '" class="form-control" placeholder="Lokasi" ' . $readonly . '>' . $atr['CPM_ATR_LOKASI'] . '</textarea>                    
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row" style="margin-top: 20px">
                                        <div class="col-md-12">
                                            <div class="lm-subtitle-md">Dimensi Reklame</div>
                                            <hr />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Panjang <b class="isi">*</b></label>
                                                <label id="load-type-' . $no . '"></label>
                                                <input name="PAJAK_ATR[CPM_ATR_PANJANG][]" type="text" class="form-control number" style="width: 100%" tabindex="' . ($idx + 4) . '" id="CPM_ATR_PANJANG-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_PANJANG'] . '" ' . $readonly . ' placeholder="Panjang" onkeyup="hitungDetail(' . $no . ')" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Lebar <b class="isi">*</b></label>                                
                                                <input name="PAJAK_ATR[CPM_ATR_LEBAR][]" tabindex="' . ($idx + 5) . '" type="text" class="form-control number" style="width: 100%" id="CPM_ATR_LEBAR-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_LEBAR'] . '" ' . $readonly . ' placeholder="Lebar" onkeyup="hitungDetail(' . $no . ')" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Muka <b class="isi">*</b></label>
                                                <label id="load-type-' . $no . '"></label>
                                                <input name="PAJAK_ATR[CPM_ATR_MUKA][]" type="text" class="form-control number" style="width: 100%" tabindex="' . ($idx + 4) . '" id="CPM_ATR_MUKA-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_MUKA'] . '" ' . $readonly . ' placeholder="MUuka" onkeyup="hitungDetail(' . $no . ')" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tinggi <b class="isi">*</b></label>
                                                <label id="load-type-' . $no . '"></label>
                                                <input name="PAJAK_ATR[CPM_ATR_TINGGI][]" type="text" class="form-control number" style="width: 100%" tabindex="' . ($idx + 4) . '" id="CPM_ATR_TINGGI-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_TINGGI'] . '" ' . $readonly . ' placeholder="Tinggi" onkeyup="hitungDetail(' . $no . ')" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Alkohol/Rokok</label>
                                                <div style="display: block">
                                                    <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="1" ' . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == '1' ? 'checked' : '') . ' onclick="hitungDetail(' . $no . ')" /> Ya</label> &nbsp;
                                                    <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="0" ' . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == '0' ? 'checked' : '') . ' onclick="hitungDetail(' . $no . ')" /> Tidak</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Pilih Rumus</label>
                                                <select class="form-control text-center" onChange="hitungDetail(' . $no . ')" name="name="PAJAK_ATR[RMS][' . ($no - 1) . ']" id="RMS-' . $no . '">';
                                                $rumus =$atr['CPM_RUMUS'];
                                                if ($rumus == 'RMS1') {
                                                    $rms = 'RUMUS 1';
                                                }else{
                                                    $rms = 'RUMUS 2';
                                                }
                                                if (in_array($lapor->_mod, array("pel", ""))) {
                                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                                        echo "<option value='{$rumus}' selected>{$rms}</option>";
                                                    }
                                                } else {
                                                    echo "<option value='{$rumus}' selected>{$rms}</option>";
                                                }
                                                echo  ' </select>                                            
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="row" style="margin-top: 20px">
                                        <div class="col-md-12">
                                            <div class="lm-subtitle-md">Jumlah (Qty)</div>
                                            <hr />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input name="PAJAK_ATR[CPM_ATR_JUMLAH][]" type="text" class="form-control number" style="width: 90%; display: inline-block" tabindex="' . ($idx + 7) . '" id="CPM_ATR_JUMLAH-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_JUMLAH'] . '" ' . $readonly . ' placeholder="Jumlah" onkeyup="hitungDetail(' . $no . ')" />
                                                <b class="isi">*</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="row" style="margin-top: 20px">
                                        <div class="col-md-12">
                                            <div class="lm-subtitle-md">Jangka Waktu</div>
                                            <hr />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                            <span id="jangka-waktu-' . $no . '"><span id="jangka-waktu" style="display: block; padding: 10px; width: 100%; text-align: center;">' . intval($DATA['pajak']['CPM_MASA_PAJAK']) . ' ' . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] . '</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <tr>
                                <td align="left" colspan="4" rowspan="6" valign="top">
                                    <div id="area_perhitungan-' . $no . '">';
                                    $html = "<div style='border-bottom:2px #999 dashed;padding:5px'>";
                                    $html .= "<table width='100%'><tr><td>";
                                    $html .= 'Luas Reklame : ' . number_format($atr['CPM_ATR_PANJANG'] * $atr['CPM_ATR_LEBAR'], 2) . " M<sup>2</sup> <br/>";
                                    $html .= 'Durasi : ' . intval($DATA['pajak']['CPM_MASA_PAJAK']) . ' ' . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] . " <br/>";
                                    $html .= 'Tarif Pajak : ' .  number_format($atr['CPM_ATR_TARIF'], 0) . "% <br/>";
                                    $html .= 'Besaran Pajak : Rp. ' . number_format($atr['CPM_ATR_HARGA'], 0) . " / {$DATA['pajak']['CPM_JNS_MASA_PAJAK']}<br/>";
                                    $html .= "</td></tr></table>";
                                    $html .= "</div><div style='background:#CCC;font-size:12px!important;padding:4px'>";
                                    $html .= $atr['CPM_ATR_PERHITUNGAN'];
                                    $html .= "</div>";
        
                                    echo $html;
                                    echo '</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" align="right" valign="top">
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" id="CPM_ATR_ID-' . $no . '" value="' . $atr['CPM_ATR_ID'] . '" />
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TARIF][]" id="CPM_ATR_TARIF-' . $no . '" value="' . $atr['CPM_ATR_TARIF'] . '" />
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL-' . $no . '" value="' . $atr['CPM_ATR_TOTAL'] . '" />
                                    
                                </td>
                            </tr>
                            ' . $hapus . '
                        </div>';
                            $no++;
                        }
                    } ?>
                </div>

        <input type="hidden" id="count" value="<?php echo $count ?>" />
        <?php if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) { ?>
            <div style="text-align:center;padding:10px">
                <?php
                // if (empty($DATA['pajak']['CPM_NO'] ) || $DATA['pajak']['CPM_NO'] == '') { 

                ?>
                <button type="button" class="btn btn-primary lm-btn btn-tambah" style="font-size: large !important" onclick="myFunction2()"><i class="fa fa-plus" aria-hidden="true"></i> Tambah</button>
                <!-- <button type="button" class="btn btn-info" onclick="playSound()">Rugi Dong</button> -->
                <?php
                // } 
                ?>
            </div>
        <?php } ?>

        <hr />

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Pembayaran Pemakaian Objek Pajak</label>
                    <input style="width: 100%" name="PAJAK[CPM_TOTAL_OMZET]" type="text" class="form-control number" id="CPM_TOTAL_OMZET" value="<?php echo  $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Pemakaian Objek Pajak">
                    <input style="width: 100%" name="tab" type="hidden" class="form-control number" id="tab" value="<?php echo  $lapor->_s ?>" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x
                        Bulan keterlambatan</label>
                    <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" class="form-control" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? ($editable_terlambat_lap == 1 ? "" : "readonly")  : "readonly"; ?> class="SUM number" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>SK Pengurangan</label>
                    <div id="formGroup" class="row" style="margin: 10px 0; display: flex; align-items: center">
                        <input type="checkbox" id="HITUNG_PENGURANGAN" style="margin: 0 5px">
                        <input name="PAJAK[CPM_DISCOUNT]" type="text" class="form-control number" style="width: 100%; margin: 0 5px" id="CPM_DISCOUNT" value="<?php echo  $DATA['pajak']['CPM_DISCOUNT'] ?>" maxlength="17" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> readonly placeholder="SK Pengurangan">
                        <label style="margin: 0 5px">%</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Pembulatan</label>
                    <input style="width: 100%" name="PAJAK[CPM_PEMBULATAN]" type="text" class="form-control number" id="CPM_PEMBULATAN" value="<?php echo $pembulanatan ?>" maxlength="17" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Persentase Pengurangan">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Total Pembayaran</label>
                    <input style="width: 100%" name="PAJAK[CPM_TOTAL_PAJAK]" type="text" class="form-control number" id="CPM_TOTAL_PAJAK" value="<?php echo  $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" readonly placeholder="Total Pembayaran">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Terbilang</label>
                    <span id="terbilang" style="display: block; text-align: center"><?php echo  ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'] - $DATA['pajak']['CPM_TOTAL_PAJAK'] + $DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah" ?></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php
                echo $ttd_penanggung_jawab;
                ?>
            </div>
        </div>
        <?php

        echo '<tr>
                <td>
                    <!-- Button trigger modal -->

                    <!-- Modal -->
                    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <b class="modal-title" id="exampleModalLongTitle" style="font-size:16px">Berkas-berkas yang sudah diupload</b>

                          </div>
                          <div class="modal-body"><table>';
        echo '<tr><td><b style="font-size:14px">1. SPTPD' . '</b></td><td><b style="font-size:14px"> : <input type="button" class="btn-print" action="print_sptpd" value="View SPTPD"></b></td></tr>';
        echo '<tr><td><b style="font-size:14px">2. NPWP/NPWPD' . '</b></td><td><b style="font-size:14px"> : ' . getImage(8, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '<tr><td><b style="font-size:14px">3. Laporan Omzet Harian' . '</b></td><td><b style="font-size:14px"> : ' . getImage(2, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '<tr><td><b style="font-size:14px">4. Bon Bill' . '</b></td><td><b style="font-size:14px"> : ' . getImage(3, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '<tr><td><b style="font-size:14px">5. SK Pengurangan' . '</b></td><td><b style="font-size:14px"> : ' . getImage(9, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '</table></div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                </td>
            </tr>';

        if ($lapor->_mod == "ver" && $lapor->_s == 2) {
            $berkass = getIDBerkas($DATA['pajak']['CPM_NO']);
            $base644 = "a=aPatda&m=mPatdaPelayanan&f=fPatdaBerkas&id={$berkass}&sts=0&read=1";
            $urlberkas = "main.php?param=" . base64_encode($base644);
            $linkberkas = "<a href=\"{$urlberkas}\" title=\"Klik untuk detail\"> <button type=\"button\" class=\"btn btn-success\" style=\"float: right; margin: 5px; 0\">Upload Berkas</button></a>";

            echo "<div class=\"row\">
                    <div class=\"col-md-12\">
                        <div class=\"lm-subtitle\">VERIFIKASI</div>
                        <hr/>
                    </div>
                </div>
                <div class=\"row\">
                    <div class=\"col-md-12\">
                        <div class=\"form-group\">
                            <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                            <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>
                            <button type=\"button\"  data-toggle=\"modal\" data-target=\"#exampleModalCenter\" class=\"btn btn-primary\" style=\"float: right; margin: 5px; 0\">
                            Berkas
                            </button>
                            {$linkberkas}
                            <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                        </div>
                    </div>
                </div>";
        } else if ($lapor->_mod == "per" && $lapor->_s == 3) {
            echo "<div class=\"row\">
                    <div class=\"col-md-12\">
                        <div class=\"lm-subtitle\">PERSETUJUAN</div>
                        <hr/>
                    </div>
                </div>
                <div class=\"row\">
                    <div class=\"col-md-12\">
                        <div class=\"form-group\">
                            <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                            <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>
                            <button type=\"button\"  data-toggle=\"modal\" data-target=\"#exampleModalCenter\" class=\"btn btn-primary\" style=\"float: right; margin: 5px; 0\">
                            Berkas
                            </button>
                            <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                        </div>
                    </div>
                </div>";
        }
        ?>

        <div class="row button-area">
            <div class="col-md-12" align="center">
                <?php
                if (in_array($lapor->_mod, array("pel", ""))) {
                    if (in_array($lapor->_s, array(1, ""))) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        if ($edit) {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"update_final\" value=\"Perbaharui dan Finalkan\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">";
                        } else {
                            // if ($DATA['jml_tunggak'] < 1) {
                            //     echo "<input type=\"button\" class=\"tunggakan btn-submit\" value=\"Simpan\">\n
                            //     <input type=\"button\" class=\"tunggakan btn-submit\" value=\"Simpan dan Finalkan\">";
                            // } else {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">\n
                                <input type=\"button\" class=\"btn-submit\" action=\"save_final\" value=\"Simpan dan Finalkan sadas\">";
                            // }
                            echo '
						<script>
								document.getElementById("ForCheck").checked = true;
								document.getElementById("ForCheck2").checked = true;
								document.getElementById("ForCheck3").checked = true;
                                document.getElementById("ForCheck4").checked = true;
                                document.getElementById("ForCheck5").checked = true;
                                document.getElementById("ForCheck6").checked = true;
                                document.getElementById("ForCheck7").checked = true;
                                document.getElementById("ForCheck8").checked = true;
                                document.getElementById("ForCheck9").checked = true;
						</script>
						';
                        }
                    } elseif ($lapor->_s == 4 && $lapor->_flg == 0) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"new_version\" value=\"Simpan versi Baru\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"new_version_final\" value=\"Simpan versi baru dan Finalkan\">";
                    } elseif (in_array($lapor->_s, array(3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";

                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                        // echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_bongkar\" value=\"Cetak Jaminan Bongkar\">";
                    } elseif (in_array($lapor->_s, array(2))) {
                        // echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                        // echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                        // echo "<input type=\"button\" class=\"btn-print\" action=\"update_sspd\" value=\"Perbaharui Data\">";
                        // echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_bongkar\" value=\"Cetak Jaminan Bongkar\">";
                    }
                } elseif ($lapor->_mod == "ver") {
                    if ($lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
                    if (in_array($lapor->_s, array(3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                        "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
                    }
                } elseif ($lapor->_mod == "per") {
                    if ($lapor->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "ply") {
                    if ($lapor->_s == 5 || $lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                    if ($payment_flag['payment_flag']) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpdn\" value=\"Cetak SKPDN\">";
                    }
                }

                ?>
            </div>
        </div>
    </div>
</form>

<div class="modal"></div>

<script>
    //   $(".tunggakan").click(function() {
    //         alert("Ada Tunggakan sebanyak <?= $DATA['jml_tunggak'] ?> tunggakan, mohon segera dilunasi terlebih dulu");
    //     });

    //     <?php
            //         if($DATA['jml_tunggak']>0) echo 'alert("Ada Tunggakan sebanyak '.$DATA['jml_tunggak'].' tunggakan, mohon segera dilunasi terlebih dulu");'

            //         
            ?>


    var waktu = [];
    $(document).ready(function() {
        get_hargadasar();
    })
</script>