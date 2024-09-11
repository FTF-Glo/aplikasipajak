<?php
$DIR = "PATDA-V1";
$modul = "walet";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");
if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $DATA = $lapor->get_previous_pajak($npwpd, $nop);
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

<form class="cmxform" autocomplete="off" id="form-lapor" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="param" id="param" value="<?php echo  base64_encode("a=" . $lapor->_a . "&m=" . $lapor->_m . "&f=" . $lapor->_f) ?>">
    <input type="hidden" id="persen_terlambat_lap" value="<?php echo $persen_terlambat_lap ?>">
    <input type="hidden" id="editable_terlambat_lap" value="<?php echo $editable_terlambat_lap ?>">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_VERSION]" value="<?php echo $DATA['pajak']['CPM_VERSION']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_PROFIL]" value="<?php echo $DATA['profil']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_TARIF]" value="<?php echo $DATA['tarif']['CPM_ID']; ?>">
    <?php
    if ($lapor->_id != "") {
        echo "<div class=\"message-box\">";
        echo "<div style=\"margin-button:0px;\"><b>Versi Dokumen :</b> {$DATA['pajak']['CPM_VERSION']}</div>";
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> {$lapor->arr_status[$lapor->_s]}</div>";
        echo ($lapor->_s == 4) ? "<div style=\"margin:auto\"><b>Ditolak karena :</b> {$lapor->_info}</div>" : "";
        echo ($lapor->_s == 4 && $lapor->_flg == 1) ? "<div style=\"margin:auto\"><b>Keterangan : </b>Sudah dibuatkan versi " . ($DATA['pajak']['CPM_VERSION'] + 1) . " nya</div>" : "";
        echo "</div>";
    }
    ?>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>PROFIL PAJAK AIR SARANG WALET</b>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 isi">
                <div class="form-group">
                    <?php if (!empty($npwpd)) : ?>
                        <label>NPWPD <b class="isi">*</b></label>
                        <input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" value="<?php echo Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) ?>" readonly>
                        <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                            <?php
                            if (empty($_SESSION['npwpd'])) :
                                $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f);
                            ?>
                                <input type="button" class="btn btn-primary lm-btn" style="margin-top:10px;" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo  $prm ?>'">
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else : ?>
                        <label>NPWPD <b class="isi">*</b></label>
                        <input type="hidden" id="TBLJNSPJK" value="WALET">
                        <select class="form-control" name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width: 92%;"></select>
                        <label id="loading" style="margin-left: 5px"></label>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 isi">
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
                    <textarea name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" style="min-width: 100%" rows="3" readonly placeholder="Alamat Wajib Pajak"><?php echo $DATA['profil']['CPM_ALAMAT_WP'] ?></textarea>
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
                        <a href="<?php echo $prm ?>" class="btn btn-primary lm-btn" style="margin-top:10px;" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
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
        <div class="row mt-1">
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

                        if (empty($DATA['pajak']['CPM_ID'])) echo '<a class="btn btn-primary lm-btn" style="margin-top:10px;" href="' . $prm . '">Tambah NOP</a>';
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
                            base64_encode('a=' . $a . '&m=mPatdaPelayananPelapor1&f=fPatdaPelayananLaporOP9&npwpd=' . $npwpd . '&npwpd=' . $npwpd . '&nop=' . $nop);
                    ?>
                        <a href="<?php echo $prm ?>" class="btn btn-sm btn-secondary" target="_blank" title="setelah data objek pajak diubah, refresh halaman ini (F5)">Ubah data objek pajak</a>
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
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Rekening Pajak <b class="isi">*</b></label>
                    <select name="PAJAK[CPM_REKENING]" id="CPM_REKENING" class="form-control">
                        <?php
                        if (isset($DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']])) {
                            $rek = $DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']];
                            echo "<option value='{$DATA['profil']['CPM_REKENING']}' tarif='{$rek['tarif']}' harga='{$rek['harga']}' selected>{$DATA['profil']['CPM_REKENING']} - {$rek['nmrek']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 lm-title subtitle" align="center">
                <b>LAPOR PAJAK SARANG WALET</b>
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
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>No Pelaporan Pajak <b class="isi">*</b></label>
                            <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input class=\"form-control\"type=\"text\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "<span style=\"color:red; display: block; text-align: center\">Tidak Tersedia</span>" ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Tipe Pajak <b class="isi">*</b></label>
                            <select class="form-control" style="width: 90%; display: inline-block" name="PAJAK[CPM_TIPE_PAJAK]" id="CPM_TIPE_PAJAK">
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
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Keterangan :</label>
                    <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" class="form-control" rows="4" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
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
            <div class="col-md-3">
                <div class="form-group">
                    <label>Bulan Pajak</label>
                    <select class="form-control" name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK">
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
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <label>Masa Pajak <b class="isi">*</b></label>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <input type="text" name="PAJAK[CPM_MASA_PAJAK1]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK1\"" : ""; ?> class="form-control" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK1']; ?>" placeholder="Masa Awal">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            s.d
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" name="PAJAK[CPM_MASA_PAJAK2]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK2\"" : ""; ?> class="form-control" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK2']; ?>" placeholder="Masa Akhir">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <table width="900" class="table child">
            <tr>
                <th>No.</th>
                <th>Luas Gedung m<sup>3</sup></th>
                <th>Jumlah titik sarang walet</th>
                <th>Jumlah (Kg) / Triwulan</th>
            </tr>
            <tr>
                <td align="center">1.<input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_ID']; ?>"></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_LUAS_GEDUNG][]" id="CPM_ATR_LUAS_GEDUNG" class="form-control number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][0]['CPM_ATR_LUAS_GEDUNG']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_JUMLAH_TITIK][]" id="CPM_ATR_JUMLAH_TITIK" class="form-control number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_TITIK']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_JUMLAH_KG][]" id="CPM_ATR_JUMLAH_KG" class="form-control JUMLAH number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_KG']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
            </tr>
            <tr>
                <td align="center">2.<input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" value="<?php echo $DATA['pajak_atr'][1]['CPM_ATR_ID']; ?>"></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_LUAS_GEDUNG][]" id="CPM_ATR_LUAS_GEDUNG" class="form-control number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][1]['CPM_ATR_LUAS_GEDUNG']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_JUMLAH_TITIK][]" id="CPM_ATR_JUMLAH_TITIK" class="form-control number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][1]['CPM_ATR_JUMLAH_TITIK']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_JUMLAH_KG][]" id="CPM_ATR_JUMLAH_KG" class="form-control JUMLAH number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][1]['CPM_ATR_JUMLAH_KG']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
            </tr>
            <tr>
                <td align="center">3.<input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" value="<?php echo $DATA['pajak_atr'][2]['CPM_ATR_ID']; ?>"></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_LUAS_GEDUNG][]" id="CPM_ATR_LUAS_GEDUNG" class="form-control number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][2]['CPM_ATR_LUAS_GEDUNG']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_JUMLAH_TITIK][]" id="CPM_ATR_JUMLAH_TITIK" class="form-control number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][2]['CPM_ATR_JUMLAH_TITIK']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                <td align="center"><input type="text" name="PAJAK_ATR[CPM_ATR_JUMLAH_KG][]" id="CPM_ATR_JUMLAH_KG" class="form-control JUMLAH number" style="width: 100%" value="<?php echo ($DATA['pajak_atr'][2]['CPM_ATR_JUMLAH_KG']) ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
            </tr>
        </table>
        <hr />
        <div class="alert alert-primary" style="margin: 0">
            <label style="margin: 0">
                <input type="checkbox" id="HITUNG_DARI_KETETAPAN">
                Berdasarkan ketetapan
            </label>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?php
                    if ($DATA['pajak']['HARGA_DASAR_ENABLE'] == 1) {
                        echo "
								<label>Harga Dasar <b class=\"isi\">*</b></label>
                                <input type=\"text\" readonly name=\"PAJAK[CPM_HARGA_DASAR]\" id=\"CPM_HARGA_DASAR\" class=\"form-control number\" style=\"width: 100%\" maxlength=\"19\" value=\"{$DATA['pajak']['CPM_HARGA_DASAR']}\" placeholder=\"Harga Dasar\">";
                    } else {
                        echo "<input type=\"hidden\" name=\"PAJAK[CPM_HARGA_DASAR]\" id=\"CPM_HARGA_DASAR\" value=\"1\">";
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-<?= ($DATA['pajak']['HARGA_DASAR_ENABLE'] == 1) ? "6" : "12" ?>">
                <div class="form-group">
                    <label>Pembayaran Objek Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TOTAL_OMZET]" id="CPM_TOTAL_OMZET" style="width: 100%" class="form-control number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Pemakaian">
                </div>
            </div>
            <div class="col-md-6" style="display:none">
                <div class="form-group">
                    <label>Pembayaran Lain-lain</label>
                    <input type="text" name="PAJAK[CPM_BAYAR_LAINNYA]" id="CPM_BAYAR_LAINNYA" style="width: 100%" class="form-control number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_BAYAR_LAINNYA'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Lain-lain">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Dasar Pengenaan Pajak (DPP)</label>
                    <input type="text" name="PAJAK[CPM_DPP]" id="CPM_DPP" style="width: 100%" class="form-control number" readonly maxlength="17" value="<?php echo $DATA['pajak']['CPM_DPP'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Dasar Pengenaan Pajak (DPP)">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tarif Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" style="width: 90%; display: inline-block" class="form-control" readonly value="<?php echo $DATA['tarif'] ?>" placeholder="Tarif Pajak"> %
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
                    <label>Sanksi Telat Lapor <?php echo ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x Bulan Keterlambatan</label>
                    <!-- <td> : <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? ($editable_terlambat_lap == 1 ? "" : "readonly")  : "readonly"; ?> class="number SUM2" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor"></td> -->
                    <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" style="width: 100%" <?php echo ($lapor->_s == 1 || $lapor->_s == "") ?  "" : "readonly" ?> class="form-control SUM2 number" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Jumlah Pajak yang dibayar <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly style="width: 100%" class="form-control number" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Terbilang : </label>
                    <input type="text" name="PAJAK[CPM_TERBILANG]" style="width: 100%" id="CPM_TERBILANG" readonly class="form-control" value="<?php echo ucwords($DATA['pajak']['CPM_TERBILANG']) ?>" placeholder="Terbilang"></label>
                </div>
            </div>
        </div>
        <?php
        if ($lapor->_mod == "ver" && $lapor->_s == 2) {
            echo "  <div class=\"row\" class=\"child\">
                        <div class=\"col-md-12\">
                            <div style=\"font-size: x-large; font-weight: bold\">VERIFIKASI</div>
                            <hr />
                        </div>
                    </div>
                    <div class=\"row\">
                        <div class=\"col-md-6\">
                            <div class=\"form-group\">
                                <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                                <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" style=\"margin-left: 10px\" value=\"0\"> Tolak</label>
                                <input type=\"text\" name=\"sptpd9\" value=\"{$DATA['pajak']["CPM_NO"]}\" hidden>
                                <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width:100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                            </div>
                        </div>
                        <div class=\"col-md-4\">
                            <div class=\"form-group\">
                                <input type=\"file\" class=\"form-control\" name=\"berkas9\" />
                                <input type=\"text\" name=\"name9\" value=\"9\" hidden>
                            </div>
                        </div>
                        <div class=\"col-md-2\">
                            <div class=\"form-group\">
                                    <input type=\"submit\" class=\"btn btn-primary\" style=\"box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1)\" name=\"upload9\" value=\"Upload\" formaction=\"function/PATDA-V1/pelayanan/upload.php\"> ";
            echo getImage(9, $DATA['pajak']['CPM_NO']);
            echo "
                            </div>
                        </div>
                    </div>";
        } elseif ($lapor->_mod == "per" && $lapor->_s == 3) {
            echo "  <div class=\"row\" class=\"child\">
                        <div class=\"col-md-12\">
                            <div class=\"lm-subtitle\">PERSETUJUAN</div>
                            <hr />
                        </div>
                    </div>
                    <div class=\"row\">
                        <div class=\"col-md-12\">
                            <div class=\"form-group\">
                                <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                                <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" style=\"margin-left: 10px\" value=\"0\"> Tolak</label>
                                <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width:100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                            </div>
                        </div>
                    </div>";
        }
        ?>
        <div class="row button-area" style="margin: 0">
            <div class="col-md-12" text-align="center">
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
                    } elseif (in_array($lapor->_s, array(2, 3, 5))) {
                        if ($lapor->_flg == 1) {
                            echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                        }
                        #echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                    }
                } elseif ($lapor->_mod == "ver") {
                    if ($lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "per") {
                    if ($lapor->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "ply") {
                    if ($lapor->_s == 5)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                }
                ?>
            </div>
        </div>
    </div>
</form>