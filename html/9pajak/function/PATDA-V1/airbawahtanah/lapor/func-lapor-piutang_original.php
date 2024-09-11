<?php
$DIR = "PATDA-V1";
$modul = "airbawahtanah";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");
if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $DATA = $lapor->get_previous_pajak($npwpd, $nop);
}
//var_dump($DATA['profil']['CPM_PERUNTUKAN'], $npwpd, $nop);
$npa = $lapor->list_npa();
$list_npa = $npa['combo'];

$npa2 = $lapor->list_npa2();
$list_npa2 = $npa2['combo'];
//var_dump($npa2, $npa);

//list pejabat
$list_pejabat = $lapor->get_pejabat();
foreach ($list_pejabat as $list_pejabat) {
    $opt_pejabat .= "<option value=\"{$list_pejabat['CPM_KEY']}\">{$list_pejabat['CPM_NIP']} - {$list_pejabat['CPM_NAMA']}</option>";
}

$type_masa = array(
    4 => "1 Bulan",
    31 => "Triwulan 1",
    32 => "Triwulan 2",
    33 => "Triwulan 3",
    34 => "Triwulan 4",
);

// echo '<pre>';
// print_r($DATA['profil']);
// echo '</pre>';
?>

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script language="javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor-piutang.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>

<!-- <pre> -->
<?php //die(print_r($DATA))
?>
<form class="cmxform" autocomplete="off" id="form-lapor" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="param" id="param" value="<?php echo  base64_encode("a=" . $lapor->_a . "&m=" . $lapor->_m . "&f=" . $lapor->_f) ?>">
    <input type="hidden" id="persen_terlambat_lap" value="<?php echo  $persen_terlambat_lap ?>">
    <input type="hidden" id="editable_terlambat_lap" value="<?php echo  $editable_terlambat_lap ?>">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_VERSION]" value="<?php echo $DATA['pajak']['CPM_VERSION']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_PROFIL]" value="<?php echo $DATA['profil']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_TARIF]" value="<?php echo $DATA['tarif']; ?>">
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
        <tr>
            <th colspan=\"5\">TTD Penangung Jawab</th>
        </tr>
        <tr>
            <td>Mengetahui:</td>
            <td><select id=\"PEJABAT2\" name=\"PAJAK[CPM_PEJABAT_MENGETAHUI]\">{$opt_pejabat}</select></td>
        </tr>
        <tr>
            <td>Tanggal Pengesahan:</td>
            <td> <input type='date' name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' ></td>
        </tr>";
    }
    ?>
    <table class="main" width="900">
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK AIR BAWAH TANAH</b></td>
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
                            <input type="button" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo  $prm ?>'">
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php else : ?>
            <tr>
                <td width="200">NPWPD <b class="isi">*</b></td>
                <td>:
                    <input type="hidden" id="TBLJNSPJK" value="AIRBAWAHTANAH">
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
                    <a href="<?php echo $prm ?>" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
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
                    //$addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
					$addOp = 'fPatdaPelayananLaporOP1';
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
                        base64_encode('a=' . $a . '&m=mPatdaPelayananPelapor1&f=fPatdaPelayananLaporOP1&npwpd=' . $npwpd . '&npwpd=' . $npwpd . '&nop=' . $nop);
                ?>
                    <a href="<?php echo $prm ?>" target="_blank" title="setelah data objek pajak diubah, refresh halaman ini (F5)">Ubah data objek pajak</a>
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
            <td>: <select name="PAJAK[CPM_REKENING]" id="CPM_REKENING" style="width:590px;">
                    <?php
                    if (isset($DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']])) {
                        $rek = $DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']];
                        echo "<option value='{$DATA['profil']['CPM_REKENING']}' tarif='{$rek['tarif']}' harga='{$rek['harga']}' selected>{$DATA['profil']['CPM_REKENING']} - {$rek['nmrek']}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>LAPOR PAJAK AIR BAWAH TANAH</b></td>
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
                    <!--<tr valign="top">
                        <td width="200">Harga m<sup>3</sup> <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_HARGA]" id="CPM_HARGA" class="number" readonly value="<?php echo isset($rek['harga']) ? $rek['harga'] : 0 ?>" placeholder="Harga"></td>
                        <td width="200" rowspan="9">Keterangan :</br>
                            <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" rows="10" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Keterangan"><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>Lokasi Sumber Air <b class="isi">*</b></td>
                        <td> : <select name="PAJAK[CPM_LOKASI_SUMBER_AIR]" id="CPM_LOKASI_SUMBER_AIR" class="SUM2">
                                <?php
                                $index = $DATA['index'][1];
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$DATA['pajak']['CPM_LOKASI_SUMBER_AIR']}' selected>{$index[$DATA['pajak']['CPM_LOKASI_SUMBER_AIR']]['URAIAN']}</option>";
                                    } else {
                                        foreach ($index as $x) {
                                            echo ($x['INDEX'] == $DATA['pajak']['CPM_LOKASI_SUMBER_AIR']) ? "<option value='{$x['INDEX']}' selected>{$x['URAIAN']}</option>" : "<option value='{$x['INDEX']}'>{$x['URAIAN']}</option>";
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_LOKASI_SUMBER_AIR']}' selected>{$index[$DATA['pajak']['CPM_LOKASI_SUMBER_AIR']]['URAIAN']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>Kualitas Air <b class="isi">*</b></td>
                        <td> : <select name="PAJAK[CPM_KUALITAS_AIR]" id="CPM_KUALITAS_AIR" class="SUM2">
                                <?php
                                $index = $DATA['index'][2];
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$DATA['pajak']['CPM_KUALITAS_AIR']}' selected>{$index[$DATA['pajak']['CPM_KUALITAS_AIR']]['URAIAN']}</option>";
                                    } else {
                                        foreach ($index as $x) {
                                            echo ($x['INDEX'] == $DATA['pajak']['CPM_KUALITAS_AIR']) ? "<option value='{$x['INDEX']}' selected>{$x['URAIAN']}</option>" : "<option value='{$x['INDEX']}'>{$x['URAIAN']}</option>";
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_KUALITAS_AIR']}' selected>{$index[$DATA['pajak']['CPM_KUALITAS_AIR']]['URAIAN']}</option>";
                                }

                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>Tingkat Kerusakan Lingkungan <b class="isi">*</b></td>
                        <td> : <select name="PAJAK[CPM_TINGKAT_KERUSAKAN]" id="CPM_TINGKAT_KERUSAKAN" class="SUM2">
                                <?php
                                $index = $DATA['index'][3];
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$DATA['pajak']['CPM_TINGKAT_KERUSAKAN']}' selected>{$index[$DATA['pajak']['CPM_TINGKAT_KERUSAKAN']]['URAIAN']}</option>";
                                    } else {
                                        foreach ($index as $x) {
                                            echo ($x['INDEX'] == $DATA['pajak']['CPM_TINGKAT_KERUSAKAN']) ? "<option value='{$x['INDEX']}' selected>{$x['URAIAN']}</option>" : "<option value='{$x['INDEX']}'>{$x['URAIAN']}</option>";
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_TINGKAT_KERUSAKAN']}' selected>{$index[$DATA['pajak']['CPM_TINGKAT_KERUSAKAN']]['URAIAN']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>-->
                    <tr valign="top">
                        

        </td>
        
        <tr valign="top">
            <td>No Pelaporan Pajak <b class="isi">*</b></td>
			<td width="302">: <input name="PAJAK[CPM_NO]" type="text" id="CPM_NO" value="<?php echo $DATA['pajak']['CPM_NO'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "") ? "" : "readonly"; ?> placeholder="No Pelaporan"></td>
			<td width="200" rowspan="9">Keterangan :</br>
            <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" rows="8" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Keterangan"><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
        </td>
		</tr>
        <tr>
            <td>Tipe Pajak <b class="isi">*</b></td>
            <td> :
                <select name="PAJAK[CPM_TIPE_PAJAK]" id="CPM_TIPE_PAJAK">
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
            </td>
        </tr>
        <tr>
            <td width="200">Tahun Pajak <b class="isi">*</b></td>
            <td> :
                <select name="PAJAK[CPM_TAHUN_PAJAK]" id="CPM_TAHUN_PAJAK">
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
            </td>
        </tr>
        <tr>
            <td>Type Masa</td>
            <td> :
                <select name="PAJAK[CPM_TYPE_MASA]" id="CPM_TYPE_MASA">
                    <?php
                    if (in_array($lapor->_mod, array("pel", ""))) {
                        if (!in_array($lapor->_i, array(1, 3, ""))) {
                            echo "<option value='{$DATA['pajak']['CPM_TYPE_MASA']}' selected>{$type_masa[$DATA['pajak']['CPM_TYPE_MASA']]}</option>";
                        } else {
                            foreach ($type_masa as $k => $v) {
                                echo ($k == $DATA['pajak']['CPM_TYPE_MASA']) ? "<option value='{$k}' selected>{$v}</option>" : "<option value='{$k}'>{$v}</option>";
                            }
                        }
                    } else {
                        echo "<option value='{$DATA['pajak']['CPM_TYPE_MASA']}' selected>{$type_masa[$DATA['pajak']['CPM_TYPE_MASA']]}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Bulan Pajak </td>
            <td> :
                <select id="CPM_MASA_PAJAK">
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
                    if (!empty($lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']])) {
                        $bulan = $lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']];
                    } else {
                        $bulan = $lapor->arr_bulan[$DATA['pajak_atr'][0]['CPM_ATR_BULAN']];
                    }
                    ?>
                    <input type="hidden" name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK10" readonly class="number" value="<?php echo (int) $DATA['pajak']['CPM_MASA_PAJAK'] ?>">
                </select>
            </td>
        </tr>
        <tr>
            <td>Masa Pajak <b class="isi">*</b></td>
            <td> : <input type="text" name="PAJAK[CPM_MASA_PAJAK1]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK1\"" : ""; ?> style="width: 120px;" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK1']; ?>" placeholder="Masa Awal"> s.d
                <input type="text" name="PAJAK[CPM_MASA_PAJAK2]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK2\"" : ""; ?> style="width: 120px;" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK2']; ?>" placeholder="Masa Akhir">
            </td>
        </tr>

    
    <tr>
        <td>Jumlah Pajak yang dibayar <b class="isi">*</b></td>
        <td> : <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" class="number"  <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar" onkeyup="hitungNPA()"></td>
    </tr>
    <tr>
        <td colspan="3">Terbilang : <input type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly style="width: 800px;" value="<?php echo $DATA['pajak']['CPM_TERBILANG'] ?>" placeholder="Terbilang"></td>
    </tr>
    <?php
    echo $ttd_penanggung_jawab;
    ?>
    </table>
    </td>
    </tr>
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
                    //echo "<input type=\"button\" class=\"btn-print\" action=\"print_npa\" value=\"Cetak NPA\">";
                    //echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
                } elseif (in_array($lapor->_s, array(2))) {
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                    //echo "<input type=\"button\" class=\"btn-print\" action=\"print_npa\" value=\"Cetak NPA\">";
                    //echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
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

<script>
    function onLoad() {
        $(".ini2").removeClass("show");
        $(".ini2").addClass("hide");

        $(".ini").removeClass("hide");
        $(".ini").addClass("show");
        document.getElementById("CPM_PERUNTUKAN").selectedIndex = "0";
        $('#CPM_ATR_VOLUME-1').val(0);


    }

    function onLoad2() {
        $(".ini").removeClass("show");
        $(".ini").addClass("hide");

        $(".ini2").removeClass("hide");
        $(".ini2").addClass("show");
        document.getElementById("CPM_PERUNTUKAN").selectedIndex = "6";
        $('#CPM_ATR_VOLUME-1').val(0);


    }

    $(document).ready(function() {
        $("#rbaru").click(function() {
            $("#rbaru").prop("checked", true);
        });
        $("#rlama").click(function() {
            $("#rlama").prop("checked", true);
        });
    });
</script>