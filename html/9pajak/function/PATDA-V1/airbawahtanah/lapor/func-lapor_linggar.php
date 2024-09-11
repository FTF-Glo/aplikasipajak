<?php
$DIR = "PATDA-V1";
$modul = "airbawahtanah";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");
if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $lapor->get_previous_pajak($npwpd, $nop);
}
$lapor->get_previous_pajak($npwpd, $nop);
$npa = $lapor->list_npa();
$list_npa = $npa['combo'];

$npa2 = $lapor->list_npa2();
$list_npa2 = $npa2['combo'];
// var_dump($lapor);
// die;

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
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>
<style>
    .datepicker {
        background-color: #fff;
        border: 1px solid #ccc;
        padding: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .header {
        display: flex;
        justify-content: space-between;
    }

    .days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    span {
        display: block;
        text-align: center;
        cursor: pointer;
    }

    span.empty {
        visibility: hidden;
    }
</style>
<!-- <pre> -->
<?php //die(print_r($DATA))
?>
<form class="cmxform" autocomplete="off" id="form-lapor" method="post" enctype="multipart/form-data" action="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>" enctype="multipart/form-data">
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
        <div class=\"row\" class=\"child\">
            <div class=\"col-md-12\">
                <div class=\"lm-subtitle;\">TTD Penangung Jawab</div>
                <hr />
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-md-6\">
                <div class=\"form-group\">
                    <label>Mengetahui :</label>
                    <select id=\"PEJABAT2\" class=\"form-control\" name=\"PAJAK[CPM_PEJABAT_MENGETAHUI]\">{$opt_pejabat}</select>
                </div>
            </div>
            <div class=\"col-md-6\">
                <div class=\"form-group\">
                    <label>Tanggal Pengesahan :</label>
                    <input type='date' class=\"form-control\" name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' >
                </div>
            </div>
        </div>";
    }
    ?>
    <div class="container lm-container">
        <div class="row">
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>PROFIL PAJAK AIR BAWAH TANAH </b>
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
                        <input type="hidden" id="TBLJNSPJK" value="AIRBAWAHTANAH">
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
                            base64_encode('a=' . $a . '&m=mPatdaPelayananPelapor1&f=fPatdaPelayananLaporOP1&npwpd=' . $npwpd . '&npwpd=' . $npwpd . '&nop=' . $nop);
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
            <div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
                <b>LAPOR PAJAK AIR BAWAH TANAH </b>
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
        <div class="row" class="child">
            <div class="col-md-12">
                <div class="lm-subtitle">Data Pajak</div>
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Peruntukan Air</label>
                            <select class="form-control" name="PAJAK[CPM_PERUNTUKAN]" id="CPM_PERUNTUKAN">
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

                                                echo ($x == $DATA['profil']['CPM_PERUNTUKAN']) ? "<option value='{$x}' class='ini2 hide'>{$y}</option>" : "<option class='ini2 hide' value='{$x}'>{$y}</option>";
                                            }
                                        } else {
                                            foreach ($list_npa as $x => $y) {
                                                echo ($x == $DATA['pajak']['CPM_PERUNTUKAN']) ? "<option value='{$x}' class='ini show' selected>{$y}</option>" : "<option value='{$x}' class='ini show'>{$y}</option>";
                                            }

                                            foreach ($list_npa2 as $x => $y) {

                                                echo ($x == $DATA['pajak']['CPM_PERUNTUKAN']) ? "<option value='{$x}' class='ini2 hide'>{$y}</option>" : "<option class='ini2 hide' value='{$x}'>{$y}</option>";
                                            }
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_PERUNTUKAN']}' selected>{$DATA['pajak']['CPM_PERUNTUKAN']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>No Pelaporan Pajak <b class="isi">*</b></label>
                            <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input class=\"form-control\" type=\"text\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "<span style=\"color:red\">Tidak Tersedia</span>" ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
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
                </div>
            </div>
            <div class="col-md-7">
                <label>Jenis Rumus</label>
                <div class="row">
                    <div class="col-md-3">
                        <?php if ($DATA['pajak']['CPM_RUMUS'] == 'rlama') : ?>
                            <input type="radio" value="rbaru" name="rumus" id="rbaru" onclick="onLoad()">
                            <label for="rbaru">Rumus Baru</label><br>
                            <input type="radio" value="rlama" name="rumus" id="rlama" onclick="onLoad2();" checked>
                            <label for="rlama">Rumus Lama</label>
                        <?php else : ?>
                            <input type="radio" value="rbaru" name="rumus" id="rbaru" onclick="onLoad()" checked>
                            <label for="rbaru">Rumus Baru</label> <br>
                            <input type="radio" value="rlama" name="rumus" id="rlama" onclick="onLoad2();">
                            <label for="rlama">Rumus Lama</label>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9">
                        <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" class="form-control" rows="10" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Keterangan"><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
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
            <div class="col-md-4">
                <div class="form-group">
                    <label>Type Masa</label>
                    <select class="form-control" name="PAJAK[CPM_TYPE_MASA]" id="CPM_TYPE_MASA">
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
                </div>
            </div>
            <div class="col-md-4">
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
                        if (!empty($lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']])) {
                            $bulan = $lapor->arr_bulan[$DATA['pajak']['CPM_MASA_PAJAK']];
                        } else {
                            $bulan = $lapor->arr_bulan[$DATA['pajak_atr'][0]['CPM_ATR_BULAN']];
                        }
                        ?>
                        <input type="hidden" name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK10" readonly class="number" value="<?php echo (int) $DATA['pajak']['CPM_MASA_PAJAK'] ?>">
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>Masa Pajak <b class="isi">*</b></label>
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="PAJAK[CPM_MASA_PAJAK1]" style="line-height: 1.45;" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK1\"" : ""; ?> readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK1']; ?>" placeholder="Masa Awal">
                        </div>
                        <div class="col-md-1">
                            s.d
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="PAJAK[CPM_MASA_PAJAK2]" style="line-height: 1.45;" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK2\"" : ""; ?> readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK2']; ?>" placeholder="Masa Akhir">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <?php
                    $timestamp = strtotime($DATA['pajak']['CPM_TGL_JATUH_TEMPO']);
                    $formattedDate = date('Y-m-d', $timestamp);
                    $formattedDate = $formattedDate == '1970-01-01' ? 'YYYY-MM-DD' : $formattedDate;
                    ?>
                    <label>Tanggal Jatuh Tempo <sup style="color:red; font-size:10px">(Tidak wajib)</sup></br></label>
                    <input type="date" name="PAJAK[CPM_TGL_JATUH_TEMPO]" id="CPM_TGL_JATUH_TEMPO" class="form-control" value="<?= $formattedDate ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-12">
                <label>Perolehan Air</label>
            </div>
            <div id="data-perolehan">
                <div id="item-perolehan-1" class="row" style="margin:0">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Bulan : </label>
                                        <div style="display: block">
                                            <span id="bulan-perolehan-1" style="text-align: left; display: inline"><?php echo  $bulan; ?></span>
                                            <span id="tahun-perolehan-1" style="text-align: left; display: inline"><?php echo  $DATA['pajak']['CPM_TAHUN_PAJAK'] ?></span>
                                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_BULAN][]" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BULAN'] ?>" id="CPM_ATR_BULAN-1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Volume Air : </label>
                                        <div class="col-sm-11" style="margin: 0; padding: 0">
                                            <input type="text" name="PAJAK_ATR[CPM_ATR_VOLUME][]" id="CPM_ATR_VOLUME-1" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_VOLUME'] ?>" class="form-control CPM_VOLUME_AIR number" style="display: inline; width: 100%" onkeyup="hitungNPA(1)" maxlength="14" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Volume Air">
                                        </div>
                                        <div class="col-sm-1" style="margin-left: 0; padding: 7px 5px">
                                            m<sup>3</sup>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="form-group">
                            <div id="tabel_perolehan-1"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_PERHITUNGAN'] ?></div>
                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TOTAL'] ?>" id="CPM_ATR_TOTAL-1">
                            <textarea name="PAJAK_ATR[CPM_ATR_PERHITUNGAN][]" style="display:none" id="CPM_ATR_PERHITUNGAN-1"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_PERHITUNGAN'] ?></textarea>
                        </div>
                    </div>
                </div>
                <div id="item-perolehan-2" class="row" style="<?php echo (isset($DATA['pajak_atr'][1]['CPM_ATR_TOTAL']) ? $DATA['pajak_atr'][1]['CPM_ATR_TOTAL'] : '') == '' ? 'display:none; margin: 0' : 'margin: 0' ?>">
                    <hr />
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Bulan : </label>
                                        <div style="display: block">
                                            <span id="bulan-perolehan-2" style="text-align: left; display: inline"><?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_BULAN']) ? $lapor->arr_bulan[$DATA['pajak_atr'][1]['CPM_ATR_BULAN']] : '' ?></span>
                                            <span id="tahun-perolehan-2" style="text-align: left; display: inline"><?php echo  $DATA['pajak']['CPM_TAHUN_PAJAK'] ?></span>
                                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_BULAN][]" value="<?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_BULAN']) ? $DATA['pajak_atr'][1]['CPM_ATR_BULAN'] : 0 ?>" id="CPM_ATR_BULAN-2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Volume Air : </label>
                                        <div class="col-sm-11" style="margin: 0; padding: 0">
                                            <input type="text" name="PAJAK_ATR[CPM_ATR_VOLUME][]" id="CPM_ATR_VOLUME-2" value="<?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_VOLUME']) ? $DATA['pajak_atr'][1]['CPM_ATR_VOLUME'] : 0 ?>" class="form-control CPM_VOLUME_AIR number" style="display: inline; width: 100%" onkeyup="hitungNPA(2)" maxlength="14" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Volume Air">
                                        </div>
                                        <div class="col-sm-1" style="margin-left: 0; padding: 7px 5px">
                                            m<sup>3</sup>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="form-group">
                            <div id="tabel_perolehan-2"><?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_PERHITUNGAN']) ? $DATA['pajak_atr'][1]['CPM_ATR_PERHITUNGAN'] : '' ?></div>
                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" value="<?php echo  isset($DATA['pajak_atr'][1]['CPM_ATR_TOTAL']) ? $DATA['pajak_atr'][1]['CPM_ATR_TOTAL'] : 0 ?>" id="CPM_ATR_TOTAL-2">
                            <textarea name="PAJAK_ATR[CPM_ATR_PERHITUNGAN][]" style="display:none" id="CPM_ATR_PERHITUNGAN-2"><?php echo isset($DATA['pajak_atr'][1]['CPM_ATR_PERHITUNGAN']) ? $DATA['pajak_atr'][1]['CPM_ATR_PERHITUNGAN'] : '' ?></textarea>
                        </div>
                    </div>
                </div>
                <div id="item-perolehan-3" class="row" style="<?php echo (isset($DATA['pajak_atr'][2]['CPM_ATR_TOTAL']) ? $DATA['pajak_atr'][2]['CPM_ATR_TOTAL'] : '') == '' ? 'display:none;margin: 0' : 'margin: 0' ?>">
                    <hr />
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Bulan : </label>
                                        <div style="display: block">
                                            <span id="bulan-perolehan-3" style="text-align: left; display: inline"><?php echo  isset($DATA['pajak_atr'][2]['CPM_ATR_BULAN']) ? $lapor->arr_bulan[$DATA['pajak_atr'][2]['CPM_ATR_BULAN']] : '' ?></span>
                                            <span id="tahun-perolehan-3" style="text-align: left; display: inline"><?php echo  $DATA['pajak']['CPM_TAHUN_PAJAK'] ?></span>
                                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_BULAN][]" value="<?php echo  isset($DATA['pajak_atr'][2]['CPM_ATR_BULAN']) ? $DATA['pajak_atr'][2]['CPM_ATR_BULAN'] : 0 ?>" id="CPM_ATR_BULAN-3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Volume Air : </label>
                                        <div class="col-sm-11" style="margin: 0; padding: 0">
                                            <input type="text" name="PAJAK_ATR[CPM_ATR_VOLUME][]" id="CPM_ATR_VOLUME-3" value="<?php echo  isset($DATA['pajak_atr'][2]['CPM_ATR_VOLUME']) ? $DATA['pajak_atr'][2]['CPM_ATR_VOLUME'] : 0 ?>" class="form-control CPM_VOLUME_AIR number" style="display: inline; width: 100%" onkeyup="hitungNPA(3)" maxlength="14" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Volume Air">
                                        </div>
                                        <div class="col-sm-1" style="margin-left: 0; padding: 7px 5px">
                                            m<sup>3</sup>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="form-group">
                            <div id="tabel_perolehan-3"><?php echo (isset($DATA['pajak_atr'][2]['CPM_ATR_PERHITUNGAN']) ? $DATA['pajak_atr'][2]['CPM_ATR_PERHITUNGAN'] : '') ?></div>
                            <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" value="<?php echo  isset($DATA['pajak_atr'][2]['CPM_ATR_TOTAL']) ? $DATA['pajak_atr'][2]['CPM_ATR_TOTAL'] : 0 ?>" id="CPM_ATR_TOTAL-3">
                            <textarea name="PAJAK_ATR[CPM_ATR_PERHITUNGAN][]" style="display:none" id="CPM_ATR_PERHITUNGAN-3"><?php echo (isset($DATA['pajak_atr'][2]['CPM_ATR_PERHITUNGAN']) ? $DATA['pajak_atr'][2]['CPM_ATR_PERHITUNGAN'] : '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <tr>
            <td>Pembayaran Pemakaian <b class="isi">*</b></td>
            <td> : <input type="text" name="PAJAK[CPM_TOTAL_OMZET]" id="CPM_TOTAL_OMZET" class="number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" readonly placeholder="Pembayaran Pemakaian"></td>
        </tr>
        <!-- <tr>
        <td>Pembayaran Lain-lain</td>
        <td> : <input type="text" name="PAJAK[CPM_BAYAR_LAINNYA]" id="CPM_BAYAR_LAINNYA" class="number SUM" maxlength="17" value="<?php echo isset($DATA['pajak']['CPM_BAYAR_LAINNYA']) ? $DATA['pajak']['CPM_BAYAR_LAINNYA'] : 0 ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Lain-lain"></td>
    </tr> -->
        <tr>
            <td>Dasar Pengenaan Pajak (DPP)</td>
            <td> : <input type="text" name="PAJAK[CPM_DPP]" id="CPM_DPP" class="number" readonly maxlength="17" value="<?php echo $DATA['pajak']['CPM_DPP'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Dasar Pengenaan Pajak (DPP)"></td>
        </tr>
        <!--
                    <tr>
                        <td>Tarif Pajak <b class="isi">*</b></td>
                        <td> : --> <input type="hidden" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="0" placeholder="Tarif Pajak">
        <!-- %
                        </td>
                    </tr>
                    <tr>
                        <td>Pembayaran Terutang (Tarif x DPP)</td>
                        <td>  :--> <input type="hidden" name="PAJAK[CPM_BAYAR_TERUTANG]" id="CPM_BAYAR_TERUTANG" readonly class="number" maxlength="17" value="<?php echo $DATA['pajak']['CPM_BAYAR_TERUTANG'] ?>" readonly placeholder="Pembayaran Terutang">
        <!-- </td>
                    </tr> -->
        <tr>
            <td>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x bulan keterlambatan</td>
            <td> : <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || $lapor->_s == 4) ?  "" : "readonly" ?> class="SUM2 number" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor"></td>
        </tr>
        <tr>
            <td>Jumlah Pajak yang dibayar <b class="isi">*</b></td>
            <td> : <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="number" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar"></td>
        </tr>
        <tr>
            <td colspan="4">Terbilang : <b><input class="form-control" style="width:800px;height:30px;display:inline-block;" type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly value="<?php echo ucwords($DATA['pajak']['CPM_TERBILANG']) ?>" placeholder="Terbilang"></b></td>
        </tr>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Pembayaran Pemakaian <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TOTAL_OMZET]" id="CPM_TOTAL_OMZET" class="form-control SUM" style="text-align: right" maxlength="17" value="<?php echo $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" readonly placeholder="Pembayaran Pemakaian">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Dasar Pengenaan Pajak (DPP)</label>
                    <input type="text" name="PAJAK[CPM_DPP]" id="CPM_DPP" class="form-control" style="text-align: right" readonly maxlength="17" value="<?php echo $DATA['pajak']['CPM_DPP'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Dasar Pengenaan Pajak (DPP)">
                </div>
            </div>
            <div class="col-md-4">

                <div class="form-group">
                    <label>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x bulan keterlambatan</label>
                    <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" class="form-control" style="text-align: right" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || $lapor->_s == 4) ?  "" : "readonly" ?> class="SUM2 number" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">

                <div class="form-group">
                    <label>Jumlah Pajak yang dibayar <b class="isi">*</b></label>
                    <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="form-control" style="text-align: right" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar">
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label>Terbilang</label>
                    <b><input class="form-control" style="text-align: right" type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly value="<?php echo ucwords($DATA['pajak']['CPM_TERBILANG']) ?>" placeholder="Terbilang"></b>
                </div>
            </div>
        </div>

        <?php echo $ttd_penanggung_jawab; ?>

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
                    } elseif (in_array($lapor->_s, array(3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_npa\" value=\"Cetak NPA\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                    } elseif (in_array($lapor->_s, array(2))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_npa\" value=\"Cetak NPA\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
                    }
                } elseif ($lapor->_mod == "ver") {
                    if ($lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "per") {
                    if ($lapor->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
                } elseif ($lapor->_mod == "ply") {
                    if ($lapor->_s == 5 || $lapor->_s == 2)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                    echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                }
                ?>
            </div>
        </div>
    </div>
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