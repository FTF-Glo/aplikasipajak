<?php
$DIR = "PATDA-V1";
$modul = "mineral";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");
if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $DATA = $lapor->get_previous_pajak($npwpd, $nop);
}
$payment_info = $lapor->get_payment_flag($DATA["pajak"]["CPM_ID"]);
// print_r($DATA['pajak']);

$masa_panen = $lapor->get_masa_panen();

$masa = !empty($DATA['pajak']['JENIS_TIPE_MASA']) ? $DATA['pajak']['JENIS_TIPE_MASA'] : 'Bulan';
if (empty($DATA['pajak']['CPM_ID'])) {
    $stgl = date('Y-m-01');
    $masa_pajak_akhir = array(
        'Triwulan' => date('t/m/Y', strtotime("$stgl + 2 month")),
        '6 Bulan' => date('t/m/Y', strtotime("$stgl + 5 month")),
        'Bulan' => date('t/m/Y'),
    );
    $DATA['pajak']['CPM_MASA_PAJAK2'] = $masa_pajak_akhir[$masa];
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
            $berkas = "<a  class='btn btn-sm btn-secondary' href ='function/PATDA-V1/pelayanan/upload/{$row['CPM_FILE_NAME']}' target='_blank'>Download/View</a>";
        }
    } else {
        $berkas = "";
    }
    return $berkas;
}
?>

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script language="javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>

