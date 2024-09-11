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

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//var_dump($DATA['profil']['CPM_PERUNTUKAN'], $npwpd, $nop);
$npa = $lapor->list_npa();
$list_npa = $npa['combo'];

$npaa = $lapor->list_npa_hide_pdam();
$list_npa_hide_pdam = $npaa['combo'];
// $tagihan = $lapor->get_tagihan_6bulan($npwpd,$nop);
// var_dump($this);
// die;
// $payment_flag = $lapor->payment_flag($DATA['pajak']['CPM_ID']);
// var_dump($payment_flag['pengurangan']);
// die;
$npa2 = $lapor->list_npa2();
$list_npa2 = $npa2['combo'];
// echo '<pre>';
// print_r($npa2);
// echo '</pre>';
// var_dump($npa2, $npa);die;

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
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js?v=.0.2.8.js"></script>
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
            <th colspan=\"6\">TTD Penangung Jawab</th>
        </tr>
        <tr>
            <td>Mengetahui:</td>
            <td><select id=\"PEJABAT2\" class=\"form-control\" style=\"width:200px;height:30px;display:inline-block;\" name=\"PAJAK[CPM_PEJABAT_MENGETAHUI]\">{$opt_pejabat}</select></td>
        </tr>
        <tr>
            <td>Tanggal Pengesahan:</td>
            <td> <input type='date' name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' ></td>
        </tr>";
    }
    ?>
    <table class="main" width="900">
        <tr>
            <td colspan="3" align="center" class="subtitle"><b>PROFIL PAJAK AIR BAWAH TANAH</b></td>
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
                    $addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
                    $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';

                    if (empty($DATA['pajak']['CPM_ID'])) echo '<input type="button" value="Tambah NOP" onclick="location.href=\'' . $prm . '\'">';
                } ?>
            </td>
        </tr>
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
            <td>: <select class="form-control" style="width:200px;height:30px;display:inline-block;" name="PAJAK[CPM_REKENING]" id="CPM_REKENING" style="width:590px;">
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
            <td colspan="3" align="center">&nbsp;</td>
        </tr>

            <tr>
            <td colspan="3" align="center" class="subtitle"><b>LAPOR PAJAK AIR BAWAH TANAH</b></td>
        </tr>
        <tr>
            <td colspan="3">
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
                        <th colspan="4">Data Pajak</th>
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
                    <?php
                    //                     echo "<pre>";
                    // var_dump($DATA['profil']['CPM_PERUNTUKAN_AIR']);
                    // die;
                  
                    // foreach ($list_npa2 as $x => $y) {
                    //     var_dump($y);
                    //     $y = substr($y, 0, -6);
                    //     // echo ($x == $DATA['profil']['CPM_PERUNTUKAN']) ? "<option value='{$x}' class='ini2 hide'>{$y}</option>" : "<option class='ini2 hide' value='{$x}' >{$y}</option>";
                    // }
                    ?>
                   <tr valign="top">
                        <td width="200">Peruntukan Air</td>
                        <td> :
                            <select class="form-control" style="width:150px;height:30px;display:inline-block;font-size:small;" name="PAJAK[CPM_PERUNTUKAN]" id="CPM_PERUNTUKAN">
                                <?php
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$DATA['pajak']['CPM_PERUNTUKAN']}' selected>{$DATA['pajak']['CPM_PERUNTUKAN']}</option>";
                                    } else {
                                        if ($DATA['profil']['CPM_PERUNTUKAN'] != "") {
                                            foreach ($list_npa as $x => $y) {
                                                echo ($x == $DATA['profil']['CPM_PERUNTUKAN']) ? "<option value='{$x}' class='ini show' selected>{$y}</option>" : "<option value='{$x}' class='ini show'>{$y}</option>";
                                            }

                                            foreach ($list_npa2 as $x => $y) {
                                                // $y = substr($y, 0, -6);
                                            //   var_dump($y);die;
                                                echo ($x == $DATA['profil']['CPM_PERUNTUKAN']) ? "<option value='{$x}' class='ini2 hide'>{$y}</option>" : "<option class='ini2 hide' value='{$x}' >{$y}</option>";
                                            }
                                        } else {
                                            foreach ($list_npa as $x => $y) {
                                                echo ($x == $DATA['pajak']['CPM_PERUNTUKAN']) ? "<option value='{$x}' class='ini show' selected>{$y}</option>" : "<option value='{$x}' class='ini show'>{$y}</option>";
                                            }

                                            foreach ($list_npa2 as $x => $y) {
                                                $y = substr($y, 0, -6);
                                                echo ($x == $DATA['pajak']['CPM_PERUNTUKAN']) ? "<option value='{$x}' class='ini2 hide'>{$y}</option>" : "<option class='ini2 hide' value='{$x}'>{$y}</option>";
                                            }
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_PERUNTUKAN']}' selected>{$DATA['pajak']['CPM_PERUNTUKAN']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <br>
                            <?php if ($DATA['pajak']['CPM_PERUNTUKAN'] == 'Industri Besar (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Industri Kecil (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Niaga Besar (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Niaga Kecil (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Perkebunan, Perikanan, Peternakan (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Usaha Lain Yang Bersifat Komersil/Industri Minuman (Lama)' || $DATA['pajak']['CPM_PERUNTUKAN'] == 'Sosial (Lama)') :
                            ?>
                                <input type="radio" value="rbaru" name="rumus" id="rbaru" onclick="onLoad()">
                                <label for="size2">Rumus Baru</label>
                                <input type="radio" value="rlama" name="rumus" id="rlama" onclick="onLoad2();" checked>
                                <label for="size1">Rumus Lama</label>
                        </td>
                                <?php else : ?>
                                <input type="radio" value="rbaru" name="rumus" id="rbaru" onclick="onLoad()" checked>
                                <label for="size2">Rumus Baru</label>
                                <input type="radio" value="rlama" name="rumus" id="rlama" onclick="onLoad2();">
                                <label for="size1">Rumus Lama</label>
            </td>
        <?php endif; ?>
        </td>
        <td width="200" rowspan="9">Keterangan :</br>
            <textarea class="form-control" style="display:inline-block;" name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" rows="7" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Keterangan"><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
        </td>
        </tr>
        <tr valign="top">
            <td>No Pelaporan Pajak <b class="isi">*</b></td>
            <td> : <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input type=\"text\" class=\"form-control\" style=\"width:200px;height:30px;display:inline-block;\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "..." ?></td>
        </tr>
        <tr>
            <td>Tipe Pajak <b class="isi">*</b></td>
            <td> :
                <select class="form-control" style="width:200px;height:30px;display:inline-block;" name="PAJAK[CPM_TIPE_PAJAK]" id="CPM_TIPE_PAJAK">
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
                <select class="form-control" style="width:200px;height:30px;display:inline-block;" name="PAJAK[CPM_TAHUN_PAJAK]" id="CPM_TAHUN_PAJAK">
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
                <select class="form-control" style="width:200px;height:30px;display:inline-block;" name="PAJAK[CPM_TYPE_MASA]" id="CPM_TYPE_MASA">
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
                <select class="form-control" style="width:200px;height:30px;display:inline-block;" id="CPM_MASA_PAJAK">
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
            <?php
            $timestamp = strtotime($DATA['pajak']['CPM_TGL_JATUH_TEMPO']);
            $formattedDate = date('Y-m-d', $timestamp);
            $formattedDate = $formattedDate == '1970-01-01' ? 'YYYY-MM-DD' : $formattedDate;
            ?>

            <td width="200" colspan="2">Tanggal Jatuh Tempo <sup style="color:red; font-size:10px">(Tidak wajib)</sup></br>
                <input type="date" name="PAJAK[CPM_TGL_JATUH_TEMPO]" id="CPM_TGL_JATUH_TEMPO" value="<?= $formattedDate ?>" placeholder="Masa Akhir" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> style="width: 170;">
            </td>
        </tr>
        <tr valign="top">
            <!-- <td width="250">Volume Air yang diambil <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_VOLUME_AIR]" id="CPM_VOLUME_AIR" class="number SUM2" maxlength="11" value="<?php echo $DATA['pajak']['CPM_VOLUME_AIR'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Volume Air"> m<sup>3</sup></td> -->
            <td>Perolehan Air</td>
            <td colspan="3">
                <div id="data-perolehan" style="border-top:1px solid #ccc">
                    <div id="item-perolehan-1" style="border-bottom:1px solid #ccc;padding-bottom:6px">
                        <table>
                            <tr>
                                <td>Bulan</td>
                                <td> :
                                    <span id="bulan-perolehan-1"><?php echo  $bulan; ?></span> <span id="tahun-perolehan-1"><?php echo  $DATA['pajak']['CPM_TAHUN_PAJAK'] ?></span>
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_BULAN][]" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BULAN'] ?>" id="CPM_ATR_BULAN-1">
                                </td>
                            </tr>
                            <tr>
                                <td>Volume Air</td>
                                <td> :
                                    <input type="text" name="PAJAK_ATR[CPM_ATR_VOLUME][]" id="CPM_ATR_VOLUME-1" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_VOLUME'] ?>" class="CPM_VOLUME_AIR number" onkeyup="hitungNPA(1)" maxlength="14" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Volume Air"> m<sup>3</sup>
                                </td>
                            </tr>
                        </table>
                        <div id="tabel_perolehan-1"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_PERHITUNGAN'] ?></div>
                        <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TOTAL'] ?>" id="CPM_ATR_TOTAL-1">
                        <textarea name="PAJAK_ATR[CPM_ATR_PERHITUNGAN][]" style="display:none" id="CPM_ATR_PERHITUNGAN-1"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_PERHITUNGAN'] ?></textarea>
                    </div>

                    <div id="item-perolehan-2" style="<?php echo (isset($DATA['pajak_atr'][1]['CPM_ATR_TOTAL']) ? $DATA['pajak_atr'][1]['CPM_ATR_TOTAL'] : '') == '' ? 'display:none;' : '' ?>border-bottom:1px solid #ccc;padding-bottom:6px"">
                                <table>
                                <tr>
                                    <td>Bulan</td>
                                    <td> :
                                        <span id=" bulan-perolehan-2"><?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_BULAN']) ? $lapor->arr_bulan[$DATA['pajak_atr'][1]['CPM_ATR_BULAN']] : '' ?></span> <span id="tahun-perolehan-2"><?php echo  $DATA['pajak']['CPM_TAHUN_PAJAK'] ?></span>
                        <input type="hidden" name="PAJAK_ATR[CPM_ATR_BULAN][]" value="<?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_BULAN']) ? $DATA['pajak_atr'][1]['CPM_ATR_BULAN'] : 0 ?>" id="CPM_ATR_BULAN-2">
            </td>
        </tr>
        <tr>
            <td>Volume Air</td>
            <td> :
                <input type="text" name="PAJAK_ATR[CPM_ATR_VOLUME][]" id="CPM_ATR_VOLUME-2" value="<?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_VOLUME']) ? $DATA['pajak_atr'][1]['CPM_ATR_VOLUME'] : 0 ?>" class="CPM_VOLUME_AIR number" onkeyup="hitungNPA(2)" maxlength="14" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Volume Air"> m<sup>3</sup>
            </td>
        </tr>
    </table>
    <div id="tabel_perolehan-2"><?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_PERHITUNGAN']) ? $DATA['pajak_atr'][1]['CPM_ATR_PERHITUNGAN'] : '' ?></div>
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" value="<?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_TOTAL']) ? $DATA['pajak_atr'][1]['CPM_ATR_TOTAL'] : 0 ?>" id="CPM_ATR_TOTAL-2">
    <textarea name="PAJAK_ATR[CPM_ATR_PERHITUNGAN][]" style="display:none" id="CPM_ATR_PERHITUNGAN-2"><?php echo isset($DATA['pajak_atr'][1]['CPM_ATR_PERHITUNGAN']) ? $DATA['pajak_atr'][1]['CPM_ATR_PERHITUNGAN'] : '' ?></textarea>
    </div>

    <div id="item-perolehan-3" style="<?php echo (isset($DATA['pajak_atr'][2]['CPM_ATR_TOTAL']) ? $DATA['pajak_atr'][2]['CPM_ATR_TOTAL'] : '') == '' ? 'display:none;' : '' ?>border-bottom:1px solid #ccc;padding-bottom:6px"">
                                <table>
                                <tr>
                                    <td>Bulan</td>
                                    <td> :
                                        <span id=" bulan-perolehan-3"><?php echo  isset($DATA['pajak_atr'][2]['CPM_ATR_BULAN']) ? $lapor->arr_bulan[$DATA['pajak_atr'][2]['CPM_ATR_BULAN']] : '' ?></span> <span id="tahun-perolehan-3"><?php echo  $DATA['pajak']['CPM_TAHUN_PAJAK'] ?></span>
        <input type="hidden" name="PAJAK_ATR[CPM_ATR_BULAN][]" value="<?php echo  isset($DATA['pajak_atr'][2]['CPM_ATR_BULAN']) ? $DATA['pajak_atr'][2]['CPM_ATR_BULAN'] : 0 ?>" id="CPM_ATR_BULAN-3">
        </td>
        </tr>
        <tr>
            <td>Volume Air</td>
            <td> :
                <input type="text" name="PAJAK_ATR[CPM_ATR_VOLUME][]" id="CPM_ATR_VOLUME-3" value="<?php echo  isset($DATA['pajak_atr'][2]['CPM_ATR_VOLUME']) ? $DATA['pajak_atr'][2]['CPM_ATR_VOLUME'] : 0 ?>" class="CPM_VOLUME_AIR number" onkeyup="hitungNPA(3)" maxlength="14" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Volume Air"> m<sup>3</sup>
            </td>
        </tr>
        </table>
        <div id="tabel_perolehan-3"><?php echo (isset($DATA['pajak_atr'][2]['CPM_ATR_PERHITUNGAN']) ? $DATA['pajak_atr'][2]['CPM_ATR_PERHITUNGAN'] : '') ?></div>
        <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" value="<?php echo  isset($DATA['pajak_atr'][2]['CPM_ATR_TOTAL']) ? $DATA['pajak_atr'][2]['CPM_ATR_TOTAL'] : 0 ?>" id="CPM_ATR_TOTAL-3">
        <textarea name="PAJAK_ATR[CPM_ATR_PERHITUNGAN][]" style="display:none" id="CPM_ATR_PERHITUNGAN-3"><?php echo (isset($DATA['pajak_atr'][2]['CPM_ATR_PERHITUNGAN']) ? $DATA['pajak_atr'][2]['CPM_ATR_PERHITUNGAN'] : '') ?></textarea>
    </div>

    </div>
    </td>
    </tr>
    <tr>
        <td>Pembayaran Pemakaian <b class="isi">*</b></td>
        <td> : <input style="width:200px;height:30px;" type="text" name="PAJAK[CPM_TOTAL_OMZET]" id="CPM_TOTAL_OMZET" class="number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" readonly placeholder="Pembayaran Pemakaian">
            <textarea style="display:none" id="NPA_DETAIL" name="PAJAK[CPM_NPA]"></textarea>
        </td>
    </tr>
    <tr>
        <td>Dasar Pengenaan Pajak (DPP)</td>
        <td> : <input style="width:200px;height:30px;" type="text" name="PAJAK[CPM_DPP]" id="CPM_DPP" class="number" readonly maxlength="17" value="<?php echo $DATA['pajak']['CPM_DPP'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Dasar Pengenaan Pajak (DPP)"></td>
        <td>
            <label>
                <input type="checkbox" id="HITUNG_PENGURANGAN">
                Pengurangan
            </label>
        </td>
    </tr>
<?php 
// var_dump($DATA);die;
 ?>
    <tr>
        <td>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x bulan keterlambatan</td>
        <td> : <input type="text" style="width:200px;height:30px;" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? ($editable_terlambat_lap == 1 ? "" : "readonly")  : "readonly"; ?> class="SUM number" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor"></td>

        <td><input type="text" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PENGURANGAN" class="number SUM" value="<?php echo $payment_flag['pengurangan'] ?>" style="width: 50px;" readonly placeholder="Pengurangan"> %
        </td>
    </tr>
    <tr>
        <td>Jumlah Pajak yang dibayar <b class="isi">*</b></td>
        <td> : <input type="text" style="width:200px;height:30px;" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="number" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar"></td>
    </tr>
    <tr>
        <td colspan="4">Terbilang : <b><input class="form-control" type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly style="width: 800px;height:30px;display:inline-block;" value="<?php echo ucwords($DATA['pajak']['CPM_TERBILANG']) ?>" placeholder="Terbilang"></b></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <?php
    echo $ttd_penanggung_jawab;
    ?>
    </table>
    </td>
    </tr>
    <?php
    if ($lapor->_mod == "ver" && $lapor->_s == 2) {
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
        echo '<tr><td><b style="font-size:14px">2. Rekapitulasi Pemanfaatan Air' . '</b></td><td><b style="font-size:14px"> : ' . getImage(5, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '<tr><td><b style="font-size:14px">3. Fotocopy SIPA, KTP, SIUP' . '</b></td><td><b style="font-size:14px"> : ' . getImage(6, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '<tr><td><b style="font-size:14px">4. Foto Water Meter' . '</b></td><td><b style="font-size:14px"> : ' . getImage(7, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '<tr><td><b style="font-size:14px">5. SKPengurangan' . '</b></td><td><b style="font-size:14px"> : ' . getImage(9, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '<tr><td><b style="font-size:14px">6. NPWP/NPWPD' . '</b></td><td><b style="font-size:14px"> : ' . getImage(8, $DATA['pajak']['CPM_NO']) . '</b></td></tr>';
        echo '</table></div>
						  <div class="modal-footer">
							<button type="button" class="btn btn-secondary"  data-dismiss="modal">Close</button>
						  </div>
						</div>
					  </div>
					</div>
				</td>
			</tr>';
            $berkass = getIDBerkas($DATA['pajak']['CPM_NO']);
			$base644 = "a=aPatda&m=mPatdaPelayanan&f=fPatdaBerkas&id={$berkass}&sts=0&read=1";
			$urlberkas = "main.php?param=" . base64_encode($base644);
            $linkberkas = "<a href=\"{$urlberkas}\" title=\"Klik untuk detail\"> <button type=\"button\" class=\"btn btn-success\">Upload Berkas</button</a>";
        echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>VERIFIKASI</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td ><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                                <td>  
                                {$linkberkas}
                                </td>
                                 <td width=\"100\" align=\"right\" colspan=\"2\">
									<button type=\"button\"  data-toggle=\"modal\" data-target=\"#exampleModalCenter\" class=\"btn btn-secondary\">
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
    } else if ($lapor->_mod == "ver2" && $lapor->_s == 6) {
        echo "<tr>
                            <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>Verifikasi 2</b></td>
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
                        // kondisi untuk tunggakan
                        // if($DATA['jml_tunggak'] < 1) {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">\n
                            <input type=\"button\" class=\"btn-submit\" action=\"save_final\" value=\"Simpan dan Finalkan\">";
                        // }
                        // end kondisi untuk tunggakan
                    }
                } elseif ($lapor->_s == 4 && $lapor->_flg == 0) {
                    echo "<input type=\"reset\" value=\"Reset\">\n ";
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"new_version\" value=\"Simpan versi Baru\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"new_version_final\" value=\"Simpan versi baru dan Finalkan\">";
                } elseif (in_array($lapor->_s, array(3, 5))) {
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_npa\" value=\"Cetak NPA\">";
                    // echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
                } elseif (in_array($lapor->_s, array(2))) {
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                }
            } elseif ($lapor->_mod == "ver") {
                if ($lapor->_s == 2)
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
            } elseif ($lapor->_mod == "ver2") {
                if ($lapor->_s == 6)
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi_2\" value=\"Proses Dokumen\">";
            } elseif ($lapor->_mod == "per") {
                if ($lapor->_s == 3)
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
            } elseif ($lapor->_mod == "ply") {
                if ($lapor->_s == 5)
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                if ($payment_flag['payment_flag']) {
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpdn\" value=\"Cetak SKPDN\">";
                }
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

    //     $(".ini").removeClass("show");
    //     $(".ini").addClass("hide");

    //     $(".ini2").removeClass("hide");
    //     $(".ini2").addClass("show");
    //     document.getElementById("CPM_PERUNTUKAN").selectedIndex = "0";
    //     $('#CPM_ATR_VOLUME-1').val(0);


    // }

        $(".ini").removeClass("show");
        $(".ini").addClass("hide");

        $(".ini2").removeClass("hide");
        $(".ini2").addClass("show");
        document.getElementById("CPM_PERUNTUKAN").selectedIndex = "2";
        $('#CPM_ATR_VOLUME-1').val(0);
    }

    // $(document).ready(function() {
    //     $("#rbaru").click(function() {
    //         $("#rbaru").prop("checked", true);
    //     });
    //     $("#rlama").click(function() {
    //         $("#rlama").prop("checked", true);
    //     });

    //     $(".tunggakan").click(function() {
    //         alert("Ada Tunggakan sebanyak <?= $DATA['jml_tunggak'] ?> tunggakan, mohon segera dilunasi terlebih dulu");
    //     });

    //     <?php
    //             if($DATA['jml_tunggak']>0) echo 'alert("Ada Tunggakan sebanyak '.$DATA['jml_tunggak'].' tunggakan, mohon segera dilunasi terlebih dulu");'
            
    //         ?>
    // });
</script>