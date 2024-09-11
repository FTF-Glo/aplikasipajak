<?php
$DIR = "PATDA-V1";
$modul = "reklame";
require_once("inc/payment/json.php");
require_once("inc/payment/sayit.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");

$list_tarif = $lapor->list_tarif();

$list_type_masa = $lapor->get_type_masa();
$list_kawasan = $lapor->get_kawasan();
$list_jalan = $lapor->get_jalan();
$list_sudut_pandang = $lapor->get_sudut_pandang();

//$list_type_masa = array(1=>'Tahun', 4=>'Bulan'); //$lapor->get_type_masa();
//$list_kawasan = $list_tarif['lokasi'];
//$list_jalan = $lapor->get_jalan();
//$list_sudut_pandang = $lapor->get_sudut_pandang();
$list_rekening = $lapor->get_list_rekening();
$count = count($DATA['pajak_atr']);
//var_dump($DATA['pajak_atr']);
// echo '<pre>',print_r($DATA['pajak_atr']),'</pre>';

if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $DATA = $lapor->get_previous_pajak($npwpd, $nop);
    //var_dump($DATA);die;
}

//list pejabat
$list_pejabat = $lapor->get_pejabat();
foreach ($list_pejabat as $list_pejabat) {
    $opt_pejabat .= "<option value=\"{$list_pejabat['CPM_KEY']}\">{$list_pejabat['CPM_NIP']} - {$list_pejabat['CPM_NAMA']}</option>";
}
?>

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/<?php echo $DIR; ?>/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/js/terbilang.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor-piutang.js"></script>
<script type="text/javascript" src="inc/<?php echo "{$DIR}"; ?>/select2/js/select2.min.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>

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
    <input type="hidden" name="PAJAK[CPM_PIUTANG]" value="1">

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
            <div id="subMenu" align="center" class="col-md-12 subtitle lm-title">
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
                                <input type="button" class="btn btn-primary lm-btn" style="margin-top: 10px" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo  $prm ?>'">
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php
                        if (!empty($DATA['profil']['CPM_NPWPD']) && empty($DATA['pajak']['CPM_ID'])) {
                            //$addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
                            $addOp = 'fPatdaPelayananLaporOP7';
                            $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';
                            echo '<input type="button" class="btn btn-primary lm-btn" style="margin-top: 10px" value="Tambah NOP" onclick="location.href=\'' . $prm . '\'">';
                        } ?>
                    <?php else : ?>
                        <label>NPWPD <b class="isi">*</b></label>
                        <input type="hidden" id="TBLJNSPJK" value="REKLAME">
                        <select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" style="width: 90%"></select>
                        <label id="loading"></label>
                    <?php endif; ?>
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
                    <textarea name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" rows="3" style="min-width: 100%" readonly><?php echo  $DATA['profil']['CPM_ALAMAT_WP'] ?></textarea>
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
                        <a href="<?php echo $prm ?>" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
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
            <div class="col-md-12">
                <div class="form-group">
                    <div class="row">
                        <div id="subMenu" align="center" class="col-md-12 subtitle lm-title">
                            <b>LAPOR PAJAK REKLAME</b>
                        </div>
                    </div>
                </div>
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
            <div class="col-md-4">
                <div class="form-group">
                    <label>No Pelaporan Pajak <b class="isi">*</b></label>
                    <input name="PAJAK[CPM_NO]" type="text" id="CPM_NO" class="form-control" value="<?php echo $DATA['pajak']['CPM_NO'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "") ? "" : "readonly"; ?> placeholder="No Pelaporan">
                </div>
                <div class="form-group">
                    <label>Pilih OP <b class="isi">*</b></label>
                    <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                        <select name="PAJAK_ATR[CPM_ATR_NOP][]" tabindex="14" id="CPM_NOP" class="form-control CPM_NOP">
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
                        <input type="text" class="form-control" value="<?php echo $DATA['pajak_atr'][0]['CPM_NOP'], ' - ', $DATA['pajak_atr'][0]['CPM_NAMA_OP'] ?>" readonly />
                        <input type="hidden" name="PAJAK_ATR[CPM_ATR_NOP][]" id="CPM_NOP-'.$no.'" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_ID_PROFIL'] ?>" readonly />
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label>Keterangan :</label>
                    <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" class="form-control" rows="5" style="min-width: 100%" tabindex="13" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>><?php echo  $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Pilih rekening <b class="isi">*</b></label>
                    <select tabindex="15" name="PAJAK_ATR[CPM_ATR_REKENING][]" id="CPM_ATR_REKENING" class="form-control">
                        <?php
                        if ($lapor->_s == "") echo "<option data-nmrek='' data-tarif='0' data-harga='0' value='' selected>Pilih Rekening</option>";
                        foreach ($list_rekening as $rek) {
                            /* <option data-nmrek='' data-tarif='0' data-harga='0'value="" disabled>Pilih Rekening</option>
                                <option value='<?php echo $rek->kdrek?>'
                                data-nmrek='<?php echo $rek->nmrek?>'
                                data-tarif='<?php echo $rek->tarif1?>'
                                data-harga='<?php echo $rek->tarif2?>'
                                data-tinggi='<?php echo $rek->tarif3?>'
                                <?php echo $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek? 'selected' : ''?>
                                <?php echo (empty($DATA['pajak']['CPM_ID']) || $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek)? '' : 'disabled'?>
                                ><?php echo $rek->kdrek.' - '.$rek->nmrek?></option>
                            <?php } */
                            $selected = $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek ? ' selected' : '';
                            $disabled = (empty($DATA['pajak']['CPM_ID']) || $DATA['pajak_atr'][0]['CPM_ATR_REKENING'] == $rek->kdrek) ? '' : ' disabled';
                            echo "<option value='{$rek->kdrek}' data-nmrek='{$rek->nmrek}' data-tarif='{$rek->tarif1}' data-harga='{$rek->tarif2}'
                                data-tinggi='{$rek->tarif3}'{$selected}{$disabled}>{$rek->kdrek} - {$rek->nmrek}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tahun Pajak <b class="isi">*</b></label>
                    <select name="PAJAK[CPM_TAHUN_PAJAK]" class="form-control" tabindex="10">
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
            <div class="col-md-6">
                <div class="form-group">
                    <label>Masa Pajak <b class="isi">*</b></label>
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AWAL][]" id='CPM_ATR_BATAS_AWAL' class="form-control" readonly value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "class='datepicker'" : "readonly"; ?> placeholder="Batas Awal" tabindex="11" title="Batas Awal">
                        </div>
                        <div class="col-md-1">
                            s.d
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AKHIR][]" id='CPM_ATR_BATAS_AKHIR' class="form-control" readonly value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "class='datepicker'" : "readonly"; ?> placeholder="Batas Akhir" tabindex="12" title="Batas Akhir">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Total Pembayaran</label>
                    <input name="PAJAK[CPM_TOTAL_PAJAK]" type="text" id="CPM_TOTAL_PAJAK" class="form-control SUM" style="text-align: right;" value="<?php echo  $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Total Pembayaran">
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label>Terbilang</label>
                    <span id="terbilang" style="display: block"><?php echo  ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah" ?></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php
                    echo $ttd_penanggung_jawab;
                    ?>
                </div>
            </div>
        </div>

        <?php
        if ($lapor->_mod == "ver" && $lapor->_s == 2) {
            echo "
                <div class=\"row\">
                    <div class=\"col-md-12\">
                        <div class=\"lm-subtitle\">
                            VERIFIKASI
                            <hr/>
                        </div>
                    </div>
                </div>
                <div class=\"row\">
                    <div class=\"col-md-12\">
                        <div class=\"form-group\">
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>
                        <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" rows=\"3\" style=\"min-width: 100%\" readonly placeholder=\"Alasan penolakan\"></textarea>
                        </div>
                    </div>
                </div>";
        } else if ($lapor->_mod == "per" && $lapor->_s == 3) {
            echo "
                <div class=\"row\">
                    <div class=\"col-md-12\">
                        <div class=\"lm-subtitle\">
                            PERSETUJUAN
                            <hr/>
                        </div>
                    </div>
                    <div class=\"col-md-12\">
                        <div class=\"form-group\">
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label>
                        <label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\" style=\"margin-left: 10px\"> Tolak</label>
                        <textarea name=\"PAJAK[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" class=\"form-control\" rows=\"3\" style=\"min-width: 100%\" readonly placeholder=\"Alasan penolakan\"></textarea>
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

                            echo '
						<script>
								document.getElementById("ForCheck").checked = true;
								document.getElementById("ForCheck2").checked = true;
								document.getElementById("ForCheck3").checked = true;
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
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_bongkar\" value=\"Cetak Jaminan Bongkar\">";
                    } elseif (in_array($lapor->_s, array(2))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_bongkar\" value=\"Cetak Jaminan Bongkar\">";
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
<div class="modal"></div>

<script>
    var waktu = [];
    <?php

    /* if (($lapor->_id != "" && $lapor->_s == 1) || (isset($get_previous) && !empty($DATA['pajak']['CPM_TOTAL_PAJAK']))) {

		$waktu = $DATA['pajak_atr'][0];
		$hari = $waktu['CPM_ATR_JUMLAH_HARI'];
		$minggu = $waktu['CPM_ATR_JUMLAH_MINGGU'];
		$bulan = $waktu['CPM_ATR_JUMLAH_BULAN'];
		$tahun = $waktu['CPM_ATR_JUMLAH_TAHUN'];
		$semester = round($tahun / 2, 2);
		$triwulan = round($semester / 2, 2);

		echo "
		waktu = [{$tahun}, {$semester}, {$triwulan}, {$bulan}, {$minggu}, {$hari}];
		$(document).ready(function(){
			$('#CPM_ATR_REKENING').trigger( 'change' );
			calculation();
			load_first();
		});";
    } */
    ?>
</script>