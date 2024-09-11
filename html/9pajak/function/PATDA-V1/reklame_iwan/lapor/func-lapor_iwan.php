<?php
$DIR = "PATDA-V1";
$modul = "reklame";
require_once("inc/payment/json.php");
require_once("inc/payment/sayit.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");

// $rumuss = $lapor->get_hargadasar();
// var_dump($rumus);
$list_tarif = $lapor->list_tarif();
$list_type_masa = $lapor->get_type_masa();
$list_kawasan = $lapor->get_kawasan();
$list_jalan = $lapor->nsl();
$list_sudut_pandang = $lapor->get_sudut_pandang();
$list_rekening = $lapor->get_list_rekening();
$count = count($DATA['pajak_atr']);

if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
    $DATA = $lapor->get_previous_pajak($npwpd, $nop);
}
// var_dump($DATA);die;
//list pejabat
$list_pejabat = $lapor->get_pejabat();
foreach ($list_pejabat as $list_pejabat) {
    $opt_pejabat .= "<option value=\"{$list_pejabat['CPM_KEY']}\">{$list_pejabat['CPM_NIP']} - {$list_pejabat['CPM_NAMA']}</option>";
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
<link href="inc/<?php echo $DIR; ?>/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/js/terbilang.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js?=v.0.2.2.js"></script>
<script type="text/javascript" src="inc/<?php echo "{$DIR}"; ?>/select2/js/select2.min.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>

<form class="cmxform" autocomplete="off" id="form-lapor" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>" enctype="multipart/form-data">
    <input type="hidden" name="function" id="function" value="save">
    <!-- <input type="hidden" name="CPM_TIPE_PAJAK" id="CPM_TIPE_PAJAK" value="2"> -->
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
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TOTAL'] ?>" />

    <input type="hidden" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][]" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_ID'] ?>" />
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
        echo "<div style=\"margin-button:0px\"><b>Versi Dokumen :</b> {$DATA['pajak']['CPM_VERSION']}</div>";
        echo "<div style=\"margin-button:0px\"><b>Status :</b> {$lapor->arr_status[$lapor->_s]}</div>";
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
                    <input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" style="width:240px;height:30px;display:inline-block;font-size:small" value="<?php echo Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) ?>" readonly>
                    <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                        <?php
                        if (empty($_SESSION['npwpd'])) :
                            $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f);
                        ?>
                            <input type="button" class="btn btn-sm btn-secondary" style="height:30px" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm ?>'">
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php
                    if (!empty($DATA['profil']['CPM_NPWPD']) && empty($DATA['pajak']['CPM_ID'])) {
                        $addOp = substr($f, 0, strlen($f) - 1) . 'OP' . substr($f, -1);
                        $prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';
                        echo '<input type="button" class="btn btn-sm btn-secondary" style="height:30px" value="Tambah NOP" onclick="location.href=\'' . $prm . '\'">';
                    } ?>
                </td>
            </tr>
        <?php else : ?>
            <tr>
                <td width="200">NPWPD <b class="isi">*</b></td>
                <td>:
                    <input type="hidden" id="TBLJNSPJK" value="REKLAME">
                    <select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width:400px"></select>
                    <label id="loading"></label>
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" class="form-control" style="width:400px;display:inline-block;font-size:small" value="<?php echo  $DATA['profil']['CPM_NAMA_WP'] ?>" readonly></td>
        </tr>
        <tr>
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_WP]" class="form-control" style="width:100%;display:inline-block" id="CPM_ALAMAT_WP" rows="1" readonly><?php echo  $DATA['profil']['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
        <tr>
            <td>Kecamatan Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" class="form-control" style="width:250px;height:30px;display:inline-block;font-size:small" value="<?php echo $DATA['profil']['CPM_KECAMATAN_WP'] ?>" readonly placeholder="Kecamatan Wajib Pajak">
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
                    <a href="<?php echo $prm ?>" class="btn btn-sm btn-secondary" style="height:30px" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
                <?php endif; ?>

            </td>
        </tr>
        <tr>
            <td>Kelurahan Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="PAJAK[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" class="form-control" style="width:250px;height:30px;display:inline-block;font-size:small" value="<?php echo $DATA['profil']['CPM_KELURAHAN_WP'] ?>" readonly placeholder="Kelurahan Wajib Pajak"></td>
        </tr>
        <!--
        <tr>
			<td>NOP <b class="isi">*</b></td>
			<td>:

			<?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
				<select name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width:200px" onchange="javascript:selectOP()">
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
                    <input type="text" name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width:200px" value="<?php echo $DATA['profil']['CPM_NOP'] ?>" readonly placeholder="NOP">
                    <?php endif; ?>
                    
                    <?php
                    // var_dump($DATA['list_nop']);
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
            <td>: <input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width:200px" value="<?php echo  $DATA['profil']['CPM_NAMA_OP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Objek Pajak <b class="isi">*</b></td>
            <td>: <textarea name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="70" rows="3" readonly><?php echo  $DATA['profil']['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>
        <tr>
            <td>Kecamatan Objek Pajak <b class="isi">*</b></td>
            <td>:
				<input type="text" name="PAJAK[CPM_NAMA_KECAMATAN_OP]" id="CPM_NAMA_KECAMATAN_OP" style="width:200px" value="<?php echo $DATA['profil']['CPM_NAMA_KECAMATAN_OP'] ?>" readonly placeholder="Kecamatan Objek Pajak">
				<input type="hidden" name="PAJAK[CPM_KECAMATAN_OP]" id="KECAMATAN_OP" value="<?php echo $DATA['profil']['CPM_KECAMATAN_OP'] ?>">
			</td>
        </tr>
        <tr>
            <td>Kelurahan Objek Pajak <b class="isi">*</b></td>
            <td>:
				<input type="text" name="PAJAK[CPM_NAMA_KELURAHAN_OP]" id="CPM_NAMA_KELURAHAN_OP" style="width:200px" value="<?php echo $DATA['profil']['CPM_NAMA_KELURAHAN_OP'] ?>" readonly placeholder="Kelurahan Objek Pajak">
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
                        <th colspan="3">Data Pajak </th>
                    </tr>
                    <tr valign="top">
                        <td width="234">No Pelaporan Pajak <b class="isi">*</b></td>
                        <td width="302"> : <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;font-size:small\" type=\"text\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "..." ?></td>
                        <td width="300" rowspan="5">Keterangan :</br>
                            <textarea name="PAJAK[CPM_KETERANGAN]" class="form-control" style="width:300px" id="CPM_KETERANGAN" rows="4" tabindex="13" cols="40" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>><?php echo  $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td width="234">Tahun Pajak <b class="isi">*</b></td>
                        <td> :
                            <select name="PAJAK[CPM_TAHUN_PAJAK]" tabindex="10" class="form-control" style="width:150px;height:30px;display:inline-block;font-size:small">
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
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AWAL][]" id='CPM_ATR_BATAS_AWAL' readonly style="width:100px" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "class='datepicker'" : "readonly"; ?> placeholder="Batas Awal" tabindex="11" title="Batas Awal">
                            s/d
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AKHIR][]" id='CPM_ATR_BATAS_AKHIR' readonly style="width:100px" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "class='datepicker'" : "readonly"; ?> placeholder="Batas Akhir" tabindex="12" title="Batas Akhir">
                        </td>
                    </tr>
