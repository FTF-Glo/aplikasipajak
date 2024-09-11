    <?php
    $DIR = "PATDA-V1";
    $modul = "restoran";
    require_once("inc/payment/json.php");
    require_once("function/{$DIR}/class-pajak.php");
    require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
    require_once("function/{$DIR}/{$modul}/op/class-op.php");
    require_once("function/{$DIR}/init_pelaporan.php");
    if (isset($get_previous) && !empty($npwpd) && !empty($nop)) {
        $DATA = $lapor->get_previous_pajak($npwpd, $nop);
    }
    $payment_info = $lapor->get_payment_flag($DATA["pajak"]["CPM_ID"]);
// var_dump($DATA['pajak']['CPM_TGL_JATUH_TEMPO']);die;

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

    <script language="javascript" src="inc/js/autoNumeric.min.js"></script>
    <script language="javascript" src="inc/js/jquery.validate.min.js"></script>
    <script language="javascript" src="inc/select2/js/select2.full.min.js?as"></script>
    <script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js?akjakdejd"></script>
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
        <input type="hidden" name="PAJAK[CPM_ID_TARIF]" value="<?php echo $DATA['tarif']['CPM_ID']; ?>">
        <input type="hidden" name="PAJAK[CPM_DEVICE_ID]" id="CPM_DEVICE_ID" value="<?php echo base64_encode($DATA['profil']['CPM_DEVICE_ID']); ?>">
        <input type="hidden" name="a" id="a" value="<?php echo $lapor->_a; ?>">

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
            <td>Tanggal Pengesahan</td>
            <td>: <input type='date' class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;font-size:small;\" name=\"PAJAK[tgl_cetak]\" value='{$defaultdate}' ></td>
        </tr>";
        }
        ?>
        <table class="main" width="900">
            <tr>
                <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK RESTORAN</b></td>
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
                            <?php
                            endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else : ?>
                <tr>
                    <td width="200">NPWPD <b class="isi">*</b></td>
                    <td>:
                        <input type="hidden" id="TBLJNSPJK" value="RESTORAN">
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
                            base64_encode('a=' . $a . '&m=mPatdaPelayananPelapor1&f=fPatdaPelayananLaporOP8&npwpd=' . $npwpd . '&npwpd=' . $npwpd . '&nop=' . $nop);
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
                <td>: <select name="PAJAK[CPM_REKENING]" id="CPM_REKENING" class="form-control" style="width:500px;height:30px;display:inline-block;font-size:small;">
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
                <td colspan="2" align="center" class="subtitle"><b>LAPOR PAJAK RESTORAN</b></td>
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
                    <table width="950" border="0" align="center" class="child">
                        <tr>
                            <th colspan="4">Data Pajak</th>
                        </tr>
                        <tr valign="top">
                            <td width="300">No Pelaporan Pajak <b class="isi">*</b></td>
                            <td width="200"> : <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input class=\"form-control\" style=\"width:150px;height:30px;display:inline-block;font-size:small;\" type=\"text\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "..." ?></td>
                            <td width="250" rowspan="4">
                                <fieldset>
                                    <legend>Metode Perhitungan</legend>
                                    <p><label><input type="radio" name="PAJAK[CPM_METODE_HITUNG]" class="CPM_METODE_HITUNG" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "onclick='javascript:return false'"; ?> value="Non DPP" <?php echo $DATA['pajak']['CPM_METODE_HITUNG'] == 'Non DPP' ? 'checked' : '' ?> /> Non DPP</label></p>
                                    <p><label><input type="radio" name="PAJAK[CPM_METODE_HITUNG]" class="CPM_METODE_HITUNG" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "onclick='javascript:return false'"; ?> value="DPP" <?php echo $DATA['pajak']['CPM_METODE_HITUNG'] == 'DPP' ? 'checked' : '' ?> /> DPP</label></p>
                                </fieldset>
                            </td>
                            <td width="400" rowspan="4">Keterangan :</br>
                                <textarea name="PAJAK[CPM_KETERANGAN]" class="form-control" style="width:300px;" id="CPM_KETERANGAN" style="display:block" rows="5" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Keterangan"><?php echo $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                        <td>Tipe Pajak <b class="isi">*</b></td>
                        <td> :
                            <select class="form-control" style="width:150px;height:30px;display:inline-block;" name="PAJAK[CPM_TIPE_PAJAK]" id="CPM_TIPE_PAJAK">
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
                            <td>Tahun Pajak <b class="isi">*</b></td>
                            <td> :
                                <select class="form-control" style="width:150px;height:30px;display:inline-block;font-size:small;" name="PAJAK[CPM_TAHUN_PAJAK]" id="CPM_TAHUN_PAJAK">
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
                            <td>Bulan Pajak</td>
                            <td> :
                                <select class="form-control" style="width:150px;height:30px;display:inline-block;font-size:small;" id="CPM_MASA_PAJAK">
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
                            </td>
                        </tr>
                       
                        <tr>
                            <td>Masa Pajak <b class="isi">*</b></td>
                            <td colspan="2"> : <input type="text" name="PAJAK[CPM_MASA_PAJAK1]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK1\"" : ""; ?> style="width: 120px;" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK1']; ?>" placeholder="Masa Awal"> s.d
                                <input type="text" name="PAJAK[CPM_MASA_PAJAK2]" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id=\"CPM_MASA_PAJAK2\"" : ""; ?> style="width: 120px;" readonly value="<?php echo $DATA['pajak']['CPM_MASA_PAJAK2']; ?>" placeholder="Masa Akhir">
                            </td>

                            <?php
                                $timestamp = strtotime($DATA['pajak']['CPM_TGL_JATUH_TEMPO']);
                                $formattedDate = date('Y-m-d', $timestamp);
                                $formattedDate = $formattedDate == '1970-01-01' ? 'YYYY-MM-DD' : $formattedDate;
                            ?>

                            <td width="400" rowspan="4">Tanggal Jatuh Tempo <span style="color:red; font-size:10px">(Tidak wajib)</span></br>                            
                                <input type="date" name="PAJAK[CPM_TGL_JATUH_TEMPO]" id="CPM_TGL_JATUH_TEMPO" value="<?= $formattedDate ?>" placeholder="Masa Akhir" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> style="width: 170;">
                            </td>
                            
                        </tr>

                        
                        <tr>
                            <td>Pembayaran makanan / minuman<b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK[CPM_TOTAL_OMZET]" id="CPM_TOTAL_OMZET" class="number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Pemakaian">
                                <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "<font id=\"tapbox\"><label id=\"val_tapbox\"></label></font>" : ""; ?>
                            </td>
                        </tr>
                        <tr style="display:none">
                            <td>Pembayaran Lain-lain</td>
                            <td> : <input type="text" name="PAJAK[CPM_BAYAR_LAINNYA]" id="CPM_BAYAR_LAINNYA" class="number SUM" maxlength="17" value="<?php echo $DATA['pajak']['CPM_BAYAR_LAINNYA'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Pembayaran Lain-lain"></td>
                        </tr>
                        <tr>
                            <td>Dasar Pengenaan Pajak (DPP)</td>
                            <td> : <input type="text" name="PAJAK[CPM_DPP]" id="CPM_DPP" class="number" readonly maxlength="17" value="<?php echo $DATA['pajak']['CPM_DPP'] ?>" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Dasar Pengenaan Pajak (DPP)"></td>
                        </tr>
                        <tr>
                            <td>Tarif Pajak <b class="isi">*</b></td>
                            <td> : <input type="text" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="<?php echo $DATA['tarif'] ?>" placeholder="Tarif Pajak"> %
                            </td>

                            <?php
                            $kegiatan = $DATA['pajak']['PELAKSANA_KEGIATAN'];
                            // var_dump($kegiatan);
                            // die;
                            if ($DATA['profil']['CPM_REKENING'] == "4.1.01.07.07") {
                            ?>
                                <td width="300" colspan="2">Pelaksana kegiatan : <?php echo "<span id='cek'><b> " . $kegiatan . " </span> <!--<button id='ganti'>Ganti</button>-->"; ?>

                                    <!-- <textarea style="display:none" name="PAJAK[PELAKSANA_KEGIATAN]" id="PELAKSANA_KEGIATAN" cols="50" rows="2" placeholder="Pelaksana Kegiatan"><?php echo $DATA['pajak']['PELAKSANA_KEGIATAN'] ?></textarea> -->

                                    <?php if (empty($kegiatan)) : ?>
                                        <select id="PELAKSANA_KEGIATAN1" name="PELAKSANA_KEGIATAN1" class="form-control d-none" style=" width: 250px;">
                                        </select>
                                        <textarea style="display:none" name="PAJAK[PELAKSANA_KEGIATAN]" id="PELAKSANA_KEGIATAN" cols="50" rows="2" placeholder="Pelaksana Kegiatan"><?php echo $DATA['pajak']['PELAKSANA_KEGIATAN'] ?></textarea>

                                        <input type="hidden" name="kode_kegiatan" id="kode_kegiatan" value="<?php echo $kegiatan; ?>">
                                </td>
                        <?php endif;
                                } ?>
                </td>

            </tr>
            <tr>
                <td>Pembayaran Terutang (Tarif x DPP)</td>
                <td> : <input type="text" name="PAJAK[CPM_BAYAR_TERUTANG]" id="CPM_BAYAR_TERUTANG" readonly class="number" maxlength="17" value="<?php echo $DATA['pajak']['CPM_BAYAR_TERUTANG'] ?>" readonly placeholder="Pembayaran Terutang"></td>
                <td>
                    <label>
                        <input type="checkbox" id="HITUNG_DARI_KETETAPAN">
                        Berdasarkan ketetapan
                    </label>
                </td>
            </tr>

            <tr>
                <td>Sanksi Telat Lapor <?= ($persen_terlambat_lap == 0 ? '' : "({$persen_terlambat_lap}%)") ?> x Bulan keterlambatan</td>
                <td> :<input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" <?php echo ($lapor->_s == 1 || $lapor->_s == ""  || $lapor->_s == 4 ) ?  "" : "readonly" ?> class="SUM2 number" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor"></td>
                <!-- <td> : <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" class="number SUM" maxlength="17" placeholder="Sanksi Telat Lapor" <?php echo ($lapor->_s == 1 || $lapor->_s == ""  || $lapor->_s == 4)  ? "" : "readonly"; ?> value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>"></td> -->
            </tr>

            <tr>
                <td>Jumlah Pajak yang dibayar <b class="isi">*</b></td>
                <td> : <input type="text" name="PAJAK[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="number" value="<?php echo $DATA['pajak']['CPM_TOTAL_PAJAK'] ?>" placeholder="Jumlah Pajak yang dibayar"></td>
            </tr>
            <tr>
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
            $params = array(
                'payment_code' => $payment_info['payment_code'], // pastikan $payment_info sudah terdefinisi dan berisi data yang sesuai
                'function' => 'printKwitansi',
                'app' => 'aPatda'
            );
            $json_string = json_encode($params);
            $base64_encoded = base64_encode($json_string);
            $urlstts = "function/PATDA-V1/payment_backdate/svc-payment.php?param=" . $base64_encoded;
            if ($payment_info['payment_flag'] == 1) {
                $cetak ='<a class="btn btn-sm btn-secondary" href ="'. $urlstts .'" target="_blank">Cetak</a>';
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
        echo '<tr><td><b style="font-size:14px">- STTS' . '</b></td><td><b style="font-size:14px"> : '.$cetak.'</b></td></tr>';
        echo '</table></div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"  data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
            </td>
        </tr>';
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>VERIFIKASI</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>";    
                                if ( $payment_info['payment_flag'] == 1) {
                                echo "<td width=\"100\"><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>";
                                }
                                echo " <td><label><input type=\"radio\" name=\"PAJAK[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label>
                                <button type=\"button\" style=\"float:right;\"  data-toggle=\"modal\" data-target=\"#exampleModalCenter\" class=\"btn btn-secondary\">
									  Berkas
									</button>
                                    </td>
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
        $params = array(
            'payment_code' => $payment_info['payment_code'], // pastikan $payment_info sudah terdefinisi dan berisi data yang sesuai
            'function' => 'printKwitansi',
            'app' => 'aPatda'
        );
        $json_string = json_encode($params);
        $base64_encoded = base64_encode($json_string);
        $urlstts = "function/PATDA-V1/payment_backdate/svc-payment.php?param=" . $base64_encoded;
        if ($payment_info['payment_flag'] == 1) {
            $cetak ='<a class="btn btn-sm btn-secondary button" style="padding-top:9px" href ="'. $urlstts .'" target="_blank">STTS</a>';
        }
        ?>
        <tr class="button-area">
            <td align="center" colspan="2">
                <?php
                if (in_array($lapor->_mod, array("pel", ""))) {
                    if (in_array($lapor->_s, array(1, ""))) {
                        echo "<input type=\"reset\" id=\"reset\" value=\"Reset\">\n ";
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
            </td>
        </tr>
        </table>
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
    <script>
        // $(document).ready(function() {
        //     $('#PELAKSANA_KEGIATAN1').select2({
        //         // theme: 'bootstrap4',
        //         allowClear: true,
        //         placeholder: 'cari Pelaksana Kegiatan',
        //         ajax: {

        //             dataType: 'json',
        //             url: 'function/PATDA-V1/restoran/lapor/svc-lapor.php',
        //             delay: 800,
        //             data: function(params) {
        //                 return {
        //                     search: params.term
        //                 }
        //             },
        //             processResults: function(data) {
        //                 return {
        //                     results: data
        //                 };
        //             },
        //         }
        //     }).on('select2:select', function(e) {

        //         $('#PELAKSANA_KEGIATAN1').hide();
        //         $("#PELAKSANA_KEGIATAN1").select2("destroy");
        //         $('#PELAKSANA_KEGIATAN').show().attr('readonly', true).val(e.params.data.text);
        //     });
        // });
    </script>
    <script>
        $(document).ready(function() {


            $('#reset').click(function() {
                $('#PELAKSANA_KEGIATAN1').val('').trigger('change');
                // .val('').trigger('change');
                $('#PELAKSANA_KEGIATAN').css('display', 'none').val('');
                //  $('#cek').css('display', 'none').val('');
                $('#PELAKSANA_KEGIATAN1').select2({
                    // theme: 'bootstrap4',
                    minimumInputLength: 3,
                    placeholder: "Masukan Pelaksana Kegiatan",
                    ajax: {
                        dataType: 'json',
                        url: 'function/PATDA-V1/restoran/lapor/svc-lapor.php',
                        delay: 800,
                        data: function(params) {
                            return {
                                search: params.term
                            }
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                    }
                }).on('select2:select', function(e) {
                    $('#PELAKSANA_KEGIATAN1').hide();
                    $("#PELAKSANA_KEGIATAN1").select2("destroy").val('');
                    $('#PELAKSANA_KEGIATAN').show().attr('readonly', true).val(e.params.data.text);
                });
            });
            $('#PELAKSANA_KEGIATAN1').select2({
                // theme: 'bootstrap4',
                allowClear: false,
                minimumInputLength: 3,
                placeholder: "Masukan  Kegiatan",
                ajax: {
                    dataType: 'json',
                    url: 'function/PATDA-V1/restoran/lapor/svc-lapor.php',
                    delay: 800,
                    data: function(params) {

                        return {
                            search: params.term
                        }
                    },
                    processResults: function(data) {
                        // console.log(data[0]);
                        return {
                            results: data
                        };
                    },
                }

            }).on('select2:select', function(e) {
                $('#PELAKSANA_KEGIATAN1').hide();
                $('#cek').hide();
                $("#PELAKSANA_KEGIATAN1").select2("destroy").val('');
                $('#PELAKSANA_KEGIATAN').show().attr('readonly', true).val(e.params.data.text);

            });
        });
    </script>