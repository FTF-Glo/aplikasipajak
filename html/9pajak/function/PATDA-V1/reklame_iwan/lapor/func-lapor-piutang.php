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
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK REKLAME</b></td>
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
                    <?php
                    if (!empty($DATA['profil']['CPM_NPWPD']) && empty($DATA['pajak']['CPM_ID'])) {
                        //$addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
						$addOp = 'fPatdaPelayananLaporOP7';
                        $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';
                        echo '<input type="button" value="Tambah NOP" onclick="location.href=\'' . $prm . '\'">';
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
        <!--
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
            ?>
                <input type="button" value="Tambah NOP" onclick="location.href='<?php echo $prm ?>'">
            <?php } ?>
			</td>
		</tr>
        <tr>
            <td>Nama Objek Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo  $DATA['profil']['CPM_NAMA_OP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Objek Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="70" rows="3" readonly><?php echo  $DATA['profil']['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>
        <tr>
            <td>Kecamatan Objek Pajak <b class="isi">*</b></td>
            <td>:
				<input type="text" name="PAJAK[CPM_NAMA_KECAMATAN_OP]" id="CPM_NAMA_KECAMATAN_OP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_KECAMATAN_OP'] ?>" readonly placeholder="Kecamatan Objek Pajak">
				<input type="hidden" name="PAJAK[CPM_KECAMATAN_OP]" id="KECAMATAN_OP" value="<?php echo $DATA['profil']['CPM_KECAMATAN_OP'] ?>">
			</td>
        </tr>
        <tr>
            <td>Kelurahan Objek Pajak <b class="isi">*</b></td>
            <td>:
				<input type="text" name="PAJAK[CPM_NAMA_KELURAHAN_OP]" id="CPM_NAMA_KELURAHAN_OP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_KELURAHAN_OP'] ?>" readonly placeholder="Kelurahan Objek Pajak">
				<input type="hidden" name="PAJAK[CPM_KELURAHAN_OP]" id="KELURAHAN_OP" value="<?php echo $DATA['profil']['CPM_KELURAHAN_OP'] ?>">
			</td>
        </tr>
        -->
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
                        <td width="234">No Pelaporan Pajak <b class="isi">*</b></td>
						<td width="302">: <input name="PAJAK[CPM_NO]" type="text" id="CPM_NO" value="<?php echo $DATA['pajak']['CPM_NO'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "") ? "" : "readonly"; ?> placeholder="No Pelaporan"></td>
                        <td width="300" rowspan="5">Keterangan :</br>
                            <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" rows="4" tabindex="13" cols="40" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>><?php echo  $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                        </td>
                    </tr>
					<tr>
					<td align="left" width="198" valign="top">Pilih OP <b class="isi">*</b></td>
                        <td align="left" width="240" valign="top">
                            <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                                <select name="PAJAK_ATR[CPM_ATR_NOP][]" tabindex="14" id="CPM_NOP" class="CPM_NOP" style="max-width:260px">
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
                                <input type="text" style="width: 260px;" value="<?php echo $DATA['pajak_atr'][0]['CPM_NOP'], ' - ', $DATA['pajak_atr'][0]['CPM_NAMA_OP'] ?>" readonly />
                                <input type="hidden" name="PAJAK_ATR[CPM_ATR_NOP][]" id="CPM_NOP-'.$no.'" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_ID_PROFIL'] ?>" readonly />
                            <?php endif; ?>
					</tr>
                    <tr>
                        <td align="left" valign="top">Pilih rekening <b class="isi">*</b></td>
                        <td align="left" valign="top">
                            <select class="form-control" tabindex="15" name="PAJAK_ATR[CPM_ATR_REKENING][]" id="CPM_ATR_REKENING" style="width:260px">
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
                        </td>

                    </tr>
                    <tr>
                        <td width="234">Tahun Pajak <b class="isi">*</b></td>
                        <td> :
                            <select name="PAJAK[CPM_TAHUN_PAJAK]" tabindex="10">
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
                        <td>Masa Pajak <b class="isi">*</b></td>
                        <td> :
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AWAL][]" id='CPM_ATR_BATAS_AWAL' readonly style="width: 100px;" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "class='datepicker'" : "readonly"; ?> placeholder="Batas Awal" tabindex="11" title="Batas Awal">
                            s/d
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AKHIR][]" id='CPM_ATR_BATAS_AKHIR' readonly style="width: 100px;" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "class='datepicker'" : "readonly"; ?> placeholder="Batas Akhir" tabindex="12" title="Batas Akhir">
                        </td>
                    </tr>
					<tr>
                        <td>Total Pembayaran</td>
                        <td>: <input name="PAJAK[CPM_TOTAL_PAJAK]" type="text" class="number SUM" id="CPM_TOTAL_PAJAK" value="<?php echo  $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Total Pembayaran"></td>
                    </tr>
                    <tr>
                        <td>Terbilang</td>
                        <td> : <span id="terbilang"><?php echo  ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah" ?></span>
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
            </td>
        </tr>
    </table>
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