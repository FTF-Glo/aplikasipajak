<?php

$DIR = "PATDA-V1";
$modul = "retribusi";
require_once("inc/payment/json.php");
require_once("inc/payment/sayit.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");


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

// var_dump($npwpd);
if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $DATA = $lapor->get_previous_pajak($npwpd, $nop);
}



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

    #labelPihakKetiga {
        position: absolute;
        top: -8px;
        left: 30px;
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
<form class="cmxform" autocomplete="off" id="form-lapor" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
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
				<tr>
					<th colspan=\"5\">TTD Penangung Jawab</th>
				</tr>
				<tr>
					<td>Mengetahui</td>
					<td>: <select id=\"PEJABAT2\" class=\"form-control\" style=\"width:500px;height:30px;display:inline-block;\" name=\"PAJAK[CPM_PEJABAT_MENGETAHUI]\">{$opt_pejabat}</select></td>
				</tr>
				<tr>
					<td>Tanggal Pengesahan </td>
					<td>: <input type='date' class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;\" name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' ></td>
				</tr>";
    }
    ?>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>LAPOR REALISASI RETRIBUSI </b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jenis Penerimaan<b class="isi">*</b></label>
                    <select class="form-control" name="jenis_penerimaan" id="jenis_penerimaan">
                        <?php

                        $sql = "SELECT id, jenis_penerimaan FROM jenis_penerimaan_retribusi";
                        $result = mysqli_query($DBLink, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$row['id']}'>{$row['jenis_penerimaan']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jenis Retribusi<b class="isi">*</b></label>
                    <select class="form-control" name="jenis_retribusi" id="jenis_retribusi">
                        <option> -- Silahkan Pilih -- </option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Rekening<b class="isi">*</b></label>
                    <input type="text" class="form-control" id="rekening" name="rekening" placeholder="Rekening" readonly>
                    <input type="hidden" class="form-control" id="jenis_penerimaan_id" name="jenis_penerimaan_id" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Anggaran<b class="isi">*</b></label>
                    <input type="number" class="form-control" id="anggaran" name="anggaran" placeholder="Anggaran" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Target<b class="isi">*</b></label>
                    <input type="number" class="form-control" id="target" name="target" placeholder="Target" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Bulan<b class="isi">*</b></label>
                    <select class="form-control" name="bulan" id="bulan">
                        <option value="01">Januari</option>
                        <option value="02">Februari</option>
                        <option value="03">Maret</option>
                        <option value="04">April</option>
                        <option value="05">Mei</option>
                        <option value="06">Juni</option>
                        <option value="07">Juli</option>
                        <option value="08">Agustus</option>
                        <option value="09">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tahun<b class="isi">*</b></label>
                    <select class="form-control" name="tahun" id="tahun">
                        <?php
                        $currentYear = date("Y");

                        for ($th = $currentYear - 5; $th <= $currentYear; $th++) {
                            $selected = ($th == $currentYear) ? 'selected' : '';
                            $opt_tahun .= "<option value='{$th}' {$selected}>{$th}</option>";
                        }
                        echo $opt_tahun;
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Jumlah Realisasi<b class="isi">*</b></label>
                    <input type="number" class="form-control" id="jumlah_realisasi" name="jumlah_realisasi" placeholder="Jumlah Resalisasi">
                </div>
            </div>
        </div>
        <div class="row button-area">
            <div class="col-md-12" align="center" colspan="3">
                <?php
                if (in_array($lapor->_mod, array("pel", ""))) {
                    if (in_array($lapor->_s, array(1, ""))) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        if ($edit) {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"update_final\" value=\"Perbaharui dan Finalkan\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">";
                        } else {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"save_final\" value=\"Simpan dan Finalkan\">";
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
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
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
                    if ($lapor->_s == 5)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                    // echo "<input type=\"button\" class=\"btn-print\" action=\"update_sspd\" value=\"Perbaharui Data\">";
                    if ($payment_flag['payment_flag']) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpdn\" value=\"Cetak SKPDN\">";
                    }
                }

                ?>
            </td>
        </div>
    </div>
</form>

<div class="modal"></div>

<script>
    $(document).ready(function() {
        $('#jenis_penerimaan').change(function() {
            var jenis_penerimaan_id = $(this).val();
            $.ajax({
                url: 'function/PATDA-V1/retribusi/lapor/get_jenis_retribusi.php',
                type: 'POST',
                data: {
                    id: jenis_penerimaan_id
                },
                success: function(response) {
                    $('#jenis_retribusi').html(response);

                    var firstOption = $('#jenis_retribusi option:first').val();
                    $('#jenis_retribusi').val(firstOption).trigger('change');
                }
            });
        });

        $('#jenis_retribusi').change(function() {
            var jenis_retribusi_id = $(this).val();
            $.ajax({
                url: 'function/PATDA-V1/retribusi/lapor/get_detail_retribusi.php',
                type: 'POST',
                data: {
                    id: jenis_retribusi_id
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    $('#rekening').val(data.rekening);
                    $('#jenis_penerimaan_id').val(data.jenis_penerimaan);
                    $('#anggaran').val(data.anggaran);
                    $('#target').val(data.target);
                }
            });
        });
    });


    var waktu = [];
    $(document).ready(function() {
        get_hargadasar();
    })
</script>