<?php 	if($lapor->_mod === "ver"): ?>
					<tr>
                        <td>Jatuh Tempo <b class="isi">*</b></td>
                        <td> :
                            <input type="text" name="PAJAK_ATR[CPM_TGL_JATUH_TEMPO][]" id='CPM_TGL_JATUH_TEMPO' readonly style="width:100px" value="<?php echo  $DATA['pajak_atr'][0]['CPM_TGL_JATUH_TEMPO'] ?>" <?php echo ($lapor->_s == "2" && $lapor->_mod == "ver") ? "class='datepicker'" : "readonly"; ?> placeholder="Tanggal Jatuh Tempo" tabindex="11" title="Tanggal Jatuh Tempo">
                        </td>
                    </tr>
<?php	endif;
		?>
                </table>
                <table width="900" class="child" border="0">
                    <tr>
                        <th colspan="2">Reklame</th>
                        <th colspan="2">Dimensi Reklame</th>
                        <th width="90" idx="label_jumlah">Qty / Muka</th>
                        <th width="111">Jangka Waktu</th>
                    </tr>
                    <tr>
                        <td align="left" width="198" valign="top">Tipe Pajak <b class="isi">*</b></td>
                        <td align="left" width="240" valign="top">
                            <select class="form-control" style="width:260px;height:30px;display:inline-block;font-size:small" name="PAJAK[CPM_TIPE_PAJAK]" id="CPM_TIPE_PAJAK">
                                <?php
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$DATA['pajak']['CPM_TYPE_PAJAK']}' selected>{$DATA['pajak']['ARR_TIPE_PAJAK'][$DATA['pajak']['CPM_TYPE_PAJAK']]}</option>";
                                    } else {
                                        foreach ($DATA['pajak']['ARR_TIPE_PAJAK'] as $x => $y) {
                                            echo ($x == $DATA['pajak']['CPM_TYPE_PAJAK']) ? "<option value='{$x}' selected>{$y}</option>" : "<option value='{$x}'>{$y}</option>";
                                        }
                                    }
                                } else {
                                    echo "<option value='{$DATA['pajak']['CPM_TYPE_PAJAK']}' selected>{$DATA['pajak']['ARR_TIPE_PAJAK'][$DATA['pajak']['CPM_TYPE_PAJAK']]}</option>";
                                }
                                ?>
                            </select>
                            <label id="load-tarif"></label>
                        </td>
                        <td width="110" align="left" valign="top">Panjang <b class="isi">*</b></td>
                        <td width="130" align="center" valign="top">
                            <input name="PAJAK_ATR[CPM_ATR_PANJANG][0]" class="form-control number" type="text" class="number" tabindex="18" id="CPM_ATR_PANJANG" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_PANJANG'] ?>" size="11" maxlength="11" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Panjang">
                        </td>
                        <td rowspan="3" align="center" valign="top">
                            <input name="PAJAK_ATR[CPM_ATR_JUMLAH][0]" type="hidden" class="form-control number" tabindex="21" id="CPM_ATR_JUMLAH" value="<?php echo isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'] : 1 ?>" size="11" maxlength="11" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Jumlah">
                            <input type="text" name="PAJAK_ATR[CPM_ATR_SISI][0]" class="form-control number" class="number" id="CPM_ATR_SISI" value="<?= $DATA['pajak_atr'][0]['CPM_ATR_SISI'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                            <b class="isi">*</b>
                        </td>
                        <?php
                        // echo  $DATA['pajak']['CPM_MASA_PAJAK'];
                        // exit;
                        ?>
                        <td rowspan="3" align="center" valign="top">
                            <span id="jangka-waktu"><?php echo  $DATA['pajak']['CPM_MASA_PAJAK'] . " " . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] ?></span>
                            <label id="load-type_1"></label>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" width="198" valign="top">Pilih OP <b class="isi">*</b></td>
                        <td align="left" width="240" valign="top">
                            <?php if (empty($DATA['pajak']['CPM_ID'])) : ?>
                                <select class="form-control" style="width:260px;height:30px;display:inline-block;font-size:small" name="PAJAK_ATR[CPM_ATR_NOP][0]" tabindex="14" id="CPM_NOP" class="CPM_NOP">
                                    <?php
                                    if (count($DATA['list_nop']) == 0) echo "<option value=''>NOP Tidak tersedia</option>";
                                    else echo (empty($nop)) ? "<option value='' selected disabled>Pilih NOP</option>" : "";

                                    foreach ($DATA['list_nop'] as $list) {
                                        // echo $list;
                                        $alamat = !empty($list['CPM_ALAMAT_OP']) ? $list['CPM_ALAMAT_OP'] . ', ' : '';
                                        $kel = !empty($list['CPM_KELURAHAN']) ? $list['CPM_KELURAHAN'] . ', ' : '';
                                        echo "<option value='{$list['CPM_ID']}' " . ($nop == $list['CPM_NOP'] ? 'selected' : '') . ">{$list['CPM_NOP']} - {$list['CPM_NAMA_OP']} | {$alamat}{$kel}Kec. {$list['CPM_KECAMATAN']}</option>";
                                    }

                                    ?>
                                </select>

                            <?php else : ?>
                                <input type="text" class="form-control"  value="<?php echo $DATA['pajak_atr'][0]['CPM_NOP'], ' - ', $DATA['pajak_atr'][0]['CPM_NAMA_OP'] ?>" readonly />
                                <input type="hidden" name="PAJAK_ATR[CPM_ATR_NOP][0]" id="CPM_NOP-'.$no.'" value="<?php echo $DATA['pajak_atr'][0]['CPM_ATR_ID_PROFIL'] ?>" readonly />
                            <?php endif; ?>
                        </td>
                        <td align="left" valign="top">Lebar <b class="isi">*</b></td>
                        <td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_LEBAR][0]" class="form-control number"  tabindex="19" type="text" class="number" id="CPM_ATR_LEBAR" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_LEBAR'] ?>" size="11" maxlength="11" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Lebar" /></td>

                    </tr>
                    <tr>
                        <td align="left" valign="top">Pilih rekening <b class="isi">*</b></td>
                        <td align="left" valign="top">
                            <select class="form-control" style="width:260px;height:30px;display:inline-block;font-size:small" tabindex=" 15" name="PAJAK_ATR[CPM_ATR_REKENING][0]" id="CPM_ATR_REKENING" style="width:260px">
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
                        <td align="left" valign="top">Tinggi <b class="isi">*</b></td>
                        <td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_TINGGI][0]" class="form-control number" tabindex="3" type="text" class="number" id="CPM_ATR_TINGGI" size="11" maxlength="11" value="<?= $DATA['pajak_atr'][0]['CPM_ATR_TINGGI'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Tinggi" /></td>

                    </tr>
                    <tr>
                        <td align="left" valign="top">Nama rekening</td>
                        <td align="left" valign="top"><span id="nama-rekening" style="text-align:left;color:#1B1389;font-weight:bold"><?php echo $DATA['pajak_atr'][0]['nmrek'] ?></span><br /><span id="warning-rekening"></span></td>
                        <td class="ID_JAM" align="left" valign="top">Jam <b class="isi">*</b></td>
                        <td class="ID_JAM" align="center" valign="top"><input class="form-control" style="width:150px;height:30px;display:inline-block;font-size:small" name="PAJAK_ATR[CPM_ATR_JAM][0]" tabindex="3" type="text" class="number" id="CPM_ATR_JAM" size="11" maxlength="11" value="<?= $DATA['pajak_atr'][0]['CPM_ATR_JAM'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Jam" /></td>
						
                    </tr>
                    <tr>
                        <td>Jenis Waktu Pemakaian</td>
                        <td>
                            <select class="form-control" style="width:260px;height:30px;display:inline-block;font-size:small" id="CPM_ATR_TYPE_MASA" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][0]">
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

                        <td colspan="2">
                            Alkohol/Rokok <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][0]" class="CPM_ALKOHOL_ROKOK" value="1" <?= ($lapor->_id != "" && $lapor->_s != 1) ? 'onclick:javascript:return false"' : '' ?> <?= $DATA['pajak_atr'][0]['CPM_ATR_ALKOHOL_ROKOK'] == '1' ? 'checked' : '' ?> /> Ya</label> &nbsp;
                            <label><input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][0]" class="CPM_ALKOHOL_ROKOK" value="0" <?= ($lapor->_id != "" && $lapor->_s != 1) ? 'onclick:javascript:return false"' : '' ?> <?= $DATA['pajak_atr'][0]['CPM_ATR_ALKOHOL_ROKOK'] == '0' ? 'checked' : '' ?> id="ForCheck2" /> Tidak</label>
                        </td>
						
						
                    </tr>
					<tr>
                        <td>Lokasi Reklame</td>
                        <td>
                            <select class="form-control" style="width:260px;height:30px;display:inline-block;font-size:small" id="CPM_ATR_JALAN" name="PAJAK_ATR[CPM_ATR_JALAN][]">
                                <?php
                                $jln = $DATA['pajak_atr'][0]['CPM_ATR_JALAN'];
								
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$jln}' selected>{$jln}</option>";
                                    } else {
                                        foreach ($list_jalan as $dalan => $tarip) {
											echo '<option value="' . $tarip['tarif_pajak'] . '" ' . ($jln == $tarip['tarif_pajak'] ? 'selected' : '') . '>' . $tarip['tarif_pajak'] . '</option>';
										}
                                    }
                                } else {
                                    echo "<option value='{$jln}' selected>{$jln}</option>";
                                }
                                ?>
                            </select>
                        </td>

                        <td colspan="2">
							<select class="form-control text-center" onChange="calculation()" name="RMS" id="RMS">
                            <?php
                                $rumus = $DATA['pajak_atr'][0]['CPM_RUMUS'];
								if ($rumus == 'RMS1') {
                                    $rms = 'RUMUS 1';
                                }else{
                                    $rms = 'RUMUS 2';
                                }
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$rumus}' selected>{$rms}</option>";
                                    } else {
                                        echo '<option value="">-->PILIH RUMUS<--</option>
                                        <option value="RMS1">RUMUS 1</option>
                                        <option value="RMS2">RUMUS 2</option>';
                                    }
                                } else {
                                    echo "<option value='{$rumus}' selected>{$rms}</option>";
                                }
                                ?>

							</select>
						</td>
						
					</tr>
                    <tr>
                        <td>Pembayaran Melalui Pihak Ketiga</td>
                        <td>
                            <div style="position:relative">
                                <input name="PAJAK_ATR[CPM_CEK_PIHAK_KETIGA][]" style="width:20px;inline-block" type="checkbox" id="CPM_CEK_PIHAK_KETIGA" value="1" <?= ($DATA['pajak_atr'][0]['CPM_CEK_PIHAK_KETIGA'] == 'true') ? 'checked="checked"' : ''; ?>>
                                <input class="form-control" style="width:240px;height:30px;display:inline-block;font-size:small;position:absolute;top:-8px;left:20px" name="PAJAK_ATR[CPM_NILAI_PIHAK_KETIGA][]" placeholder="Nilai Pihak Ketiga" type="text" id="CPM_NILAI_PIHAK_KETIGA" readonly="readonly" value="<?php echo $DATA['pajak_atr'][0]['CPM_NILAI_PIHAK_KETIGA'] ?>">
                            </div>
                        </td>
                        <td align="left" colspan="4" rowspan="6" valign="top">
                            <div id="area_perhitungan" ></div>
                        </td>
                    </tr>
                    <tr>
                        <td>Biaya Tarif Pajak</td>
                        <td>
                            <input name="PAJAK_ATR[CPM_ATR_BIAYA][]" style="width:260px" placeholder="Biaya Tarif Pajak" type="text" class="number" id="CPM_ATR_BIAYA" readonly value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BIAYA'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td>Harga Dasar Ukuran</td>
                        <td>
                            <input name="PAJAK_ATR[CPM_ATR_HARGA_DASAR_UK][]" style="width:260px" placeholder="Biaya Harga Dasar" type="text" class="number" id="CPM_ATR_HARGA_DASAR_UK" readonly value="<?= $DATA['pajak_atr'][0]['CPM_ATR_HARGA_DASAR_UK'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td>Harga Dasar Ketinggian</td>
                        <td>
                            <input name="PAJAK_ATR[CPM_ATR_HARGA_DASAR_TIN][]" style="width:260px" placeholder="Biaya Harga Dasar" type="text" class="number" id="CPM_ATR_HARGA_DASAR_TIN" readonly value="<?= $DATA['pajak_atr'][0]['CPM_ATR_HARGA_DASAR_TIN'] ?>" <?= ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Judul reklame <b class="isi">*</b></td>
                        <td align="left" valign="top">
                            <div align="left">
                                <textarea name="PAJAK_ATR[CPM_ATR_JUDUL][]" id="CPM_ATR_JUDUL" tabindex="22" style="width:260px" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Judul Reklame"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_JUDUL'] ?></textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Lokasi <b class="isi">*</b></td>
                        <td align="left" valign="top">
                            <div align="left">
                                <textarea name="PAJAK_ATR[CPM_ATR_LOKASI][]" id="CPM_ATR_LOKASI" tabindex="23" style="width:260px" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Lokasi"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_LOKASI'] ?></textarea>
                            </div>
                        </td>
                    </tr>
                    <?php
                                $timestamp = strtotime($DATA['pajak']['CPM_TGL_JATUH_TEMPO']);
                                $formattedDate = date('Y-m-d', $timestamp);
                                $formattedDate = $formattedDate == '1970-01-01' ? 'YYYY-MM-DD' : $formattedDate;
                            ?>
                    <tr>
                        <td align="left" valign="top">Tanngl Jatuh Tempo <b class="isi" style="font-size:9px">(Tidak Wajib)</b></td>
                        <td align="left" valign="top">
                            <div align="left">
                            <input type="date" name="PAJAK[CPM_TGL_JATUH_TEMPO]" id="CPM_TGL_JATUH_TEMPO" tabindex="24" value="<?= $formattedDate ?>" placeholder="Masa Akhir" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> style="width: 260;">
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
                            // var_dump($DATA['pajak_atr']);die;
                            if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) {
                                $hapus = '<button type="button" id="btn-hapus-' . $no . '" onclick="delRow(' . $no . ',' . $atr['CPM_ATR_ID'] . ')">Hapus</button>';
                            } else {
                                $hapus = '';
                            }
                            echo '<table width="900" class="child" id="atr_rek-' . $no . '" border="0" style="margin-top:8px">
                            <tr>
                                <th colspan="2">Reklame ' . $no . '</th>
                                <th colspan="2">Dimensi Reklame</th>
                                <th width="110">Jumlah (Qty)</th>
                                <th width="50">Jangka Waktu</th>
                            </tr>
                            <tr>
                                <td align="left" width="198" valign="top">Pilih OP <b class="isi">*</b></td>
                                <td align="left" width="240" valign="top">';
                            if (empty($DATA['pajak']['CPM_ID'])) {
                                echo '<select name="PAJAK_ATR[CPM_ATR_NOP][' . ($no - 1) . ']" tabindex="14" id="CPM_NOP-' . $no . '" style="max-width:260px"><option value="" disabled>Pilih NOP</option>';

                                foreach ($DATA['list_nop'] as $list) {
                                    $alamat = !empty($list['CPM_ALAMAT_OP']) ? $list['CPM_ALAMAT_OP'] . ', ' : '';
                                    $kel = !empty($list['CPM_KELURAHAN']) ? $list['CPM_KELURAHAN'] . ', ' : '';
                                    echo "<option value='{$list['CPM_ID']}' " . ($atr['CPM_ATR_ID_PROFIL'] == $list['CPM_ID'] ? 'selected' : '') . ">{$list['CPM_NOP']} - {$list['CPM_NAMA_OP']} | {$alamat}{$kel}Kec. {$list['CPM_KECAMATAN']}</option>";
                                }

                                echo '</select>';
                            } else {
                                echo '<input type="text" style="width:260px" value="', $atr['CPM_NOP'], ' - ', $atr['CPM_NAMA_OP'], '" readonly /><input type="hidden" name="PAJAK_ATR[CPM_ATR_NOP][' . ($no - 1) . ']" id="CPM_NOP-' . $no . '" value="', $atr['CPM_ATR_ID_PROFIL'], '" readonly />';
                            }
                            // var_dump( $atr['CPM_ATR_SISI']);exit;
                            echo '</td>
                                <td width="110" align="left" valign="top">Panjang <b class="isi">*</b></td>
                                <td width="130" align="center" valign="top"><label id="load-type-' . $no . '"></label>
                                    <input name="PAJAK_ATR[CPM_ATR_PANJANG][' . ($no - 1) . ']" type="text" class="number" tabindex="' . ($idx + 4) . '" id="CPM_ATR_PANJANG-' . $no . '" maxlength="6" value="' . $atr['CPM_ATR_PANJANG'] . '" ' . $readonly . '  style="width:110px;height:30px;display:inline-block;font-size:small" placeholder="Panjang" onkeyup="hitungDetail(' . $no . ')" />
                                </td>
                                <td rowspan="3" align="center" valign="top">
                                    <input name="PAJAK_ATR[CPM_ATR_SISI][' . ($no - 1) . ']" type="text" class="number" tabindex="' . ($idx + 7) . '" id="CPM_ATR_SISI-' . $no . '" maxlength="3" value="' . $atr['CPM_ATR_SISI'] . '" ' . $readonly . ' style="width:80px;height:30px;display:inline-block;font-size:small" placeholder="Jumlah" onkeyup="hitungDetail(' . $no . ')" />
                                    <b class="isi">*</b>
                                    </td>
                                <td rowspan="3" align="center" valign="top">
                                    <input name="PAJAK_ATR[CPM_MASA_PAJAK][' . ($no - 1) . ']" type="hidden" id="CPM_MASA_PAJAK-' . $no . '" value="' . $atr['CPM_MASA_PAJAK'] . '" />
                                    <span id="jangka-waktu">' . intval($DATA['pajak']['CPM_MASA_PAJAK']) . ' ' . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] . '</span></span>
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
                                        data-tinggi='{$rek->tarif3}'{$selected}{$disabled}>{$rek->kdrek} - {$rek->nmrek}</option>";
                            }
                            echo '</select>
                                </td>
                                <td align="left" valign="top">Lebar <b class="isi">*</b></td>
                                <td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_LEBAR][' . ($no - 1) . ']" tabindex="' . ($idx + 5) . '" type="text" class="number" id="CPM_ATR_LEBAR-' . $no . '" maxlength="6" value="' . $atr['CPM_ATR_LEBAR'] . '" ' . $readonly . ' style="width:110px;height:30px;display:inline-block;font-size:small" placeholder="Lebar" onkeyup="hitungDetail(' . $no . ')" /></td>

                            </tr>
                            <tr>
                                <td align="left" valign="top">Nama rekening</td>
                                <td align="left" valign="top"><span id="nama-rekening-' . $no . '" style="text-align:left;color:#1B1389;font-weight:bold">', $atr['nmrek'], '</span><br /><span id="warning-rekening"></span></td>
                                <td align="left" valign="top">Tinggi <b class="isi">*</b></td>
                                <td align="center">
                                    <input name="PAJAK_ATR[CPM_ATR_TINGGI][' . ($no - 1) . ']" tabindex="' . ($idx + 6) . '" type="text" id="CPM_ATR_TINGGI-' . $no . '" class="number" maxlength="6" onkeyup="hitungDetail(' . $no . ')" value="' . $atr['CPM_ATR_TINGGI'] . '" ' . $readonly . ' style="width:110px;height:30px;display:inline-block;font-size:small" placeholder="TINGGI" />
                                </td>
                            </tr>
                            <tr>
                                <td>Jenis Waktu Pemakaian </td>
                                <td>
                                    <select class="form-control" id="CPM_ATR_TYPE_MASA-' . $no . '" tabindex="' . ($idx + 2) . '" onchange="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]">';
                            $jenis_x = $atr['CPM_ATR_TYPE_MASA'];
                            $namajnswktu = array(1 => 'Tahun', 2 => 'none', 3 => 'Triwulan', 4 => 'Bulan', 5 => 'Minggu', 6 => 'Hari');
                            if (in_array($lapor->_mod, array("pel", ""))) {
                                if (!in_array($lapor->_i, array(1, 3, ""))) {
                                    echo "<option value='{$jenis_x}' selected>{$namajnswktu[$jenis_x]}</option>";
                                } else {
                                    foreach ($list_type_masa as $key => $val) {
                                        echo "<option value='{$key}' " . ($jenis_x == $key ? 'selected' : '') . ">$val</option>";
                                    }
                                }
                            } else {
                                echo "<option value='{$jenis_x}' selected>{$namajnswktu[$jenis_x]}</option>";
                            }
                            echo '</select>
                                </td>
                                <td colspan="2">
                                <label>Alkohol/Rokok </label>
                                <input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" onchange="hitungDetail(' . $no . ')" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="1" ' . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == '1' ? 'checked' : '') . ' style="margin-left:20px"> Ya &nbsp;
                                <label>
                                <input type="radio" name="PAJAK_ATR[CPM_ATR_ALKOHOL_ROKOK][' . ($no - 1) . ']" onchange="hitungDetail(' . $no . ')" class="CPM_ALKOHOL_ROKOK-' . $no . '" value="0" ' . ($atr['CPM_ATR_ALKOHOL_ROKOK'] == '0' ? 'checked' : '') . ' id="ForCheck2" /> Tidak
                                </label>
                            </td>
                            </tr>
                  
                            <tr>
                                <td>Lokasi Reklame</td>
                                <td>
                                    <select data-x="' . $atr['CPM_ATR_JALAN'] . '" class="form-control" id="CPM_ATR_JALAN-' . $no . '" tabindex="' . ($idx + 4) . '" onchange="hitungDetail(' . $no . ')" name="PAJAK_ATR[CPM_ATR_JALAN][' . ($no - 1) . ']">';
                            $jalan = $atr['CPM_ATR_JALAN'];
                            
                            if (in_array($lapor->_mod, array("pel", ""))) {
                                if (!in_array($lapor->_i, array(1, 3, ""))) {
                                    echo "<option value='{$jalan}' selected>{$jalan}</option>";
                                } else {
                                    foreach ($list_jalan as $jln) {
                                        echo "<option value='{$jln}' " . ($jalan == $jln ? 'selected' : '') . ">$jln</option>";
                                    }
                                }
                            } else {
                                echo "<option value='{$jalan}' selected>{$jalan}</option>";
                            }


                         
                            echo  '</select>
                                </td>

                                <td colspan="2">
                                <select class="form-control text-center" onChange="hitungDetail(' . $no . ')" name="RMS" id="RMS">';
                                $rumus =$atr['CPM_RUMUS'];
                                if ($rumus == 'RMS1') {
                                    $rms = 'RUMUS 1';
                                }else{
                                    $rms = 'RUMUS 2';
                                }
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$rumus}' selected>{$rms}</option>";
                                    }
                                } else {
                                    echo "<option value='{$rumus}' selected>{$rms}</option>";
                                }
                                echo  ' </select>
                            </td>
                            </tr>
                            <!--<tr>
                                <td>Pembayaran Melalui Pihak Ketiga</td>
                                <td>
                                    <input name="PAJAK_ATR[CPM_CEK_PIHAK_KETIGA][' . ($no - 1) . ']" style="width:20px" type="checkbox" id="CPM_CEK_PIHAK_KETIGA-' . $no . '" value="1" />
                                    <input name="PAJAK_ATR[CPM_NILAI_PIHAK_KETIGA][' . ($no - 1) . ']" style="width:240px" placeholder="Nilai Pihak Ketiga" type="text" id="CPM_NILAI_PIHAK_KETIGA-' . $no . '" readonly="readonly" />
                                </td>
                            </tr>-->
                            <tr>
                                <td>Biaya Tarif Pajak</td>
                                <td>
                                    <input name="PAJAK_ATR[CPM_ATR_BIAYA][' . ($no - 1) . ']" style="width:260px" placeholder="Biaya Tarif Pajak" type="text" class="number" value="' . $atr['CPM_ATR_BIAYA'] . '" id="CPM_ATR_BIAYA-' . $no . '" readonly />
                                </td>
                                <td align="left" colspan="4" rowspan="6" valign="top">
                                    <div id="area_perhitungan-' . $no . '">';
                            $html = "<div style='border-bottom:2px #999 dashed;padding:5px'>";
                            $html .= "<table width='100%'><tr><td>";
                            $html .= 'Luas Reklame  : ' . number_format($atr['CPM_ATR_PANJANG'] * $atr['CPM_ATR_LEBAR'] * $atr['CPM_ATR_TINGGI'] * $atr['CPM_ATR_SISI'], 2) . " M<sup>2</sup> <br/>";
                            $html .= 'Durasi : ' . intval($DATA['pajak']['CPM_MASA_PAJAK']) . ' ' . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] . " <br/>";
                            $html .= 'Tarif Pajak : ' . number_format($atr['CPM_ATR_TARIF'], 0) . "% <br/>";
                            $html .= 'Besaran Pajak : Rp. ' . number_format($atr['CPM_ATR_HARGA'], 0) . " / {$DATA['pajak']['CPM_JNS_MASA_PAJAK']}<br/>";
                            $html .= "</td></tr></table>";
                            $html .= "</div><div style='background:#CCC;font-size:12px!important;padding:4px'>";
                            $html .= $rumuss;
                            $html .= "</div>";

                            

                            echo $html;
                            echo '</div>
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">Judul reklame <b class="isi">*</b></td>
                                <td align="left" valign="top"><div align="left">
                                        <textarea name="PAJAK_ATR[CPM_ATR_JUDUL][' . ($no - 1) . ']" id="CPM_ATR_JUDUL-' . $no . '" tabindex="' . ($idx + 8) . '" style="width:260px" placeholder="Judul Reklame" ' . $readonly . '>' . $atr['CPM_ATR_JUDUL'] . '</textarea>
                                    </div></td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">Lokasi <b class="isi">*</b></td>
                                <td align="left" valign="top"><div align="left">
                                        <textarea name="PAJAK_ATR[CPM_ATR_LOKASI][' . ($no - 1) . ']" id="CPM_ATR_LOKASI-' . $no . '" tabindex="' . ($idx + 9) . '" style="width:260px" placeholder="Lokasi" ' . $readonly . '>' . $atr['CPM_ATR_LOKASI'] . '</textarea>
                                    </div></td>
                            </tr>
                            <tr>
                                <td colspan="6" align="right" valign="top">
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_ID][' . ($no - 1) . ']" id="CPM_ATR_ID-' . $no . '" value="' . $atr['CPM_ATR_ID'] . '" />
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TARIF][' . ($no - 1) . ']" id="CPM_ATR_TARIF-' . $no . '" value="' . $atr['CPM_ATR_TARIF'] . '" />
                                    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][' . ($no - 1) . ']" id="CPM_ATR_TOTAL-' . $no . '" value="' . $atr['CPM_ATR_TOTAL'] . '" />
                                    ' . $hapus . '
                                </td>
                            </tr>
                        </table>';
                            $no++;
                        }
                    } ?>
                </div>
                <input type="hidden" id="count" value="<?php echo $count ?>" />
                <?php if ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) { ?>
                    <div style="text-align:center;padding:10px"> <button type="button" class="btn btn-info btn-tambah" onclick="myFunction2()">Tambah</button></div>
                <?php } ?>
                </br>
                <table width="100%" border="0" align="center" class="child">
                    <tr>
                        <td width="250">Pembayaran Pemakaian Objek Pajak</td>
                        <td> : <input name="PAJAK[CPM_TOTAL_OMZET]" type="text" class="number" id="CPM_TOTAL_OMZET" value="<?php echo  $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" readonly <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Pemakaian Objek Pajak"></td>
                    </tr>

                    <tr>
                        <td>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x Bulan keterlambatan</td>
                        <td> : <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" class="number SUM" maxlength="17" placeholder="Sanksi Telat Lapor" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "readonly" : "readonly"; ?> value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>"></td>
                    </tr>

                    <tr>
                        <td>SK Pengurangan</td>
                        <td>:
                            <input type="text" name="PAJAK[CPM_SK_DISCOUNT]" id="CPM_SK_DISCOUNT" class="sk-pengurangan" maxlength="25" value="<?php echo  $DATA['pajak']['CPM_SK_DISCOUNT'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="SK Pengurangan">
                    </tr>
                    <tr>
                        <td>Persentase Pengurangan</td>
                        <td>:
                            <input name="PAJAK[CPM_DISCOUNT]" type="text" class="number" id="CPM_DISCOUNT" value="<?php echo  $DATA['pajak']['CPM_DISCOUNT'] ?>" maxlength="17" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Persentase Pengurangan">
                    </tr>
                    <tr>
                        <td>Total Pembayaran</td>
                        <td>: <input name="PAJAK[CPM_TOTAL_PAJAK]" type="text" class="number" id="CPM_TOTAL_PAJAK" value="<?php echo  $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" readonly placeholder="Total Pembayaran"></td>
                    </tr>
                    <tr>
                        <!-- <td>Terbilang</td> -->
                        <!-- <td> : <span id="terbilang"><?php echo  ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah" ?></span> -->
                        <td colspan="4">Terbilang : <b><input type="text" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" readonly class="form-control" style="width:800px;height:30px;display:inline-block;" value="<?php echo ucwords($DATA['pajak']['CPM_TERBILANG']) ?>" placeholder="Terbilang"></b></td>
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

                                <td width=\"100%\" align=\"left\" colspan=\"2\">
                                <input type=\"text\" name=\"sptpd9\" value=\"{$DATA['pajak']["CPM_NO"]}\" hidden>
                                <input type=\"file\"  name=\"berkas9\" style=\"border:0px; width:180\" />
                                <input type=\"text\" name=\"name9\" value=\"9\" hidden>
                                <input type=\"submit\"name=\"upload9\" value=\"upload\" formaction=\"function/PATDA-V1/pelayanan/upload.php\"> ";
                               echo getImage(9, $DATA['pajak']['CPM_NO']);
                            echo "</td>
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
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
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
	$(document).ready(function(){
		<?php
		switch(TRUE){
			case ($lapor->_mod == "ver" && $lapor->_s == 2) || ($lapor->_mod == "pel" ):
				echo "get_hargadasar();";
			break;
			default: 
				//echo "alert('seri : ".$lapor->_s." / Mod : ".$lapor->_mod."');";
			break;
		}
?>
		
		var nmrek = $('#CPM_ATR_REKENING').val();
		
//		if(nmrek === "4.1.01.09.01.004" || nmrek === "" || nmrek === "4.1.01.09.01.04"){
//			$('.ID_JAM').show();
//		}else{
//			$('.ID_JAM').hide();
//		}

		$("#CPM_CEK_PIHAK_KETIGA").click(function() {
			var x = document.getElementById("CPM_CEK_PIHAK_KETIGA").checked;
//	 	calculation();
			$('#CPM_NILAI_PIHAK_KETIGA').val('');
			if ($('#CPM_CEK_PIHAK_KETIGA').prop('checked')) {			
				$('#CPM_NILAI_PIHAK_KETIGA').removeAttr('readonly');
				$('#CPM_ATR_KAWASAN').prop('selectedIndex',0);
				$('#CPM_ATR_KAWASAN').attr('disabled','true');
				$('#CPM_ATR_TINGGI').attr('readonly','readonly');
				$('#CPM_ATR_LEBAR').attr('readonly','readonly');
				$('#CPM_ATR_PANJANG').attr('readonly','readonly');
				$('#CPM_ATR_JUMLAH').attr('readonly','readonly');				
			}else{
				$('#CPM_NILAI_PIHAK_KETIGA').attr('readonly','readonly');
				$('#CPM_ATR_KAWASAN').removeAttr('disabled');
				$('#CPM_ATR_TINGGI').removeAttr('readonly');
				$('#CPM_ATR_LEBAR').removeAttr('readonly');
				$('#CPM_ATR_JUMLAH').removeAttr('readonly');
				$('#CPM_ATR_PANJANG').removeAttr('readonly');
			}
		});
		$("#CPM_NILAI_PIHAK_KETIGA").change(function() {
	   		calculation();
		})
	});
    var waktu = [];
    <?php

    if (($lapor->_id != "" && $lapor->_s == 1) || (isset($get_previous) && !empty($DATA['pajak']['CPM_TOTAL_PAJAK']))) {

        $n_pajak 	= count($DATA['pajak_atr']);
        $waktu 		= $DATA['pajak_atr'][0];
        $hari 		= $waktu['CPM_ATR_JUMLAH_HARI'];
        $minggu 	= $waktu['CPM_ATR_JUMLAH_MINGGU'];
        $bulan 		= $waktu['CPM_ATR_JUMLAH_BULAN'];
        $tahun 		= $waktu['CPM_ATR_JUMLAH_TAHUN'];
        $semester 	= round($tahun / 2, 2);
        $triwulan 	= round($semester / 2, 2);

        $hitung_detail = '';
        if (($n_pajak > 1)) {
            $n_c = $n_pajak + 1;
            for ($i = 2; $i <= $n_c; $i++) {
                $hitung_detail .= "hitungDetail('" . $i . "');";
            }
        }

        echo "
		waktu = [{$tahun}, {$semester}, {$triwulan}, {$bulan}, {$minggu}, {$hari}];
		$(document).ready(function(){
			$('#CPM_ATR_REKENING').trigger( 'change' );
			calculation();
            hitungUlangAll();
			load_first();
		});";

        echo "
            function hitungUlangAll(){
                {$hitung_detail} 
            }
        ";
    }
    ?>


</script>