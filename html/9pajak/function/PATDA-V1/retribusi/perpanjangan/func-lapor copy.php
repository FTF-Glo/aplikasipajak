<?php
$DIR = "PATDA-V1";
$modul = "reklame";
require_once("inc/payment/json.php");
require_once("inc/payment/sayit.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/perpanjangan/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");

$list_tarif = $lapor->list_tarif();
$payment_flag = $lapor->payment_flag($DATA['pajak']['CPM_ID']);
function tgl_verifikasi($id)
{
    global $DBLink;
    $res = mysqli_query($DBLink, " select CPM_TRAN_DATE from PATDA_REKLAME_DOC_TRANMAIN WHERE CPM_TRAN_STATUS = 5 AND CPM_TRAN_REKLAME_ID ='{$id}'");
    if ($data = mysqli_fetch_assoc($res)) {
        return $data;
    }
    return;
}
$tgl_tran_date = tgl_verifikasi($payment_flag['id_switching']);
$tanggal_obj = date_create_from_format('d-m-Y', $tgl_tran_date['CPM_TRAN_DATE']);
$tanggal_obj->modify('+1 year');
$tgl_jatuh_tempo = $tanggal_obj->format('Y-m-d');

// var_dump($tgl_jatuh_tempo);
// die;

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
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/perpanjangan/func-lapor.js?v=<?= date('Hi') ?>"></script>
<script type="text/javascript" src="inc/<?php echo "{$DIR}"; ?>/select2/js/select2.min.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js?v=<?= date('Hi') ?>"></script>

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
    if ($lapor->_id != "" && $_REQUEST['f'] != 'fPatdaPelayanan10') {
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
    <table class="main" width="900">
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK REKLAME</b></td>
        </tr>
        <?php if (!empty($npwpd)) : ?>
            <tr>
                <td width="200">NPWPD <b class="isi">*</b></td>
                <td>:
                    <input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) ?>" readonly>
                    <input type="text" name="PAJAK[CPM_TGL_JATUH_TEMPO]" id="CPM_TGL_JATUH_TEMPO" style="width: 200px;" value="<?= $tgl_jatuh_tempo ?>" readonly>
                    <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                        <?php
                        if (empty($_SESSION['npwpd'])) :
                            $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f);
                        ?>
                            <input type="button" class="btn btn-sm btn-secondary" style="height:30px;" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm ?>'">
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php
                    if (!empty($DATA['profil']['CPM_NPWPD']) && empty($DATA['pajak']['CPM_ID'])) {
                        $addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
                        $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';
                        echo '<input type="button" class="btn btn-sm btn-secondary" style="height:30px;" value="Tambah NOP" onclick="location.href=\'' . $prm . '\'">';
                    } ?>
                </td>
            </tr>
        <?php else : ?>
            <tr>
                <td width="200">NPWPD <b class="isi">*</b></td>
                <td>:
                    <input type="hidden" id="TBLJNSPJK" value="REKLAME">
                    <select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width:250px;"></select>
                    <label id="loading"></label>
                </td>
            </tr>
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

        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo  $DATA['profil']['CPM_NAMA_WP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="70" rows="3" readonly><?php echo  $DATA['profil']['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
        <tr>
            <td>Kecamatan Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_KECAMATAN_WP'] ?>" readonly placeholder="Kecamatan Wajib Pajak">
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
                    <a href="<?php echo $prm ?>" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
                <?php endif; ?>

            </td>
        </tr>
        <tr>
            <td>Kelurahan Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_KELURAHAN_WP'] ?>" readonly placeholder="Kelurahan Wajib Pajak"></td>
        </tr>


        <tr>
            <td colspan="2" align="center" class="subtitle"><b>LAPOR PAJAK REKLAME</b></td>
        </tr>
        <tr>
            <td colspan="2">
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
                                    <input type="button" value="Kosongkan pelaporan sebelumnya" onclick="location.href=\'' . $prm . '\'" id="btn-get_previous">
                                    <br/><br/>
                                </center>';
                        }
                    } else {

                        $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f . '&npwpd=' . $npwpd . '&nop=' . $nop . '&get_previous=1') . '#btn-get_previous';
                        echo '<center>
                                <input type="button" value="Isi form dengan pelaporan sebelumnya" onclick="location.href=\'' . $prm . '\'" id="btn-get_previous">
                                <br/><br/>
                            </center>';
                    }
                }
                ?>

                <table style="width:100%" border="0" align="center" class="child">
                    <tr>
                        <th colspan="3">Data Pajak</th>
                    </tr>
                    <tr valign="top">
                        <td>No Pelaporan Pajak <b class="isi">*</b></td>
                        <td width="10">:</td>
                        <td width="700">
                            ...
                        </td>
                        <!-- <td width="300" rowspan="5"> -->
                        <!-- Keterangan :</br>
                            <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" rows="4" tabindex="13" cols="40" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>><?php echo  $DATA['pajak']['CPM_KETERANGAN'] ?></textarea> -->
                        <!-- </td> -->
                    </tr>

                    <tr>
                        <td>Tanggal Input <b class="isi">*</b></td>
                        <td width="10">:</td>
                        <td> <input type="text" class="form-control" style="width:200px;height:30px;" name="PAJAK[SAVED_DATE]" id="SAVED_DATE1" value="<?php echo date("Y-m-d") ?>" placeholder="Tanggal Pelaporan">
                        </td>
                    </tr>
                    <tr>
                        <td width="234">Tahun Pajak <b class="isi">*</b></td>
                        <td width="10">:</td>
                        <td>
                            <select class="form-control" style="width:200px;height:30px;" name="PAJAK[CPM_TAHUN_PAJAK]" tabindex="10">
                                <?php


                                for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
                                    echo ($th == $DATA['pajak']['CPM_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                }


                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td>Masa Pajak <b class="isi">*</b></td>
                        <td width="10">:</td>
                        <td>
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AWAL][]" id='CPM_ATR_BATAS_AWAL' readonly style="width: 100px; display:inline-block; font-size:smaller;" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL'] ?>" <?php echo ($_REQUEST['f'] == 'fPatdaPelayanan10') ? "class='datepicker form-control'" : "class='form-control' readonly"; ?> placeholder="Batas Awal" tabindex="11" title="Batas Awal">
                            s/d
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AKHIR][]" id='CPM_ATR_BATAS_AKHIR' readonly style="width: 100px; display:inline-block; font-size:smaller;" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR'] ?>" <?php echo ($_REQUEST['f'] == 'fPatdaPelayanan10') ? "class='datepicker form-control'" : "class='form-control' readonly"; ?> placeholder="Batas Akhir" tabindex="12" title="Batas Akhir">

                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td width="10"></td>
                        <td>

                        </td>
                    </tr>
                </table>

                <table width="900" class="child" border="0">
                    <tr>
                        <th colspan="2">Reklame</th>
                        <th colspan="2">Dimensi Reklame 1</th>
                        <th width="80" id="label_jumlah">Qty</th>
                        <th width="111">Jangka Waktu</th>
                    </tr>
                    <tr>
                        <td align="left" width="198" valign="top">Pilih OP <b class="isi">*</b></td>
                        <td align="left" width="240" valign="top">
                            <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                                <select style="width:260px;height:30px;display:inline-block;" name="PAJAK_ATR[CPM_ATR_NOP][]" tabindex="14" id="CPM_NOP" class="CPM_NOP form-control">
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
                                <input class="form-control" style="width:260px;height:30px;display:inline-block;" type="text" style="width: 260px;" value="<?php echo $DATA['pajak_atr'][0]['CPM_NOP'], ' - ', $DATA['pajak_atr'][0]['CPM_NAMA_OP'] ?>" readonly />
                                <input type="hidden" name="PAJAK_ATR[CPM_ATR_NOP][]" id="CPM_NOP-'.$no.'" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_ID_PROFIL'] ?>" class="form-control" readonly />
                            <?php endif; ?>
                        </td>

                        <td width="110" align="left" valign="top">Panjang <b class="isi">*</b></td>
                        <td width="130" align="center" valign="top"><label id="load-type_1"></label>
                            <input class="form-control" style="width:150px;height:30px;display:inline-block;" name="PAJAK_ATR[CPM_ATR_PANJANG][]" type="text" class="number" tabindex="18" id="CPM_ATR_PANJANG" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_PANJANG'] ?>" size="11" maxlength="11" placeholder="Panjang">
                        </td>
                        <td width="110" align="left" valign="top">Qty <b class="isi">*</b></td>
                        <td rowspan="3" align="center" valign="top"><label id="load-type_1"></label>

                            <input name="PAJAK_ATR[CPM_ATR_JUMLAH][]" type="text" class="number" tabindex="21" id="CPM_ATR_JUMLAH" value="<?php echo isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'] : 1 ?>" size="11" maxlength="11" placeholder="Jumlah">
                            <b class="isi">*</b>
                        </td>
                        <td rowspan="3" align="center" valign="top">
                            <span id="jangka-waktu"><?php echo  $DATA['pajak']['CPM_MASA_PAJAK'] . " " . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Pilih rekening <b class="isi">*</b></td>
                        <td align="left" valign="top">
                            <select class="form-control" style="width:260px;height:30px;display:inline-block;" tabindex="15" name="PAJAK_ATR[CPM_ATR_REKENING][]" id="CPM_ATR_REKENING" style="width:260px">
                                <?php
                                if ($lapor->_s == "") echo "<option data-nmrek='' data-tarif='0' data-harga='0' value='' selected>Pilih Rekening</option>";
                                foreach ($list_rekening as $rek) {
                                    $selected = $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek ? ' selected' : '';
                                    $disabled = (empty($DATA['pajak']['CPM_ID']) || $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek) ? '' : ' disabled';
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
                                        data-tinggi='{$rek->tarif3}'{$selected}>{$rek->kdrek} - {$rek->nmrek}</option>";
                                    } else {
                                        echo "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
                                        data-tinggi='{$rek->tarif3}'{$selected}>{$rek->kdrek} - {$rek->nmrek}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td align="left" valign="top">Lebar <b class="isi">*</b></td>
                        <td align="center" valign="top"><input class="form-control" style="width:150px;height:30px;display:inline-block;" name="PAJAK_ATR[CPM_ATR_LEBAR][]" tabindex="19" type="text" class="number" id="CPM_ATR_LEBAR" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_LEBAR'] ?>" size="11" maxlength="11" placeholder="Lebar" /></td>

                    </tr>
                    <tr>
                        <td align="left" valign="top">Nama rekening</td>
                        <td align="left" valign="top"><span id="nama-rekening" style="text-align:left;color:black;font-weight:bold"><?php echo $DATA['pajak_atr'][0]['nmrek'] ?></span><br /><span id="warning-rekening"></span></td>
                    </tr>
                    <tr>
                        <td>Jenis Waktu Pemakaian</td>
                        <td>
                            <select class="form-control" style="height: 30px;" id="CPM_ATR_TYPE_MASA" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]">
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
                        </td>
                        <td align="left" class="CPM_ATR_TINGGI_" valign="top">Ketinggian<b class="isi">*</b></td>
                        <td align="left" class="CPM_ATR_TINGGI_" valign="top">
                            <select class="form-control" style="height: 30px;" id="CPM_ATR_TINGGI" name="PAJAK_ATR[CPM_ATR_TINGGI][]">
                                <?php
                                $type_tinggi = $DATA['pajak_atr'][0]['CPM_ATR_TINGGI'];

                                foreach ($list_type_tinggi as $key => $val) {
                                    echo "<option value='{$key}' " . ($type_tinggi == $key ? 'selected' : '') . ">$val</option>";
                                }


                                ?>
                            </select>

                    </tr>
                    <tr>
                        <td class="ID_JAM"></td>
                        <td class="ID_JAM"></td>
                        <td class="ID_JAM" align="left" valign="top">Waktu Tayang <b class="isi">*</b></td>
                        <td class="ID_JAM" align="center" valign="top">

                            <input class="form-control" style="width:150px;height:30px;display:inline-block;" name="PAJAK_ATR[CPM_ATR_JAM][]" tabindex="3" type="text" class="number" id="CPM_ATR_JAM" size="11" minlength="" maxlength="11" onkeypress="" value="<?= $DATA['pajak_atr'][0]['CPM_ATR_JAM'] ?>" placeholder="Jam/Hari" />


                        </td>
                    </tr>
                    <tr>

                        <td>Lokasi Jalan </td>
                        <td>
                            <select class="form-control" style="height: 30px;" id="CPM_ATR_JALAN_TYPE" class="CPM_ATR_JALAN_TYPE" name="PAJAK_ATR[CPM_ATR_JALAN_TYPE][]">
                                <?php
                                $jln = $DATA['pajak_atr'][0]['CPM_ATR_JALAN_TYPE'];
                                // Ubah baris berikut agar dapat menangani opsi input manual (tags)
                                if (!empty($jln) && !in_array($jln, $list_jalan_type)) {
                                    echo "<option value='{$jln}' selected>{$jln}</option>";
                                }

                                // Tambahkan opsi input manual (tags) dengan menambahkan data-jln
                                echo "<option data-jln='{$list_jalan_lok}' value='' selected></option>";
                                foreach ($list_jalan_type as $kws) {
                                    echo "<option data-jln='{$list_jalan_lok}' value='{$kws}' " . ($jln == $kws ? 'selected' : '') . ">$kws</option>";
                                }

                                ?>
                            </select>
                        </td>


                        <td class="BERADA" align="left" valign="top">Berada di <b class="isi">*</b></td>
                        <td class="BERADA" align="left" valign="top" colspan="3">

                            <label><input type="radio" name="PAJAK_ATR[CPM_ATR_BANGUNAN][]" class="CPM_BANGUNAN" value="TANAH" <?= $DATA['pajak_atr'][0]['CPM_ATR_BANGUNAN'] == 'TANAH' ? ' checked' : '' ?> <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : " onclick=\"javascript:return false\""; ?> id="ForCheck" /> DI ATAS TANAH</label> &nbsp;
                            <label><input type="radio" name="PAJAK_ATR[CPM_ATR_BANGUNAN][]" class="CPM_BANGUNAN" value="BANGUNAN" <?= $DATA['pajak_atr'][0]['CPM_ATR_BANGUNAN'] == 'BANGUNAN' ? ' checked' : '' ?> <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : " onclick=\"javascript:return false\""; ?> id="ForCheck" /> DIATAS GEDUNG / BANGUNAN</label>
                        </td>
                    </tr>
                    <!-- kawasan -->
                    <tr>
                        <td>Lokasi Reklame </td>
                        <!-- <td>
                            <input name="PAJAK_ATR[CPM_ATR_JALAN][]" style="width: 260px;" placeholder="" type="text" id="CPM_ATR_JALAN" readonly value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_JALAN'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                        </td> -->

                        <td>
                            <select class="form-control" style="width:260px;height:30px;display:inline-block;" id="CPM_ATR_JALAN" name="PAJAK_ATR[CPM_ATR_JALAN][]" class="form-control">
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
                        </td>

                        <td colspan="2">
                            Alkohol/Rokok <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][0]" class="CPM_ALKOHOL_ROKOK" value="1" <?= ($lapor->_id != "" && $lapor->_s != 1) ? 'onclick:javascript:return false"' : '' ?> <?= $DATA['pajak_atr'][0]['CPM_ATR_ALKOHOL_ROKOK'] == '1' ? 'checked' : '' ?> /> Ya</label> &nbsp;
                            <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][0]" class="CPM_ALKOHOL_ROKOK" value="0" <?= ($lapor->_id != "" && $lapor->_s != 1) ? 'onclick:javascript:return false"' : '' ?> <?= $DATA['pajak_atr'][0]['CPM_ATR_ALKOHOL_ROKOK'] == '0' ? 'checked' : '' ?> id="ForCheck2" /> Tidak</label>
                        </td>
                        <td colspan="2">
                            <label><input type="radio" name="PAJAK_ATR[CPM_ATR_GEDUNG][]" class="CPM_GEDUNG" value="LUAR" <?= $DATA['pajak_atr'][0]['CPM_ATR_GEDUNG'] == 'LUAR' ? ' checked' : '' ?> <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : " onclick=\"javascript:return false\""; ?> id="ForCheck3" /> Luar Gedung</label> &nbsp;
                            <label><input type="radio" name="PAJAK_ATR[CPM_ATR_GEDUNG][]" class="CPM_GEDUNG" value="DALAM" <?= $DATA['pajak_atr'][0]['CPM_ATR_GEDUNG'] == 'DALAM' ? ' checked' : '' ?> <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : " onclick=\"javascript:return false\""; ?> /> Dalam Gedung</label>
                        </td>

                    </tr>

                    <tr>
                        <td>Pembayaran Melalui Pihak Ketiga</td>
                        <td>
                            <div id="formGroup" style="margin-bottom: 5px; margin-top: 4px;">
                                <?php if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel") || $_REQUEST['f'] == 'fPatdaPelayanan10') {  ?>
                                    <input name="PAJAK_ATR[CPM_CEK_PIHAK_KETIGA][]" style="width: 20px;" type="checkbox" id="CPM_CEK_PIHAK_KETIGA" value="1" <?= ($DATA['pajak_atr'][0]['CPM_CEK_PIHAK_KETIGA'] == 'true') ? 'checked="checked"' : ''; ?>>
                                <?php } ?>
                                <br>
                                <label for="" id="labelPihakKetiga"><input class="form-control" name="PAJAK_ATR[CPM_NILAI_PIHAK_KETIGA][]" style="width: 230px; height: 30px;" placeholder="Nilai Pihak Ketiga" type="text" id="CPM_NILAI_PIHAK_KETIGA" readonly="readonly" value="<?php echo $DATA['pajak_atr'][0]['CPM_NILAI_PIHAK_KETIGA'] ?>"></label>
                        </td>
                        <td align="left" colspan="4" rowspan="6" valign="top">
                            <div id="area_perhitungan">
                                <?php
                                if ($lapor->_id != "" && $lapor->_s != 1) {
                                    $html = "<div style='font-weight:bold;padding:5px;font-style:italic;width:100%!important;text-align:left;'>";
                                    $html .= "<div style='border-bottom:2px #999 dashed;padding:5px'>";
                                    $html .= "<table width='100%'><tr><td>";
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
                                    $html .= "<table width='100%'><tr><td style='background:#CCC;font-size:12px!important'>";
                                    $html .= $DATA['pajak_atr'][0]['CPM_ATR_PERHITUNGAN'];
                                    $html .= "</td></tr></table>";
                                    $html .= "</div>";

                                    echo $html;
                                } ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Biaya Tarif Pajak</td>
                        <td>
                            <input name="PAJAK_ATR[CPM_ATR_BIAYA][]" style="width: 260px;" placeholder="Biaya Tarif Pajak" type="text" class="number" id="CPM_ATR_BIAYA" readonly value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BIAYA'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td>Harga Dasar Ketinggian</td>
                        <td>
                            <input name="PAJAK_ATR[CPM_ATR_HARGA_DASAR_TIN][]" style="width: 260px;" placeholder="Biaya Harga Dasar" type="text" class="number" id="CPM_ATR_HARGA_DASAR_TIN" readonly value="<?= $DATA['pajak_atr'][0]['CPM_ATR_HARGA_DASAR_TIN'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Judul reklame <b class="isi">*</b></td>
                        <td align="left" valign="top">
                            <div align="left">
                                <textarea class="form-control" name="PAJAK_ATR[CPM_ATR_JUDUL][]" id="CPM_ATR_JUDUL" tabindex="22" style="width: 260px;" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Judul Reklame"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_JUDUL'] ?></textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Lokasi <b class="isi">*</b></td>
                        <td align="left" valign="top">
                            <div align="left">
                                <textarea class="form-control" name="PAJAK_ATR[CPM_ATR_LOKASI][]" id="CPM_ATR_LOKASI" tabindex="23" style="width: 260px;" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Lokasi"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_LOKASI'] ?></textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" align="center" valign="top"><label id="perhitungan_1"></label></td>
                    </tr>
                </table>

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
                                $hapus = '<button type="button" id="btn-hapus-' . $no . '" onclick="hapusDetail(' . $no . ')">Hapus</button>';
                            } else {
                                $hapus = '';
                            }
                            echo '<table width="900" class="child" id="atr_rek-' . $no . '" border="0" style="margin-top:8px">
                            <tr>
                                <th colspan="2">Reklame</th>
                                <th colspan="2">Dimensi Reklame ' . $no . '</th>
                                <th width="80">Jumlah (Qty)</th>
                                <th width="111">Jangka Waktu</th>
                            </tr>
                            <tr>
                                <td align="left" width="198" valign="top">Pilih OP <b class="isi">*</b></td>
                                <td align="left" width="240" valign="top">';
                            if (empty($DATA['pajak']['CPM_ID'])) {
                                echo '<select name="PAJAK_ATR[CPM_ATR_NOP][]" tabindex="14" id="CPM_NOP-' . $no . '" style="max-width:260px"><option value="" disabled>Pilih NOP</option>';

                                foreach ($DATA['list_nop'] as $list) {
                                    $alamat = !empty($list['CPM_ALAMAT_OP']) ? $list['CPM_ALAMAT_OP'] . ', ' : '';
                                    $kel = !empty($list['CPM_KELURAHAN']) ? $list['CPM_KELURAHAN'] . ', ' : '';
                                    echo "<option value='{$list['CPM_ID']}' " . ($atr['CPM_ATR_ID_PROFIL'] == $list['CPM_ID'] ? 'selected' : '') . ">{$list['CPM_NOP']} - {$list['CPM_NAMA_OP']} | {$alamat}{$kel}Kec. {$list['CPM_KECAMATAN']}</option>";
                                }

                                echo '</select>';
                            } else {
                                echo '<input type="text" style="width: 260px;" value="', $atr['CPM_NOP'], ' - ', $atr['CPM_NAMA_OP'], '" readonly /><input type="hidden" name="PAJAK_ATR[CPM_ATR_NOP][]" id="CPM_NOP-' . $no . '" value="', $atr['CPM_ATR_ID_PROFIL'], '" readonly />';
                            }
                            echo '</td>
                                <td width="110" align="left" valign="top">Panjang <b class="isi">*</b></td>
                                <td width="130" align="center" valign="top"><label id="load-type-' . $no . '"></label>
                                    <input name="PAJAK_ATR[CPM_ATR_PANJANG][]" type="text" class="number" tabindex="' . ($idx + 4) . '" id="CPM_ATR_PANJANG-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_PANJANG'] . '" placeholder="Panjang" onkeyup="hitungDetail(' . $no . ')" />
                                </td>
                                <td rowspan="3" align="center" valign="top">
                                    <input name="PAJAK_ATR[CPM_ATR_JUMLAH][]" type="text" class="number" tabindex="' . ($idx + 7) . '" id="CPM_ATR_JUMLAH-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_JUMLAH'] . '"  placeholder="Jumlah" onkeyup="hitungDetail(' . $no . ')" />
                                    <b class="isi">*</b>
                                </td>
                                <td rowspan="3" align="center" valign="top">
                                    <span id="jangka-waktu-' . $no . '"><span id="jangka-waktu">' . intval($DATA['pajak']['CPM_MASA_PAJAK']) . ' ' . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] . '</span></span>
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">Pilih rekening <b class="isi">*</b></td>
                                <td align="left" valign="top">
                                    <select class="form-control" tabindex="' . ($idx + 1) . '" name="PAJAK_ATR[CPM_ATR_REKENING][]" onchange="rekDetail(' . $no . ')" id="CPM_ATR_REKENING-' . $no . '" style="width:260px">';
                            foreach ($list_rekening as $rek) {
                                $selected = $atr['CPM_ATR_REKENING'] == $rek->kdrek ? ' selected' : '';
                                $disabled = (empty($DATA['pajak']['CPM_ID']) || $atr['CPM_ATR_REKENING'] == $rek->kdrek) ? '' : ' disabled';
                                echo "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
                                        data-tinggi='{$rek->tarif3}'{$selected}>{$rek->kdrek} - {$rek->nmrek}</option>";
                            }
                            echo '</select>
                                </td>
                                <td align="left" valign="top">Lebar <b class="isi">*</b></td>
                                <td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_LEBAR][]" tabindex="' . ($idx + 5) . '" type="text" class="number" id="CPM_ATR_LEBAR-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_LEBAR'] . '"  placeholder="Lebar" onkeyup="hitungDetail(' . $no . ')" /></td>                                
                            </tr>
                            <td>Jenis Waktu Pemakaian</td>
                            <td>
                            <select id="CPM_ATR_TYPE_MASA-' . $no . '" tabindex="' . ($idx + 2) . '" onchange="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]" class="form-control">';
                            echo "<option value='{$atr['CPM_ATR_TYPE_MASA']}' selected>{$list_type_masa[$atr['CPM_ATR_TYPE_MASA']]}</option>";

                            echo '</select>
                                </td>

                                <td align="left" valign="top">Ketinggian <b class="isi">*</b></td>
                                <td align="center" valign="top">
                               
                                <select class="form-control" style="height: 30px;" id="CPM_ATR_TINGGI-' . $no . '" name="PAJAK_ATR[CPM_ATR_TINGGI][]" onkeyup="hitungDetail(' . $no . ')">';

                            $type_tinggi = $atr['CPM_ATR_TINGGI'];


                            foreach ($list_type_tinggi as $key => $val) {
                                echo "<option value='{$key}' " . ($type_tinggi == $key ? 'selected' : '') . ">$val</option>";
                            }


                            echo '</select>
                          <!--  <input name="PAJAK_ATR[CPM_ATR_TINGGI][]" tabindex="' . ($idx + 5) . '" type="text" style="width:150px" id="CPM_ATR_TINGGI-' . $no . '" size="11" maxlength="11" value="' . $atr['CPM_ATR_TINGGI'] . '" ' . $readonly . ' placeholder="Lebar" onkeyup="hitungDetail(' . $no . ')" /> --!>
                                </td>
                            </tr>
                            <tr>
                            <td>Lokasi Jalan</td>
                            <td>
                        
                            <select id="CPM_ATR_JALAN_TYPE-' . $no . '" tabindex="' . ($idx + 2) . '" onchange="hitungDetail(' . $no . ')"  style="width:260px;height:30px;display:inline-block;"  name="PAJAK_ATR[CPM_ATR_JALAN_TYPE][]" class="form-control">';
                            $jln2 = $atr['CPM_ATR_JALAN_TYPE'];

                            // Tambahkan opsi input manual (tags) dengan menambahkan data-jln
                            echo "<option data-jln='{$list_jalan_lok}' value='' selected></option>";
                            foreach ($list_jalan_type as $kws) {
                                echo "<option data-jln='{$list_jalan_lok}' value='{$kws}' " . ($jln2 == $kws ? 'selected' : '') . ">$kws</option>";
                            }
                            echo '</select>
                            
                                </td>

                                <td class="BERADA" align="left" valign="top">Berada di <b class="isi">*</b></td>
                                <td class="BERADA" align="left" valign="top" colspan="3">
                                    <label><input type="radio" name="PAJAK_ATR[CPM_ATR_BANGUNAN][' . ($no - 1) . ']" class="CPM_BANGUNAN-' . $no . '" value="TANAH" ' . ($atr['CPM_ATR_BANGUNAN'] == 'TANAH' ? ' checked' : '') . '  id="ForCheck" onclick="hitungDetail(' . $no . ')" /> DI ATAS TANAH</label> &nbsp;
                                    <label><input type="radio" name="PAJAK_ATR[CPM_ATR_BANGUNAN][' . ($no - 1) . ']" class="CPM_BANGUNAN-' . $no . '" value="BANGUNAN" ' . ($atr['CPM_ATR_BANGUNAN'] == 'BANGUNAN' ? ' checked' : '') . '  id="ForCheck" onclick="hitungDetail(' . $no . ')" /> DI ATAS GEDUNG / BANGUNAN</label>
                                </td>
                                
                            </tr>

                            <tr>
                            <td>Lokasi Reklame</td>
                            <td>
                                <input name="PAJAK_ATR[CPM_ATR_JALAN][]" style="width: 260px;" type="text" value="' . $atr['CPM_ATR_JALAN'] . '" id="CPM_ATR_JALAN-' . $no . '" readonly />
                            </td>

                            <td colspan="2">
                                Alkohol/Rokok <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="1" ' . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == '1' ? 'checked' : '') . ' onclick="hitungDetail(' . $no . ')" /> Ya</label> &nbsp;
                                <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="0" ' . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == '0' ? 'checked' : '') . ' onclick="hitungDetail(' . $no . ')" /> Tidak</label>
                            </td>

                           
                            <td colspan="2">
                                <label><input type="radio" name="PAJAK_ATR[CPM_ATR_GEDUNG][' . ($no - 1) . ']" class="CPM_GEDUNG-' . $no . '" value="LUAR" ' . ($atr['CPM_ATR_GEDUNG'] == 'LUAR' ? ' checked' : '') . '  id="ForCheck3"  onclick="hitungDetail(' . $no . ')"  /> Luar Gedung</label> &nbsp;
                                <label><input type="radio" name="PAJAK_ATR[CPM_ATR_GEDUNG][' . ($no - 1) . ']" class="CPM_GEDUNG-' . $no . '" value="DALAM" ' . ($atr['CPM_ATR_GEDUNG'] == 'DALAM' ? ' checked' : '') . '  id="ForCheck3"  onclick="hitungDetail(' . $no . ')"  /> Dalam Gedung</label>
                            </td>

                                                     

                        
                            </tr>

                            <tr>
                                <td>Biaya Tarif Pajak</td>
                                <td>
                                    <input name="PAJAK_ATR[CPM_ATR_BIAYA][]" style="width: 260px;" placeholder="Biaya Tarif Pajak" type="text" class="number" value="' . $atr['CPM_ATR_BIAYA'] . '" id="CPM_ATR_BIAYA-' . $no . '" readonly />
                                </td>
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
                                <td align="left" valign="top">Judul reklame <b class="isi">*</b></td>
                                <td align="left" valign="top"><div align="left">
                                        <textarea name="PAJAK_ATR[CPM_ATR_JUDUL][]" id="CPM_ATR_JUDUL-' . $no . '" tabindex="' . ($idx + 8) . '" style="width: 260px;" placeholder="Judul Reklame" ' . $readonly . '>' . $atr['CPM_ATR_JUDUL'] . '</textarea>
                                    </div></td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">Lokasi <b class="isi">*</b></td>
                                <td align="left" valign="top"><div align="left">
                                        <textarea name="PAJAK_ATR[CPM_ATR_LOKASI][]" id="CPM_ATR_LOKASI-' . $no . '" tabindex="' . ($idx + 9) . '" style="width: 260px;" placeholder="Lokasi" ' . $readonly . '>' . $atr['CPM_ATR_LOKASI'] . '</textarea>
                                    </div></td>
                            </tr>
                            <tr>
                                <td colspan="6" align="right" valign="top">
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" id="CPM_ATR_ID-' . $no . '" value="' . $atr['CPM_ATR_ID'] . '" />
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TARIF][]" id="CPM_ATR_TARIF-' . $no . '" value="' . $atr['CPM_ATR_TARIF'] . '" />
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL-' . $no . '" value="' . $atr['CPM_ATR_TOTAL'] . '" />
                                    
                                </td>
                            </tr>
                            ' . $hapus . '
                        </table>';
                            $no++;
                        }
                    } ?>
                </div>


                <input type="hidden" id="count" value="<?php echo $count ?>" />

                <div style="text-align:center;padding:10px">
                    <?php
                    // if (empty($DATA['pajak']['CPM_NO'] ) || $DATA['pajak']['CPM_NO'] == '') { 

                    ?>
                    <button type="button" class="btn btn-info btn-tambah" onclick="myFunction2()">Tambah</button>
                    <?php
                    // } 
                    ?>
                </div>

                </br>

                <table width="100%" border="0" align="center" class="child">
                    <?php   ?>
                    <tr>
                        <td width="250">Pembayaran Pemakaian Objek Pajak</td>
                        <td width="10">:</td>
                        <td>
                            <input style="text-align:right; width:200px; height:30px;" name="PAJAK[CPM_TOTAL_OMZET]" type="text" class="form-control number" id="CPM_TOTAL_OMZET" value="<?php echo  $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Pemakaian Objek Pajak">
                            <input style="text-align:right; width:200px; height:30px;" name="tab" type="hidden" class="form-control number" id="tab" value="<?php echo  $lapor->_s ?>" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                        </td>
                    </tr>

                    <tr>
                        <td>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x
                            Bulan keterlambatan</td>
                        <td width="10">:</td>
                        <td>

                            <input type="text" style="width:200px;height:30px;" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? ($editable_terlambat_lap == 1 ? "" : "readonly")  : "readonly"; ?> class="SUM number" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor">
                        </td>
                    </tr>

                    <tr>
                        <td>SK Pengurangan</td>
                        <td width="10">:</td>
                        <td>
                            <div id="formGroup" style="margin-bottom: 8px; margin-top: 8px;">
                                <input style="width: 20px;" type="checkbox" id="HITUNG_PENGURANGAN">
                                <br>
                                <label for="" id="labelPihakKetiga"><input style="text-align:right; width:170px; height:30px;" name="PAJAK[CPM_DISCOUNT]" type="text" class="form-control number" id="CPM_DISCOUNT" value="<?php echo  $DATA['pajak']['CPM_DISCOUNT'] ?>" maxlength="17" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> readonly placeholder="SK Pengurangan"></label>
                        </td>
                        <td>
                            %
                        </td>
                    </tr>

                    <tr>
                        <td>Pembulatan</td>
                        <td width="10">:</td>
                        <td width="10">
                            <input style="text-align:right; width:200px; height:30px;" name="PAJAK[CPM_PEMBULATAN]" type="text" class="form-control number" id="CPM_PEMBULATAN" value="<?php echo $pembulanatan ?>" maxlength="17" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Persentase Pengurangan">
                        </td>
                        <td>
                            <!-- <label>
                                <input type="checkbox" id="HITUNG_PENGURANGAN">
                                Pengurangan
                            </label> -->
                        </td>
                    </tr>
                    <tr>
                        <td>Total Pembayaran</td>
                        <td width="10">:</td>
                        <td>
                            <input style="text-align:right; width:200px; height:30px;" name="PAJAK[CPM_TOTAL_PAJAK]" type="text" class="form-control number" id="CPM_TOTAL_PAJAK" value="<?php echo  $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" readonly placeholder="Total Pembayaran">
                        </td>
                    </tr>
                    <tr>
                        <td>Terbilang</td>
                        <td>:</td>
                        <td> <span id="terbilang"><?php echo  ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'] - $DATA['pajak']['CPM_TOTAL_PAJAK'] + $DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah" ?></span>
                    </tr>
                    <?php
                    echo $ttd_penanggung_jawab;
                    ?>
                </table>

            </td>
        </tr>
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
            echo "<tr>
                    <td colspan=\"3\" align=\"center\" class=\"subtitle\"><b>VERIFIKASI</b></td>
                </tr>
                <tr>
                    <td colspan=\"3\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                                <td width=\"100\" align=\"right\" colspan=\"2\">
                                    <button type=\"button\"  data-toggle=\"modal\" data-target=\"#exampleModalCenter\" class=\"btn btn-primary\">
                                      Berkas
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan=\"3\"><textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea><br></td>
                            </tr>
                        </table>
                    </td>
                </tr>";
        } else if ($lapor->_mod == "per" && $lapor->_s == 3) {
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>PERSETUJUAN</b></td>
                </tr>
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                                <td width=\"100\" align=\"right\" colspan=\"2\">
                                    <button type=\"button\"  data-toggle=\"modal\" data-target=\"#exampleModalCenter\" class=\"btn btn-primary\">
                                      Berkas
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan=\"2\"><textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea></td>
                            </tr>
                        </table>
                    </td>
                </tr>";
        }
        ?>
        <tr class="button-area">
            <td align="center" colspan="3">
                <?php
                if (in_array($lapor->_mod, array("pel", ""))) {
                    if (in_array($lapor->_s, array(1, "")) || $_REQUEST['f'] == 'fPatdaPelayanan10') {



                        echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">\n
                                <input type=\"button\" class=\"btn-submit\" action=\"save_final_perpanjangan\" value=\"Simpan dan Finalkan\">";
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
                }
                ?>
            </td>
        </tr>
    </table>
</form>

<div class="modal"></div>

<script>
    // $(".tunggakan").click(function() {
    //     alert("Ada Tunggakan sebanyak <?= $DATA['jml_tunggak'] ?> tunggakan, mohon segera dilunasi terlebih dulu");
    // });

    // <?php
        // if ($DATA['jml_tunggak'] > 0) echo 'alert("Ada Tunggakan sebanyak ' . $DATA['jml_tunggak'] . ' tunggakan, mohon segera dilunasi terlebih dulu");'
        // 
        ?>


    var waktu = [];
    $(document).ready(function() {
        get_hargadasar();
    })
</script>