<form class="cmxform" autocomplete="off" id="form-lapor" method="post" enctype="multipart/form-data" action="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="param" id="param" value="<?php echo  base64_encode("a=" . $lapor->_a . "&m=" . $lapor->_m . "&f=" . $lapor->_f) ?>">
    <input type="hidden" id="persen_terlambat_lap" value="<?php echo $persen_terlambat_lap ?>">
    <input type="hidden" id="editable_terlambat_lap" value="<?php echo $editable_terlambat_lap ?>">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_VERSION]" value="<?php echo $DATA['pajak']['CPM_VERSION']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_PROFIL]" value="<?php echo $DATA['profil']['CPM_ID']; ?>">
    <!-- tambahan aldes -->
    <input type="hidden" name="PAJAK[CPM_ID_TARIF]" value="<?php echo (empty($DATA['tarif']['CPM_ID']) ? 0 : $DATA['tarif']['CPM_ID']); ?>">

    <?php if ($lapor->_s == 4) : ?>
        <input type="hidden" name="PAJAK[DITOLAK_TGL_LAPOR]" value="<?php echo $DATA['pajak']['CPM_TGL_LAPOR']; ?>">
        <input type="hidden" name="PAJAK[DITOLAK_TGL_INPUT]" value="<?php echo $DATA['pajak']['CPM_TGL_INPUT']; ?>">
    <?php endif; ?>

    <?php
    if ($lapor->_id != "") {
        echo "<div class=\"message-box\">";
        echo "<div style=\"margin-button:0px;\"><b>Versi Dokumen :</b> {$DATA['pajak']['CPM_VERSION']}</div>";
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> {$lapor->arr_status[$lapor->_s]}</div>";
        echo ($lapor->_s == 4) ? "<div style=\"margin:auto\"><b>Ditolak karena :</b> {$lapor->_info}</div>" : "";
        echo ($lapor->_s == 4 && $lapor->_flg == 1) ? "<div style=\"margin:auto\"><b>Keterangan : </b>Sudah dibuatkan versi " . ($DATA['pajak']['CPM_VERSION'] + 1) . " nya</div>" : "";
        echo "</div>";
    }

    if (in_array($s, array(2, 3, 5))) {
        $defaultdate =  date('Y-m-d');
        $ttd_penanggung_jawab .= "
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div class=\"form-group\">
                    <label>Tanggal Pengesahan</label>
                    <input type='date' class=\"form-control\" name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' >
                </div>
            </div>
        </div>";
    }
    ?>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>PROFIL PAJAK MINERAL NON LOGAM DAN BATUAN</b>
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
                                <input type="button" value="Cari NPWPD Lainnya" class="btn btn-primary lm-btn" style="margin-top: 10px" onclick="location.href='<?php echo $prm ?>'">
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else : ?>
                        <label>NPWPD <b class="isi">*</b></label>
                        <input type="hidden" id="TBLJNSPJK" value="MINERAL">
                        <select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" style="width: 90%"></select>
                        <label id="loading"></label>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Wajib Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" class="form-control" value="<?php echo $DATA['profil']['CPM_NAMA_WP'] ?>" readonly placeholder="Nama Wajib Pajak">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Alamat Wajib Pajak <b class="isi">*</b></label>
                    <textarea name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" rows="3" style="min-width: 100%" readonly placeholder="Alamat Wajib Pajak"><?php echo $DATA['profil']['CPM_ALAMAT_WP'] ?></textarea>
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
                        <a href="<?php echo $prm ?>" target="_blank" class="btn btn-primary lm-btn" style="margin-top: 10px" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
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
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>NOP <b class="isi">*</b></label>
                    <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                        <select name="PAJAK[CPM_NOP]" id="CPM_NOP" class="form-control" onchange="javascript:selectOP()">
                            <?php
                            if (count($DATA['list_nop']) == 0) echo "<option value=''>NOP Tidak tersedia</option>";
                            else echo (empty($nop)) ? "<option value='' selected disabled>Pilih NOP</option>" : "";

                            foreach ($DATA['list_nop'] as $list) {
                                $alamat = !empty($list['CPM_ALAMAT_OP']) ? $list['CPM_ALAMAT_OP'] . ', ' : '';
                                $kel = !empty($list['CPM_KELURAHAN']) ? $list['CPM_KELURAHAN'] . ', ' : '';
                                echo "<option value='{$list['CPM_NOP']}' " . ($nop == $list['CPM_NOP'] ? 'selected' : '') . ">{$list['CPM_NOP']} - {$list['CPM_NAMA_OP']} | {$alamat}{$kel}Kec. {$list['CPM_KECAMATAN']}</option>";
                            }
                            ?>
                        </select>
                    <?php else : ?>
                        <input type="text" name="PAJAK[CPM_NOP]" id="CPM_NOP" class="form-control" value="<?php echo $DATA['profil']['CPM_NOP'] ?>" readonly placeholder="NOP">
                    <?php endif; ?>

                    <?php
                    if (!empty($DATA['profil']['CPM_NPWPD'])) {
                        $addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
                        $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';

                        if (empty($DATA['pajak']['CPM_ID'])) echo '<input type="button" class="btn btn-primary lm-btn" style="margin-top: 10px" value="Tambah NOP" onclick="location.href=\'' . $prm . '\'">';
                    } ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Objek Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" class="form-control" value="<?php echo $DATA['profil']['CPM_NAMA_OP'] ?>" readonly placeholder="Nama Objek Pajak">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Alamat Objek Pajak <b class="isi">*</b></label>
                    <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" class="form-control" style="min-width: 100%" rows="3" readonly placeholder="Alamat Objek Pajak"><?php echo $DATA['profil']['CPM_ALAMAT_OP'] ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Kecamatan Objek Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_NAMA_KECAMATAN_OP]" id="CPM_NAMA_KECAMATAN_OP" class="form-control" value="<?php echo $DATA['profil']['CPM_NAMA_KECAMATAN_OP'] ?>" readonly placeholder="Kecamatan Objek Pajak">
                    <input type="hidden" name="PAJAK[CPM_KECAMATAN_OP]" id="KECAMATAN_OP" value="<?php echo $DATA['profil']['CPM_KECAMATAN_OP'] ?>">
                    <?php
                    if (
                        !empty($npwpd) && !empty($nop) &&
                        (empty($DATA['profil']['CPM_KECAMATAN_OP']) ||
                            empty($DATA['profil']['CPM_KELURAHAN_OP'])
                        )
                    ) :
                        $prm = 'main.php?param=' .
                            base64_encode('a=' . $a . '&m=mPatdaPelayananPelapor1&f=fPatdaPelayananLaporOP4&npwpd=' . $npwpd . '&npwpd=' . $npwpd . '&nop=' . $nop);
                    ?>
                        <a href="<?php echo $prm ?>" class="btn btn-primary lm-btn" style="margin-top: 10px" target="_blank" title="setelah data objek pajak diubah, refresh halaman ini (F5)">Ubah data objek pajak</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Kelurahan Objek Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_NAMA_KELURAHAN_OP]" id="CPM_NAMA_KELURAHAN_OP" class="form-control" value="<?php echo $DATA['profil']['CPM_NAMA_KELURAHAN_OP'] ?>" readonly placeholder="Kelurahan Objek Pajak">
                    <input type="hidden" name="PAJAK[CPM_KELURAHAN_OP]" id="KELURAHAN_OP" value="<?php echo $DATA['profil']['CPM_KELURAHAN_OP'] ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" align="center" class="col-md-12 subtitle lm-title">
                <b>LAPOR PAJAK MINERAL NON LOGAM DAN BATUAN</b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
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
        </div>
        <div class="row" class="child">
            <div class="col-md-12">
                <div class="lm-subtitle">Data Pajak</div>
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>No Pelaporan Pajak <b class="isi">*</b></label>
                    <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input class=\"form-control\" type=\"text\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "<br/><span style=\"color:red;\">Tidak Tersedia</span>" ?>
                </div>
                <div class="form-group">
                    <label>Tipe Pajak <b class="isi">*</b></label>
                    <select class="form-control" name="PAJAK[CPM_TIPE_PAJAK]" id="CPM_TIPE_PAJAK" style="width: 90%; display: inline-block">
                        <?php
                        if (in_array($lapor->_mod, array("pel", ""))) {
                            if (!in_array($lapor->_i, array(1, 3, ""))) {
                                echo "<option value='{$DATA['pajak']['CPM_TIPE_PAJAK']}' selected>{$DATA['pajak']['ARR_TIPE_PAJAK'][$DATA['pajak']['CPM_TIPE_PAJAK']]}</option>";
                            } else {
                                foreach ($DATA['pajak']['ARR_TIPE_PAJAK'] as $x => $y) {
                                    echo ($x == $DATA['pajak']['CPM_TIPE_PAJAK']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                }
                            }
                        } else {
                            echo "<option value='{$DATA['pajak']['CPM_TIPE_PAJAK']}' selected>{$DATA['pajak']['ARR_TIPE_PAJAK'][$DATA['pajak']['CPM_TIPE_PAJAK']]}</option>";
                        }
                        ?>
                    </select>
                    <label id="load-tarif"></label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" rows="4" class="form-control" style="min-width: 100%" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Keterangan"><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Tahun Pajak <b class="isi">*</b></label>
                    <select class="form-control" name="PAJAK[CPM_TAHUN_PAJAK]" id="CPM_TAHUN_PAJAK">
                        <?php
                        if (in_array($lapor->_mod, array("pel", ""))) {
                            if (!in_array($lapor->_i, array(1, 3, ""))) {
                                echo "<option value='{$DATA['pajak']['CPM_TAHUN_PAJAK']}' selected>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</option>";
                            } else {
                                for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
                                    echo ($th == $DATA['pajak']['CPM_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                }
                            }
                        } else {
                            echo "<option value='{$DATA['pajak']['CPM_TAHUN_PAJAK']}' selected>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Bulan Pajak</label>
                    <select class="form-control" id="CPM_MASA_PAJAK">
                        <?php
                        if (in_array($lapor->_mod, array("pel", ""))) {
                            if (!in_array($lapor->_i, array(1, 3, ""))) {
                                echo "<option value='{$DATA['pajak']['CPM_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]}</option>";
                            } else {
                                echo "<option></option>";
                                foreach ($lapor->arr_bulan as $x => $y) {
                                    echo ($DATA['pajak']['CPM_MASA_PAJAK'] == $x) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                }
                            }
                        } else {
                            echo "<option value='{$DATA['pajak']['CPM_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]}</option>";
                        }
                        ?>
                        <input type="hidden" name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK10" readonly class="number" value="<?php echo (int) $DATA['pajak']['CPM_MASA_PAJAK'] ?>">
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label>Masa Pajak <b class="isi">*</b></label>
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" name="PAJAK[CPM_MASA_PAJAK1]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK1\"" : ""; ?> class="form-control" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK1']; ?>" placeholder="Masa Awal">
                        </div>
                        <div class="col-md-1">
                            s.d
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="PAJAK[CPM_MASA_PAJAK2]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK2\"" : ""; ?> class="form-control" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK2']; ?>" placeholder="Masa Akhir">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <?php
                    $timestamp = strtotime($DATA['pajak']['CPM_TGL_JATUH_TEMPO']);
                    $formattedDate = date('Y-m-d', $timestamp);
                    $formattedDate = $formattedDate == '1970-01-01' ? 'YYYY-MM-DD' : $formattedDate;
                    ?>

                    <label>Tanggal Jatuh Tempo <span style="color:red; font-size:10px">(Tidak wajib)</span></label>
                    <input type="date" name="PAJAK[CPM_TGL_JATUH_TEMPO]" id="CPM_TGL_JATUH_TEMPO" class="form-control" value="<?= $formattedDate ?>" placeholder="Masa Akhir" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jenis Tipe Masa</label>
                    <select class="form-control" name="PAJAK[CPM_MASA_TIPE]" id="CPM_MASA_TIPE">
                        <?php

                        if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) {
                            foreach ($masa_panen as $key => $val) {
                                $sel = $key == $masa ? ' selected' : '';
                                echo "<option value=\"{$key}\"{$sel}>{$val}</option>";
                            }
                        } else {
                            echo "<option value=\"{$DATA['pajak']['JENIS_TIPE_MASA']}\">{$DATA['pajak']['JENIS_TIPE_MASA']}</option>";
                        }
                        ?></select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Rekening Pajak <b class="isi">*</b></label>
                    <input type="text" class="form-control" name="PAJAK[CPM_REKENING]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_REKENING\"" : ""; ?> readonly value="<?php echo $DATA['pajak']['CPM_REKENING']; ?>">
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-12">
                <table width="900" class="table child">
                    <tr>
                        <th>No.</th>
                        <th>Nama Komoditas</th>
                        <th>Volume / Tonase (m<sup>3</sup>)</th>
                        <th>Harga pasar / nilai standar (m<sup>3</sup>)</th>
                    </tr>
                    <?php
                    $xx = 1;
                    foreach ($DATA['pajak_atr'] as $pajak_atr) {
                    ?>
                        <tr>
                            <td align="center"><?php echo $xx ?>.</td>
                            <td align="center">
                                <select name="PAJAK_ATR[CPM_ATR_NAMA][]" class="CPM_ATR_NAMA form-control" id="CPM_ATR_NAMA_<?php echo $xx ?>" onchange="javascript:getHarga(this, <?php echo $xx ?>)" idsum="<?php echo $xx ?>">
                                    <?php
                                    $rek = isset($DATA['pajak']['ARR_REKENING'][$pajak_atr['CPM_ATR_NAMA']]) ? $DATA['pajak']['ARR_REKENING'][$pajak_atr['CPM_ATR_NAMA']] : array();
                                    if (in_array($lapor->_mod, array("pel", ""))) {
                                        if (!in_array($lapor->_i, array(1, 3, ""))) {
                                            //{$pajak_atr['CPM_ATR_NAMA']}
                                            echo "<option value='{$pajak_atr['CPM_ATR_NAMA']}' selected>{$DATA['pajak']['ARR_REKENING'][$rek['kdrek']]['nmrek']}</option>";
                                        } else {
                                            echo "<option selected tarif='0' harga='0' value='' hidden>-- Pilih Nama Komoditas --</option>";
                                            foreach ($DATA['pajak']['ARR_REKENING'] as $rek) {
                                                //{$rek['kdrek']}
                                                echo ($rek['kdrek'] == $pajak_atr['CPM_ATR_NAMA']) ?
                                                    "<option value='{$rek['kdrek']}' tarif='{$rek['tarif']}' harga='{$rek['harga']}' selected>{$rek['nmrek']}</option>" :
                                                    "<option value='{$rek['kdrek']}' tarif='{$rek['tarif']}' harga='{$rek['harga']}'>{$DATA['pajak']['ARR_REKENING'][$rek['kdrek']]['nmrek']}</option>";
                                            }
                                        }
                                    } else {
                                        //{$pajak_atr['CPM_ATR_NAMA']}
                                        echo "<option value='{$pajak_atr['CPM_ATR_NAMA']}' selected>{$DATA['pajak']['ARR_REKENING'][$rek['kdrek']]['nmrek']}</option>";
                                    }
                                    ?>
                                </select>
                                <label id="load-harga_<?php echo $xx ?>"></label>
                            </td>
                            <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_VOLUME][]" id="CPM_ATR_VOLUME_<?php echo $xx ?>" class="form-control number" value="<?php echo $pajak_atr['CPM_ATR_VOLUME'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> onkeyup="javascript:getBayar(<?php echo $xx ?>)"></td>
                            <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_HARGA][]" id="CPM_ATR_HARGA_<?php echo $xx ?>" class="form-control number" value="<?php echo (int) $pajak_atr['CPM_ATR_HARGA'] ?>" readonly></td>
                        </tr>
                    <?php
                        $xx++;
                    }
                    ?>
                </table>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Pembayaran Objek Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TOTAL_OMZET]" id="CPM_TOTAL_OMZET" class="form-control number SUM" style="width: 100%" maxlength="17" readonly="" value="<?php echo $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" placeholder="Pembayaran Pemakaian">

                    <div style="display: none">
                        <label>Pembayaran Lain-lain</label>
                        <input type="text" name="PAJAK[CPM_BAYAR_LAINNYA]" id="CPM_BAYAR_LAINNYA" class="form-control number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_BAYAR_LAINNYA'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Lain-lain">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "<font id=\"tapbox\" class=\"alert alert-secondary\" style=\"display: block; width: 100%; margin-top: 25px; padding: 7px; text-align: center; border: 1px solid rgba(89,122,59, 0.5)\">Data Pembanding : Total Volume <label id=\"val_tracking\" style=\"margin-bottom: 0\">0</label></font>" : ""; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Dasar Pengenaan Pajak (DPP)</label>
                    <input type="text" name="PAJAK[CPM_DPP]" id="CPM_DPP" class="form-control number" style="width: 100%" readonly maxlength="17" value="<?php echo $DATA['pajak']['CPM_DPP'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Dasar Pengenaan Pajak (DPP)">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tarif Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" class="form-control" style="width: 90%; display: inline-block" readonly value="<?php echo $DATA['tarif'] ?>" placeholder="Tarif Pajak"> %
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Pembayaran Terutang (Tarif x DPP)</label>
                    <input type="text" name="PAJAK[CPM_BAYAR_TERUTANG]" id="CPM_BAYAR_TERUTANG" style="width: 100%" readonly class="form-control number" maxlength="17" value="<?php echo $DATA['pajak']['CPM_BAYAR_TERUTANG'] ?>" readonly placeholder="Pembayaran Terutang">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x Bulan keterlambatan</label>
                    <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" <?php echo ($lapor->_s == 1 || $lapor->_s == ""  || $lapor->_s == 4) ?  "" : "readonly" ?> class="form-control SUM2 number" style="width: 100%" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Jumlah Pajak yang dibayar <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="form-control number" style="width: 100%" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar">
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label>Terbilang : </label>
                    <input type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly class="form-control" style="width: 100%" value="<?php echo ucwords($DATA['pajak']['CPM_TERBILANG']) ?>" placeholder="Terbilang">
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
        if ($lapor->_mod == "ver" && $lapor->_s == 2) {
            $params = array(
                'payment_code' => $payment_info['payment_code'], // pastikan $payment_info sudah terdefinisi dan berisi data yang sesuai
                'function' => 'printKwitansi',
                'app' => 'aPatda'
            );
            $json_string = json_encode($params);
            $base64_encoded = base64_encode($json_string);
            $urlstts = "function/PATDA-V1/payment_backdate/svc-payment.php?param=" . $base64_encoded;
            if ($payment_info['payment_flag'] == 1) {
                $cetak = '<a class="btn btn-sm btn-secondary" href ="' . $urlstts . '" target="_blank">Cetak</a>';
            }
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
            // echo '<tr><td><b style="font-size:14px">1. SPTPD' . '</b></td><td><b style="font-size:14px"> : <input type="button" class="btn-print" action="print_sptpd" value="View SPTPD"></b></td></tr>';
            echo '<tr><td><b style="font-size:14px">- NPWP/NPWPD' . '</b></td><td><b style="font-size:14px"> : ' . getImage(8, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
            echo '<tr><td><b style="font-size:14px">- Laporan Omzet Harian' . '</b></td><td><b style="font-size:14px"> : ' . getImage(2, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
            echo '<tr><td><b style="font-size:14px">- Bon Bill' . '</b></td><td><b style="font-size:14px"> : ' . getImage(3, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
            echo '<tr><td><b style="font-size:14px">- STTS' . '</b></td><td><b style="font-size:14px"> : ' . $cetak . '</b></td></tr>';
            echo '</table></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"  data-dismiss="modal">Close</button>
                            </div>
                            </div>
                        </div>
                        </div>
                    </td>
                </tr>';
            echo "<div class=\"row\">
            <div class=\"col-md-12\">
                <div class=\"lm-subtitle\">VERIFIKASI</div>
                <hr/>
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-md-6 child\">
                <div class=\"form-group \">";
            // if ($payment_info['payment_flag'] == 1) {
            echo "<label style=\"margin-right: 10px\"><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>";
            // }
            echo "
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label>
                        <button type=\"button\" style=\"float:right; margin-bottom: 5px; font-size: small\" data-toggle=\"modal\" data-target=\"#exampleModalCenter\" class=\"btn btn-secondary\">
                            Berkas
                        </button>
                        <input type=\"text\" class=\"form-control\" name=\"sptpd9\" value=\"{$DATA['pajak'][" CPM_NO"]}\" hidden>
                        <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                </div>
            </div>
            <div class=\"col-md-4\">
                <div class=\"form-group \">
                    <br/>
                    <br/>
                    <input type=\"file\" name=\"berkas9\" class=\"form-control\" />
                    <input type=\"text\" class=\"form-control\" name=\"name9\" value=\"9\" hidden>
                </div>
            </div>
            <div class=\"col-md-2\">
                <div class=\"form-group \">
                    <br/>
                    <br/>
                    <input type=\"submit\" class=\"btn btn-primary lm-btn\" name=\"upload9\" value=\"Upload\" formaction=\"function/PATDA-V1/pelayanan/upload.php\">         ";
            echo getImage(9, $DATA['pajak']['CPM_NO']);
            echo "    
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
                        <div class=\"form-group \">
                            <div style=\"width: 100%\">
                                <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                                <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\" > Tolak</label>
                            </div>
                            <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                        </div>
                    </div>
                </div>";
        }
        $params = array(
            'payment_code' => $payment_info['payment_code'], // pastikan $payment_info sudah terdefinisi dan berisi data yang sesuai
            'function' => 'printKwitansi',
            'app' => 'aPatda'
        );
        $json_string = json_encode($params);
        $base64_encoded = base64_encode($json_string);
        $urlstts = "function/PATDA-V1/payment_backdate/svc-payment.php?param=" . $base64_encoded;
        if ($payment_info['payment_flag'] == 1) {
            $cetak = '<a class="btn btn-sm btn-secondary button" style="padding-top:9px" href ="' . $urlstts . '" target="_blank">STTS</a>';
        }
        ?>

        <div class="row button-area" style="margin: 0">
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
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"save_final\" value=\"Simpan dan Finalkan\">";
                        }
                    } elseif ($lapor->_s == 4 && $lapor->_flg == 0) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"new_version\" value=\"Simpan versi Baru\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"new_version_final\" value=\"Simpan versi baru dan Finalkan\">";
                    } elseif (in_array($lapor->_s, array(2, 5))) {

                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";

                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                        if ($lapor->_s == 5) {
                            echo $cetak;
                        }
                    }
                } elseif ($lapor->_mod == "ver") {
                    if ($lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "per") {
                    if ($lapor->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "ply") {
                    if ($lapor->_s == 5 || $lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                }
                ?>
            </div>
        </div>
    </div>
</form>

<div id="modalDialog"></div>
<div id="cBox" style="width: 205px;z-index:9999; height: 300px; right:2%; top: 10%; border: 1px solid gray; background-color: #eaeaea; display: none; position:fixed; overflow: auto;">
    <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
        <div style="float: left;">
            <span style="font-size: 12px;">Link Download</span>
        </div>
        <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
    </div>
    <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
</div>