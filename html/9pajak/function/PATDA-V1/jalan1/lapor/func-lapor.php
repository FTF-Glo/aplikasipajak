<?php
$DIR = "PATDA-V1";
$modul = "jalan";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");
if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $DATA = $lapor->get_previous_pajak($npwpd, $nop);
}
$list_pejabat = $lapor->get_pejabat();
foreach ($list_pejabat as $list_pejabat) {
    $opt_pejabat .= "<option value=\"{$list_pejabat['CPM_KEY']}\">{$list_pejabat['CPM_NIP']} - {$list_pejabat['CPM_NAMA']}</option>";
}
// var_dump($DATA['pajak_atr']);
// die;
// print_r($DATA);
// var_dump($DATA['pajak']['CPM_MASA_PAJAK']);
// die;
?>

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script language="javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js?=v.0.1.1.js"></script>
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
       <!-- <tr>
            <th colspan=\"6\">TTD Penangung Jawab</th>
        </tr>-->
        <tr>
            <td>Mengetahui </td>
            <td>: <select id=\"PEJABAT2\" class=\"form-control\" style=\"width:300px;height:30px;display:inline-block;font-size:small;\" name=\"PAJAK[CPM_PEJABAT_MENGETAHUI]\">{$opt_pejabat}</select></td>
        </tr>
        <tr>
            <td>Tanggal Pengesahan</td>
            <td>: <input type='date' class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;font-size:small;\" name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' ></td>
        </tr>";
    }
    // if (in_array($s, array(2, 3, 5))) {
    //     $defaultdate =  date('Y-m-d');
    //     $ttd_penanggung_jawab .= "
    //     <tr>
    //         <th colspan=\"6\">TTD Penangung Jawab</th>
    //     </tr>
    //     <tr>
    //         <td>Mengetahui :</td>
    //         <td> <select id=\"PEJABAT2\" class=\"form-control\" style=\"width:300px;height:30px;display:inline-block;font-size:small;\" name=\"PAJAK[CPM_PEJABAT_MENGETAHUI]\">{$opt_pejabat}</select></td>
    //     </tr>
    //     <tr>
    //         <td>Tanggal Pengesahan :</td>
    //         <td> <input type='date' class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;font-size:small;\" name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' ></td>
    //     </tr>";
    // }
    ?>
    <table class="main" width="900">
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK PENERANGAN JALAN</b></td>
        </tr>
        <?php if (!empty($npwpd)) : ?>
            <tr>
                <td width="200">NPWPD <b class="isi">*</b></td>
                <td>:
                    <input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) ?>" readonly>
                    <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                        <?php
                        if (empty($_SESSION['npwpd'])) :
                            $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f);
                        ?>
                            <input type="button" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm ?>'">
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php else : ?>
            <tr>
                <td width="200">NPWPD <b class="isi">*</b></td>
                <td>:
                    <input type="hidden" id="TBLJNSPJK" value="JALAN">
                    <select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width:250px;"></select>
                    <label id="loading"></label>
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_WP'] ?>" readonly placeholder="Nama Wajib Pajak"></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="80" rows="3" readonly placeholder="Alamat Wajib Pajak"><?php echo $DATA['profil']['CPM_ALAMAT_WP'] ?></textarea></td>
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
                    <a href="<?php echo $prm ?>" class="btn btn-sm btn-secondary" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
                <?php endif; ?>

            </td>
        </tr>
        <tr>
            <td>Kelurahan Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_KELURAHAN_WP'] ?>" readonly placeholder="Kelurahan Wajib Pajak"></td>
        </tr>
        <tr>
            <td>NOP <b class="isi">*</b></td>
            <td>:

                <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                    <select name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width:200px;" onchange="javascript:selectOP()">
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
                    <input type="text" name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NOP'] ?>" readonly placeholder="NOP">
                <?php endif; ?>

                <?php
                if (!empty($DATA['profil']['CPM_NPWPD'])) {
                    $addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
                    $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';

                    if (empty($DATA['pajak']['CPM_ID'])) echo '<input type="button" value="Tambah NOP" onclick="location.href=\'' . $prm . '\'">';
                } ?>
            </td>
        </tr>

        <tr>
            <td>Nama Objek Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_OP'] ?>" readonly placeholder="Nama Objek Pajak"></td>
        </tr>
        <tr valign="top">
            <td>Alamat Objek Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="80" rows="3" readonly placeholder="Alamat Objek Pajak"><?php echo $DATA['profil']['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>
        <tr>
            <td>Kecamatan Objek Pajak <b class="isi">*</b></td>
            <td>:
                <input type="text" name="PAJAK[CPM_NAMA_KECAMATAN_OP]" id="CPM_NAMA_KECAMATAN_OP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_KECAMATAN_OP'] ?>" readonly placeholder="Kecamatan Objek Pajak">
                <input type="hidden" name="PAJAK[CPM_KECAMATAN_OP]" id="KECAMATAN_OP" value="<?php echo $DATA['profil']['CPM_KECAMATAN_OP'] ?>">
                <?php
                if (
                    !empty($npwpd) && !empty($nop) &&
                    (empty($DATA['profil']['CPM_KECAMATAN_OP']) ||
                        empty($DATA['profil']['CPM_KELURAHAN_OP'])
                    )
                ) :
                    $prm = 'main.php?param=' .
                        base64_encode('a=' . $a . '&m=mPatdaPelayananPelapor1&f=fPatdaPelayananLaporOP6&npwpd=' . $npwpd . '&npwpd=' . $npwpd . '&nop=' . $nop);
                ?>
                    <a href="<?php echo $prm ?>" class="btn btn-sm btn-secondary" target="_blank" title="setelah data objek pajak diubah, refresh halaman ini (F5)">Ubah data objek pajak</a>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td>Kelurahan Objek Pajak <b class="isi">*</b></td>
            <td>:
                <input type="text" name="PAJAK[CPM_NAMA_KELURAHAN_OP]" id="CPM_NAMA_KELURAHAN_OP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_KELURAHAN_OP'] ?>" readonly placeholder="Kelurahan Objek Pajak">
                <input type="hidden" name="PAJAK[CPM_KELURAHAN_OP]" id="KELURAHAN_OP" value="<?php echo $DATA['profil']['CPM_KELURAHAN_OP'] ?>">
            </td>
        </tr>
        <tr valign="top">
            <td>Rekening Pajak <b class="isi">*</b></td>
            <td>: <select name="PAJAK[CPM_REKENING]" class="form-control" style="width:500px;height:30px;display:inline-block;font-size:small;" id="CPM_REKENING">
                    <?php
                    if (isset($DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']])) {
                        $rek = $DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']];
                        echo "<option value='{$DATA['profil']['CPM_REKENING']}' tarif='{$rek['tarif']}' harga='{$rek['harga']}' selected>{$DATA['profil']['CPM_REKENING']} - {$rek['nmrek']}</option>";
                    }
                    ?>
                </select>
                <input type="hidden" name="PAJAK[CPM_JENIS_PAJAK]" value="" />
            </td>
        </tr>
        <!-- <tr>
            <td>Jenis Pajak</td>
            <td>: 
                <input type="text" name="PAJAK[CPM_JENIS_PAJAK]" value="<?php echo $DATA['profil']['CPM_JENIS_JALAN'] ?>" readonly />
            </td>
        </tr> -->
        <tr>
            <td colspan="2" align="center">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>LAPOR PAJAK JALAN</b></td>
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
                <table width="100%" border="0" align="center" class="child">
                    <tr>
                        <th colspan="3">Data Pajak</th>
                    </tr>
                    <tr valign="top">
                        <td width="200">No Pelaporan Pajak <b class="isi">*</b></td>
                        <td> : <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;font-size:small;\" type=\"text\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "..." ?></td>
                        <td width="200" rowspan="9">Keterangan :</br>
                            <textarea class="form-control" name="PAJAK_ATR[CPM_ATR_KETERANGAN]" id="CPM_KETERANGAN" rows="10" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Keterangan"><?php echo $DATA['pajak_atr']['CPM_ATR_KETERANGAN'] ?></textarea>
                        </td>
                    </tr>
                    <tr>

                        <td>Tipe Pajak <b class="isi">*</b></td>
                        <td> :
                            <select class="form-control" style="width:150px;height:30px;display:inline-block;font-size:small;" name="PAJAK_ATR[CPM_ATR_TIPE_PAJAK][]" id="CPM_TIPE_PAJAK">
                                <?php
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        foreach ($DATA['pajak']['ARR_TIPE_PAJAK'] as $x => $y) {
                                            echo ($x == $DATA['pajak_atr'][0]['CPM_ATR_TIPE_PAJAK']) ? "<option  readonly value='{$x}' selected>{$y}</option>" : "";
                                        }
                                        //echo "<option value='{$DATA['pajak_atr'][0]['CPM_ATR_TIPE_PAJAK']}' selected>wwww    </option>";
                                    } else {
                                        foreach ($DATA['pajak']['ARR_TIPE_PAJAK'] as $x => $y) {
                                            echo ($x == $DATA['pajak_atr'][0]['CPM_TIPE_PAJAK']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                        }
                                    }
                                } else {
                                    foreach ($DATA['pajak']['ARR_TIPE_PAJAK'] as $x => $y) {
                                        echo ($x == $DATA['pajak_atr'][0]['CPM_ATR_TIPE_PAJAK']) ? "<option  readonly value='{$x}' selected>{$y}</option>" : "";
                                    }
                                }
                                ?>
                            </select>
                            <label id="load-tarif"></label>
                        </td>
                    </tr>
                    <tr>

                        <td width="200">Tahun Pajak <b class="isi">*</b></td>
                        <td> :

                            <select class="form-control" style="width:150px;height:30px;display:inline-block;font-size:small;" name="PAJAK_ATR[CPM_ATR_TAHUN_PAJAK][]" id="CPM_TAHUN_PAJAK">
                                <?php

                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$DATA['pajak_atr'][0]['CPM_ATR_TAHUN_PAJAK']}' selected>{$DATA['pajak_atr'][0]['CPM_ATR_TAHUN_PAJAK']}</option>";
                                    } else {
                                        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {

                                            echo ($th == $DATA['pajak_atr'][0]['CPM_ATR_TAHUN_PAJAK']) ? "<option value='{$th}' selected>{$th}</option>" : "<option value='{$th}'>{$th}</option>";
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak_atr'][0]['CPM_ATR_TAHUN_PAJAK']}' selected>{$DATA['pajak_atr'][0]['CPM_ATR_TAHUN_PAJAK']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Bulan Pajak</td>
                        <td> :
                            <select class="form-control" style="width:150px;height:30px;display:inline-block;font-size:small;" id="CPM_MASA_PAJAK">
                                <?php
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        foreach ($lapor->arr_bulan as $x => $y) {
                                            echo ($DATA['pajak_atr'][0]['CPM_ATR_MASA_PAJAK'] == $x) ? "<option  readonly value='{$x}' selected>{$y}</option>" : "";
                                        }
                                        //  echo "<option value='{$DATA['pajak_atr']['CPM_ATR_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$DATA['CPM_ATR_MASA_PAJAK']['CPM_ATR_MASA_PAJAK']]}</option>";
                                    } else {
                                        echo "<option></option>";
                                        foreach ($lapor->arr_bulan as $x => $y) {
                                            echo ($DATA['pajak_atr'][0]['CPM_ATR_MASA_PAJAK'] == $x) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                        }
                                    }
                                } else {
                                    foreach ($lapor->arr_bulan as $x => $y) {
                                        echo ($DATA['pajak_atr'][0]['CPM_ATR_MASA_PAJAK'] == $x) ? "<option readonly value='{$x}' selected>{$y}</option>" : "";
                                    }
                                    //echo "<option value='{$DATA['pajak']['CPM_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']]}</option>";
                                }
                                ?>
                                <input type="hidden" name="PAJAK_ATR[CPM_ATR_MASA_PAJAK][]" id="CPM_MASA_PAJAK10" readonly class="number" value="<?php echo (int) $DATA['pajak_atr'][0]['CPM_ATR_MASA_PAJAK'] ?>">
                            </select>
                        </td>
                    </tr>
                    <tr>

                        <td>Masa Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_MASA_PAJAK1][]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK1\"" : ""; ?> style="width: 120px;" readonly value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_MASA_PAJAK1']; ?>" placeholder="Masa Awal"> s.d
                            <input type="text" name="PAJAK_ATR[CPM_ATR_MASA_PAJAK2][]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK2\"" : ""; ?> style="width: 120px;" readonly value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_MASA_PAJAK2']; ?>" placeholder="Masa Akhir">
                        </td>
                    </tr>

                    <?php
                    $no_rek = $DATA['profil']['CPM_REKENING'];
                    $allowed_reks = array("4.1.01.10.03", "4.1.01.10.04", "4.1.01.10.05", "4.1.01.10.06");
                    if (in_array($no_rek, $allowed_reks)) {
                    ?>
                        <tr>
                            <td width="250">Merk Jenset <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_MERK_JENSET][]" id="merkJenset" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_MERK_JENSET']; ?>" placeholder="Merk Jenset"></td>
                        </tr>
                        <tr>
                            <td width="250">Unit <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_UNIT][]" id="unit" class="number" maxlength="19" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_UNIT']; ?>" placeholder="Unit"></td>
                        </tr>
                        <tr>
                            <td width="250">Kapasitas Pembangkit <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_PEMBANGKIT][]" id="CPM_PEMBANGKIT" class="number" maxlength="19" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_PEMBANGKIT']; ?>" placeholder="Kapasitas Pembangkit"></td>
                        </tr>
                        <tr>
                            <td width="250">Faktor Daya <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_FAKTOR_DAYA][]" id="CPM_FAKTOR_DAYA" class="number" maxlength="19" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_FAKTOR_DAYA']; ?>" placeholder="Faktor Daya"></td>
                        </tr>
                        <tr>
                            <td width="250">Harga Satuan <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_SATUAN][]" id="CPM_SATUAN" class="number" maxlength="19" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_SATUAN']; ?>" placeholder="Harga Satuan"> </td>
                        </tr>
                        <tr>
                            <td width="250">Pemakaian <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_TOTAL_KWH][]" id="CPM_TOTAL_KWH" class="number" maxlength="19" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_TOTAL_KWH']; ?>" placeholder="Total Jam"> Satuan Jam</td>
                        </tr>


                    <?php
                    } else {
                    ?>
                        <tr>
                            <td width="250">Pemakaian <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_TOTAL_KWH][]" id="CPM_TOTAL_KWH2" class="number" maxlength="19" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_TOTAL_KWH']; ?>" placeholder="Total Kwh"> Satuan Kwh</td>
                        </tr>
                    <?php
                    }
                    ?>


                    <?php
                    if ($DATA['pajak']['HARGA_DASAR_ENABLE'] == 1) {
                        echo "<tr>
								<td width=\"250\">Harga Dasar <b class=\"isi\">*</b></td>
								<td> : <input type=\"text\" readonly name=\"PAJAK[CPM_HARGA_DASAR]\" id=\"CPM_HARGA_DASAR\" class=\"number\" maxlength=\"19\" value=\"{$DATA['pajak']['CPM_HARGA_DASAR']}\" placeholder=\"Harga Dasar\"></td>
							</tr>";
                    } else {
                        echo "<input type=\"hidden\" name=\"PAJAK[CPM_HARGA_DASAR]\" id=\"CPM_HARGA_DASAR\" value=\"1\">";
                    }
                    ?>
                    <tr>
                        <td width="250">Pembayaran Pemakaian Objek Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_TOTAL_OMZET][]" id="CPM_TOTAL_OMZET" class="number SUM" maxlength="19" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_TOTAL_OMZET'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Pemakaian"></td>
                    </tr>
                    <tr style="display:none">
                        <td>Pembayaran Lain-lain</td>
                        <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_BAYAR_LAINNYA][]" id="CPM_BAYAR_LAINNYA" class="number SUM" maxlength="19" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_BAYAR_LAINNYA'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Lain-lain"></td>
                    </tr>
                    <tr>
                        <td>Dasar Pengenaan Pajak (DPP)</td>
                        <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_DPP][]" id="CPM_DPP" class="number" readonly maxlength="19" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_DPP'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Dasar Pengenaan Pajak (DPP)"></td>
                    </tr>
                    <tr>

                        <td>Tarif Pajak <b class="isi">*</b></td>
                        <td> :
                            <?php if (in_array($lapor->_mod, array("pel", ""))) {
                                if (!in_array($lapor->_i, array(1, 3, ""))) { ?>

                                    <input type="text" name="PAJAK_ATR[CPM_ATR_TARIF_PAJAK][]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_TARIF_PAJAK'] ?>" placeholder="Tarif Pajak">

                                    <!-- <option value='{$DATA[' pajak_atr']['CPM_ATR_MASA_PAJAK']}' selected>{$lapor->arr_bulan[$DATA['CPM_ATR_MASA_PAJAK']['CPM_ATR_MASA_PAJAK']]}</option>"; -->
                                <?php } elseif (!in_array($lapor->_i, array(1, 4, ""))) {
                                    // var_dump($rek['tarif']);
                                    echo '<input type="text" name="PAJAK_ATR[CPM_ATR_TARIF_PAJAK][]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="' . $rek['tarif'] . '" placeholder="aa"> %';
                                    //  die;
                                } else {
                                    // var_dump($DATA);
                                    // die;

                                ?>

                                    <input type="text" name="PAJAK_ATR[CPM_ATR_TARIF_PAJAK][]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="<?php echo ($DATA['pajak_atr'][0]['CPM_ATR_TARIF_PAJAK'] == '') ? $DATA['tarif'] : $DATA['pajak_atr'][0]['CPM_ATR_TARIF_PAJAK'] ?>" placeholder="aa"> %
                                <?php
                                }
                            } else { ?>

                                <input type="text" name="PAJAK_ATR[CPM_ATR_TARIF_PAJAK][]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_TARIF_PAJAK'] ?>" placeholder="Tarif Pajak"> %
                            <?php }
                            ?>

                            <input type="hidden" class="number" maxlength="19" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL" value="0" />

                        </td>
                    </tr>

                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" id="CPM_ATR_ID" style="width: 50px;" readonly value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_ID'] ?>" placeholder="">
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
                            if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) {
                                $hapus = '<button type="button" id="btn-hapus-' . $no . '" onclick="hapusDetail(' . $no . ')">Hapus</button>';
                            } else {
                                $hapus = '';
                            }
                            echo '<table width="100%" class="child" id="atr_rek-' . $no . '" border="0" style="margin-top:8px">
                            <tr>
                                 <th colspan="3">Data Pajak ' . $no . '</th>
                            </tr>
                            <tr valign="top">
        
                            <td width="200">No Pelaporan Pajak <b class="isi">*</b></td>
                            <td> : <input  value="' . $DATA['pajak']['CPM_NO'] . '" readonly></td>
                            <td width="200" rowspan="9"> Keterangan : 
                            <textarea class="form-control" name="PAJAK_ATR[CPM_ATR_KETERANGAN]" id="CPM_KETERANGAN" rows="10"></textarea>
                            </td>
                        <tr>
                        <tr>
                        <td>Tipe Pajak <b class="isi">*</b></td>
                        <td> :
                        <select class="form-control" onchange="rumusPerhitungan(' . $no . ')" style="width:150px;height:30px;display:inline-block;font-size:small;" name="PAJAK_ATR[CPM_ATR_TIPE_PAJAK][]" tabindex="' . $idx . '" id="CPM_TIPE_PAJAK-' . $no . '">';

                            $tipePajak = $DATA['pajak']['ARR_TIPE_PAJAK'];
                            $tipeAtr = $atr['CPM_ATR_TIPE_PAJAK'];

                            if (in_array($lapor->_mod, array("pel", ""))) {
                                if (!in_array($lapor->_i, array(1, 3, ""))) {
                                    // foreach ($DATA['pajak']['ARR_TIPE_PAJAK'] as $x => $y) {
                                    //     echo ($x == $DATA['pajak_atr'][0]['CPM_ATR_TIPE_PAJAK']) ? "<option  readonly value='{$x}' selected>{$y}</option>" : "";
                                    // }
                                    foreach ($tipePajak as $kws => $val) {
                                        //  echo "<option value='{$kws}'" . ($tipeAtr == $kws ? ' selected' : '') . ">$val</option>";
                                        echo ($kws == $tipeAtr) ? "<option  readonly value='{$kws}' selected>{$val}</option>" : "";
                                    }
                                } else {
                                    foreach ($tipePajak as $kws => $val) {
                                        echo "<option value='{$kws}'" . ($tipeAtr == $kws ? ' selected' : '') . ">$val</option>";
                                    }
                                }
                            } else {
                                foreach ($tipePajak as $kws => $val) {
                                    //  echo "<option value='{$kws}'" . ($tipeAtr == $kws ? ' selected' : '') . ">$val</option>";
                                    echo ($kws == $tipeAtr) ? "<option  readonly value='{$kws}' selected>{$val}</option>" : "";
                                }
                            }

                            echo '</select>
                        </td>	
                    </tr>
                    <tr>
                        <td>Tahun Pajak <b class="isi">*</b></td>
                        <td> :
                            <select class="form-control" onchange="rumusPerhitungan(' . $no . ')" style="width:150px;height:30px;display:inline-block;font-size:small;" name="PAJAK_ATR[CPM_ATR_TAHUN_PAJAK][]" tabindex="' . ($idx) . '" id="CPM_TAHUN_PAJAK-' . $no . '"  >';
                            for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
                                $tahun[] = $th;
                            }
                            $tahunAtr = $atr['CPM_ATR_TAHUN_PAJAK'];
                            if (in_array($lapor->_mod, array("pel", ""))) {
                                if (!in_array($lapor->_i, array(1, 3, ""))) {
                                    foreach ($tahun as $kws) {
                                        echo ($kws == $tahunAtr) ? "<option  readonly value='{$kws}' selected>{$kws}</option>" : "";
                                    }
                                } else {
                                    foreach ($tahun as $kws) {
                                        echo "<option value='{$kws}' " . ($tahunAtr == $kws ? 'selected' : '') . ">$kws</option>";
                                    }
                                }
                            } else {
                                foreach ($tahun as $kws) {
                                    echo "<option value='{$kws}' " . ($tahunAtr == $kws ? 'selected' : '') . ">$kws</option>";
                                }
                            }
                            echo '</select>
                        </td>	
                    </tr>
                    <tr>
                        <td>Bulan Pajak <b class="isi">*</b></td>
                        <td> :
                              <select class="form-control" onchange="rumusPerhitungan(' . $no . ')" style="width:150px;height:30px;display:inline-block;font-size:small;" name="PAJAK_ATR[CPM_ATR_MASA_PAJAK][]" tabindex="' . ($idx) . '" id="CPM_MASA_PAJAK-' . $no . '">';
                            $bulan = $lapor->arr_bulan;
                            $bulanAtr = $atr['CPM_ATR_MASA_PAJAK'];
                            if (in_array($lapor->_mod, array("pel", ""))) {
                                if (!in_array($lapor->_i, array(1, 3, ""))) {
                                    foreach ($bulan as $kws => $val) {
                                        echo ($kws == $bulanAtr) ? "<option  readonly value='{$kws}' selected>{$val}</option>" : "";
                                        //  echo "<option value='{$kws}' " . ($bulanAtr == $kws ? 'selected' : '') . ">$val</option>";
                                    }
                                } else {
                                    foreach ($bulan as $kws => $val) {
                                        echo "<option value='{$kws}' " . ($bulanAtr == $kws ? 'selected' : '') . ">$val</option>";
                                    }
                                }
                            } else {
                                foreach ($bulan as $kws => $val) {
                                    echo ($kws == $bulanAtr) ? "<option  readonly value='{$kws}' selected>{$val}</option>" : "";
                                    //  echo "<option value='{$kws}' " . ($bulanAtr == $kws ? 'selected' : '') . ">$val</option>";
                                }
                            }

                            echo '</select>
                            </td>	
                        </tr>
                        <tr>
                        <td>Masa Pajak <b class="isi">*</b></td>
                        <td> :
                            <input type="text" name="PAJAK_ATR[CPM_ATR_MASA_PAJAK1][]" id="CPM_MASA_PAJAK1-' . $no . '" style="width: 120px;" value="' . $atr['CPM_ATR_MASA_PAJAK1'] . '" readonly placeholder="Masa Awal">s.d
                            <input type="text" name="PAJAK_ATR[CPM_ATR_MASA_PAJAK2][]" id="CPM_MASA_PAJAK2-' . $no . '" style="width: 120px;"  value="' . $atr['CPM_ATR_MASA_PAJAK2'] . '" readonly placeholder="Masa Akhir">
                        </td>	
                        </tr>';
                            $atrRek =   $DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['kdrek'];
                            //$atrisiRek =   $atr;
                            // var_dump($atr);
                            if ($atrRek == '4.1.01.10.03' || $atrRek == '4.1.01.10.04' || $atrRek == "4.1.01.10.05" || $atrRek == "4.1.01.10.06") {
                                echo '<tr>
                                            <td width="250">Merk Jenset <b class="isi">*</b></td>
                                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_MERK_JENSET][]" id="merkJenset" value="' . $atr['CPM_ATR_MERK_JENSET'] . '" ' . $readonly . ' placeholder="Merk Jenset"> </td>
                                        </tr>
                                        <tr>
                                            <td width="250">Unit <b class="isi">*</b></td>
                                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_UNIT][]" id="unit-' . $no . '" class="number" maxlength="19"  value="' . $atr['CPM_ATR_UNIT'] . '" ' . $readonly . ' placeholder="Unit"></td>
                                        </tr>
                                        <tr>
                                            <td width="250">Kapasitas Pembangkit <b class="isi">*</b></td>
                                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_PEMBANGKIT][]" id="CPM_PEMBANGKIT-' . $no . '" class="number" maxlength="19"  value="' . $atr['CPM_ATR_PEMBANGKIT'] . '" ' . $readonly . ' placeholder="Kapasitas Pembangkit"></td>
                                        </tr>
                                        <tr>
                                            <td width="250">Faktor Daya <b class="isi">*</b></td>
                                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_FAKTOR_DAYA][]" id="CPM_FAKTOR_DAYA-' . $no . '" class="number" maxlength="19"  value="' . $atr['CPM_ATR_FAKTOR_DAYA'] . '" ' . $readonly . ' placeholder="Faktor Daya"></td>
                                        </tr>
                                        <tr>
                                            <td width="250">Harga Satuan <b class="isi">*</b></td>
                                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_SATUAN][]" id="CPM_SATUAN-' . $no . '" class="number" maxlength="19"  value="' . $atr['CPM_ATR_SATUAN'] . '" ' . $readonly . ' placeholder="Harga Satuan"> </td>
                                        </tr>
                                        <tr>
                                            <td width="250">Pemakaian <b class="isi">*</b></td>
                                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_TOTAL_KWH][]" id="CPM_TOTAL_KWH-' . $no . '" class="number" maxlength="19"  value="' . $atr['CPM_ATR_TOTAL_KWH'] . '" ' . $readonly . ' placeholder="Total Jam"> Satuan Jam</td>
                                        </tr>';
                            } else {
                                echo '<tr>
                            <td width="250">Pemakaian <b class="isi">*</b></td>
                            <td> : <input type="text" onkeyup="rumusPerhitungan(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TOTAL_KWH][]" id="CPM_TOTAL_KWH2-' . $no . '"  value="' . $atr['CPM_ATR_TOTAL_KWH'] . '" ' . $readonly . ' class="number" maxlength="19" placeholder="Total Kwh"> Satuan Kwh</td>
                        </tr>';
                            }

                            echo ' <tr>
                            <td width="250">Pembayaran Pemakaian Objek Pajak <b class="isi">*</b></td>
                            <td> : <input type="text" onkeyup="rumusPerhitungan(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TOTAL_OMZET][]" value="' . $atr['CPM_ATR_TOTAL_OMZET'] . '" id="CPM_TOTAL_OMZET-' . $no . '" ' . $readonly . ' class="number SUM" maxlength="19"  placeholder="Pembayaran Pemakaian"></td>
                        </tr>
                        <tr>
                        <tr style="display:none">
                            <td>Pembayaran Lain-lain</td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_BAYAR_LAINNYA][]" id="CPM_BAYAR_LAINNYA" class="number SUM" maxlength="19" value="' . $atr['CPM_ATR_BAYAR_LAINNYA'] . '" placeholder="Pembayaran Lain-lain"></td>
                         </tr>
                            <td width="250">Dasar Pengenaan Pajak (DPP)</td>
                            <td> : <input type="text"  name="PAJAK_ATR[CPM_ATR_DPP][]" id="CPM_DPP-' . $no . '" class="number SUM" maxlength="19" value="' . $atr['CPM_ATR_DPP'] . '" placeholder="Pembayaran Pemakaian" readonly></td>
                         </tr>
                         <tr>
                            <td>Tarif Pajak <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK_ATR[CPM_ATR_TARIF_PAJAK][]" id="CPM_TARIF_PAJAK-' . $no . '" style="width: 50px;" readonly value="' . $atr['CPM_ATR_TARIF_PAJAK'] . '" placeholder="Tarif Pajak"> %
                            </td>
                        </tr>

                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" id="CPM_ATR_ID" style="width: 50px;" readonly value="' . $atr['CPM_ATR_ID'] . '" placeholder=""> 

                        <tr>
                            <td colspan="6" align="right" valign="top">
                            
                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" id="CPM_ATR_ID-' . $no . '" value="" />
                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL-' . $no . '" value="0" />
                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_TARIF][]" id="CPM_ATR_TARIF-' . $no . '" value="" />
                            <button type="button" >Hapus</button>
                            </td>
                        </tr>
                            </table>';
                        }
                    }

                    ?>
                </div>
                <input type="hidden" id="count" value="<?php echo ($count == NULL ? 1 : $count) ?>" />

                <?php if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) { ?>
                    <div style="text-align:center;padding:10px"> <button type="button" class="btn btn-info btn-tambah" onclick="myFunction2()">Tambah</button></div>
                <?php } ?>
                </br>
            </td>
        </tr>
        <tr>
        <tr>
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child">
                    <tr>
                        <td>Pembayaran Terutang (Tarif x DPP)</td>
                        <td> : <input type="text" name="PAJAK[CPM_BAYAR_TERUTANG]" id="CPM_BAYAR_TERUTANG" readonly class="number" maxlength="19" value="<?php echo $DATA['pajak']['CPM_BAYAR_TERUTANG'] ?>" readonly placeholder="Pembayaran Terutang">
                            <!-- </td>
                        <td> -->
                            <label>
                                <input type="checkbox" id="HITUNG_DARI_KETETAPAN">
                                Berdasarkan ketetapan
                            </label>
                        </td>
                    </tr>


                    <tr>
                        <td>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x Bulan keterlambatan</td>
                        <td> : <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" class="number SUM" maxlength="17" placeholder="Sanksi Telat Lapor" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "readonly" : "readonly"; ?> value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>"></td>
                    </tr>

                    <tr>
                        <td>Jumlah Pajak yang dibayar <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="number" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar"></td>
                    </tr>
                    <tr>
                        <td colspan="3">Terbilang : <b><input class="form-control" style="width:800px;height:30px;display:inline-block;" type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly value="<?php echo ucwords($DATA['pajak']['CPM_TERBILANG']) ?>" placeholder="Terbilang"></b></td>
                        <!-- <input type="text" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL" class="number" maxlength="19" value="0" /> -->
                    </tr>
                </table>
            </td>
        </tr>

        <?php
        echo $ttd_penanggung_jawab;
        ?>
        <?php
        if ($lapor->_mod == "ver" && $lapor->_s == 2) {
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>VERIFIKASI</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\"><textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea></td>                        
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
            <td align="center" colspan="2">
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
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                        //echo "<input type=\"button\" class=\"btn-print\" action=\"print_notaHitung\" value=\"Cetak Nota Hitung\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_notaHitung\" value=\"Cetak Nota Hitung\">";
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
            </td>
        </tr>
    </table>
</form>