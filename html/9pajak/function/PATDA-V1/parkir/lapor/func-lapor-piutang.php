<?php
$DIR = "PATDA-V1";
$modul = "parkir";
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

<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script language="javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor-piutang.js"></script>
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
    <input type="hidden" name="PAJAK[CPM_DEVICE_ID]" id="CPM_DEVICE_ID" value="<?php echo base64_encode($DATA['profil']['CPM_DEVICE_ID']); ?>">
    <input type="hidden" name="a" id="a" value="<?php echo $lapor->_a; ?>">
    <input type="hidden" name="PAJAK[CPM_PIUTANG]" value="1">

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
            <div id="subMenu" align="center" class="col-md-12 subtitle lm-title">
                <b>PROFIL PAJAK PARKIR</b>
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
                                <input type="button" class="btn btn-primary lm-btn" style="margin-top: 10px" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo  $prm ?>'">
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else : ?>
                        <label width="200">NPWPD <b class="isi">*</b></label>
                        <input type="hidden" id="TBLJNSPJK" value="PARKIR">
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
                        <a href="<?php echo $prm ?>" class="btn btn-primary lm-btn" style="margin-top:10px; target=" _blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Kelurahan Wajib Pajak <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" class="form-control"" value=" <?php echo $DATA['profil']['CPM_KELURAHAN_WP'] ?>" readonly placeholder="Kelurahan Wajib Pajak">
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
                        //$addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
                        $addOp = 'fPatdaPelayananLaporOP5';
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
                            base64_encode('a=' . $a . '&m=mPatdaPelayananPelapor1&f=fPatdaPelayananLaporOP5&npwpd=' . $npwpd . '&npwpd=' . $npwpd . '&nop=' . $nop);
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
            <div id="subMenu" align="center" class="col-md-12 subtitle lm-title">
                <b>LAPOR PAJAK PARKIR</b>
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
								<input type="button" class="btn btn-primary lm-btn-medium" value="Kosongkan pelaporan sebelumnya" onclick="location.href=\'' . $prm . '\'" id="btn-get_previous">
								<br/><br/>
							</center>';
                        }
                    } else {

                        $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f . '&npwpd=' . $npwpd . '&nop=' . $nop . '&get_previous=1') . '#btn-get_previous';
                        echo '<center>
							<input type="button" class="btn btn-primary lm-btn-medium" value="Isi form dengan pelaporan sebelumnya" onclick="location.href=\'' . $prm . '\'" id="btn-get_previous">
							<br/><br/>
						</center>';
                    }
                }
                ?>
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
                    <input name="PAJAK[CPM_NO]" type="text" class="form-control" id="CPM_NO" value="<?php echo $DATA['pajak']['CPM_NO'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "") ? "" : "readonly"; ?> placeholder="No Pelaporan">
                </div>
                <div class="form-group">
                    <label>Tipe Pajak <b class="isi">*</b></label>
                    <select class="form-control" name="PAJAK[CPM_TIPE_PAJAK]" id="CPM_TIPE_PAJAK">
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
                    <textarea name="PAJAK[CPM_KETERANGAN]" class="form-control" style="min-width:100%;" id="CPM_KETERANGAN" rows="5" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Keterangan"><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
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
                    <label>Bulan Pajak </label>
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
            <div class="col-md-6">
                <div class="form-group">
                    <label>Masa Pajak <b class="isi">*</b></label>
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="PAJAK[CPM_MASA_PAJAK1]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK1\"" : ""; ?> readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK1']; ?>" placeholder="Masa Awal">
                        </div>
                        <div class="col-md-1">
                            s.d
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="PAJAK[CPM_MASA_PAJAK2]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK2\"" : ""; ?> readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK2']; ?>" placeholder="Masa Akhir">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Lokasi Parkir <b class="isi">*</b></label>
                    <label style="display:block"><input type="radio" name="PAJAK[CPM_LOKASI]" class="CPM_LOKASI" value="0" <?php echo ($DATA['pajak']['CPM_LOKASI'] == 0 ? 'checked' : '') ?> <?php echo (!empty($value['CPM_USER']) ? "onclick='javascript:return true'" : '') ?>> Tempat Umum</label>
                    <label style="display:block"><input type="radio" name="PAJAK[CPM_LOKASI]" class="CPM_LOKASI" style="" value="1" <?php echo ($DATA['pajak']['CPM_LOKASI'] == 1 ? 'checked' : '') ?> <?php echo (!empty($value['CPM_USER']) ? "onclick='javascript:return true'" : '') ?>> Pelabuhan Bandara</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Jumlah Pajak yang dibayar <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> class="form-control number SUM" style="width: 100%" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Terbilang</label>
                    <b><input type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly class="form-control" value="<?php echo ucwords($DATA['pajak']['CPM_TERBILANG']) ?>" placeholder="Terbilang"></b>
                </div>
            </div>
        </div>

        <?php
        echo $ttd_penanggung_jawab;
        ?>

        <?php
        if ($lapor->_mod == "ver" && $lapor->_s == 2) {
            echo "<div class=\"row\" class=\"child\">
            <div class=\"col-md-12\">
                <div class=\"lm-subtitle\">VERIFIKASI</div>
                <hr />
            </div>
            </div>
            <div class=\"row\">
                <div class=\"col-md-12\">
                    <div class=\"form-group\">
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>
                        <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                    </div>
                </div>
            </div>";
        } else if ($lapor->_mod == "per" && $lapor->_s == 3) {
            echo "<div class=\"row\" class=\"child\">
                <div class=\"col-md-12\">
                    <div class=\"lm-subtitle\">PERSETUJUAN</div>
                    <hr />
                </div>
            </div>
            <div class=\"row\">
                <div class=\"col-md-12\">
                    <div class=\"form-group\">
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>
                        <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" style=\"min-width: 100%\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea>
                    </div>
                </div>
            </div>";
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
                    } elseif (in_array($lapor->_s, array(3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
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