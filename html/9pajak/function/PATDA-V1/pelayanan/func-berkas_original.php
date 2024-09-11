<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$DIR = "PATDA-V1";
$modul = "pelayanan";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-berkas.php");
$berkas = new BerkasPajak();
$berkas->read_dokumen();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$DATA = $berkas->get_berkas();
$DATA['CPM_AUTHOR'] = ($DATA['CPM_AUTHOR'] == "") ? $data->uname : $DATA['CPM_AUTHOR'];
$radio_lampiran[1] = strpos($DATA['CPM_LAMPIRAN'], "1") === false ? "" : "checked";
$radio_lampiran[2] = strpos($DATA['CPM_LAMPIRAN'], "2") === false ? "" : "checked";
$radio_lampiran[3] = strpos($DATA['CPM_LAMPIRAN'], "3") === false ? "" : "checked";
$radio_lampiran[4] = strpos($DATA['CPM_LAMPIRAN'], "4") === false ? "" : "checked";
$radio_lampiran[5] = strpos($DATA['CPM_LAMPIRAN'], "5") === false ? "" : "checked";
$radio_lampiran[6] = strpos($DATA['CPM_LAMPIRAN'], "6") === false ? "" : "checked";
$radio_lampiran[7] = strpos($DATA['CPM_LAMPIRAN'], "7") === false ? "" : "checked";
$radio_lampiran[8] = strpos($DATA['CPM_LAMPIRAN'], "8") === false ? "" : "checked";

//tambahan
global $DBLink;
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
            $berkas = "<a href ='function/PATDA-V1/pelayanan/upload/{$row['CPM_FILE_NAME']}' target='_blank'>Download/view</a>";
        }
    } else {
        $berkas = "";
    }
    return $berkas;
}
?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/func-berkas.js"></script>

<form class="cmxform" id="form-berkas" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-berkas.php?param=<?php echo base64_encode($json->encode(array("a" => $berkas->_a, "m" => $berkas->_m))) ?>" enctype="multipart/form-data">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="BERKAS[CPM_ID]" value="<?php echo $DATA['CPM_ID']; ?>">
    <input type="hidden" name="BERKAS[CPM_AUTHOR]" value="<?php echo $DATA['CPM_AUTHOR']; ?>">
    <input type="hidden" name="BERKAS[CPM_PETUGAS]" value="<?php echo $DATA['CPM_AUTHOR']; ?>">
    <?php
    if ($berkas->_id != "") {
        echo "<div class=\"message-box\">";
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> " . ($berkas->_i == 0 ? "Berkas Masuk" : "Berkas diterima") . "</div>";
        echo "</div>";
    }
    ?>
    <table class="main" width="900">
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PENERIMAAN BERKAS PELAYANAN</b></td>
        </tr>
        <tr>
            <td width="200">Tanggal masuk Surat <b class="isi">*</b></td>
            <td>: <input type="text" name="BERKAS[CPM_TGL_INPUT]" id="CPM_TGL_INPUT" style="width: 150px;" value="<?php echo $DATA['CPM_TGL_INPUT'] ?>" readonly></td>
        </tr>
        <tr>
            <td>Jenis Pajak <b class="isi">*</b></td>
            <td>:
                <select name="BERKAS[CPM_JENIS_PAJAK]" id="CPM_JENIS_PAJAK">
                    <?php
                    if ($DATA['CPM_JENIS_PAJAK'] != "") {
                        echo "<option value=\"{$DATA['CPM_JENIS_PAJAK']}\" selected>{$berkas->arr_pajak[$DATA['CPM_JENIS_PAJAK']]}</option>";
                    } else {
                        foreach ($berkas->arr_pajak as $pjk_id => $pjk_name) {
                            echo "<option value=\"{$pjk_id}\">{$pjk_name}</option>";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>No. SPTPD <b class="isi">*</b></td>
            <td>:
                <input type="text" name="BERKAS[CPM_NO_SPTPD]" id="CPM_NO_SPTPD" style="width: 200px;" <?php echo ($berkas->_id != "") ? "readonly" : "" ?> value="<?php echo $DATA['CPM_NO_SPTPD'] ?>">
            </td>
        </tr>
        <tr>
            <td>NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="BERKAS[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($DATA['CPM_NPWPD']) ?>" readonly></td>
        </tr>
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="BERKAS[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $DATA['CPM_NAMA_WP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="BERKAS[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="70" rows="3" readonly><?php echo $DATA['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>
        <tr>
            <td>Nama Objek Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="BERKAS[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $DATA['CPM_NAMA_OP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Objek Pajak <b class="isi">*</b></td>
            <td>: <textarea name="BERKAS[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="70" rows="3" readonly><?php echo $DATA['CPM_ALAMAT_OP'] ?></textarea></td>
        </tr>

        <tr>
            <td colspan="2">
                <p><b>Lampiran : </b></p></br>
                <span style="color:red" width="39%">File Maximal 2 Mb</span>

                <table width="100%" border="0" align="center" class="header">
                    <tr>
                        <td width="150"><label style='cursor:pointer'><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="1" <?php echo $radio_lampiran[1] ?>> SPTPD</label></td>

                        <td>
                            <input type="text" name="sptpd1" value="<?= $DATA['CPM_NO_SPTPD']; ?>" hidden>
                            <input type="file" name="berkas1" style="border:0px" />
                            <input type="text" name="name1" value="1" hidden>
                            <input type="submit" name="upload1" value="upload" formaction="function/PATDA-V1/pelayanan/upload.php" />
                            <?= getImage(1, $DATA['CPM_NO_SPTPD']); ?>
                            </br>
                        </td>

                    </tr>
                    <?php if ($DATA['CPM_JENIS_PAJAK'] == 1) : ?>
                        <tr>
                            <td width="150"><label style='cursor:pointer'><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="5" <?php echo $radio_lampiran[5] ?>> Rekapitulasi Pemanfaatan Air</label></td>

                            <td>
                                <input type="text" name="sptpd5" value="<?= $DATA['CPM_NO_SPTPD']; ?>" hidden>
                                <input type="file" name="berkas5" style="border:0px" />
                                <input type="text" name="name5" value="5" hidden>
                                <input type="submit" name="upload5" value="upload" formaction="function/PATDA-V1/pelayanan/upload.php" />
                                <?= getImage(5, $DATA['CPM_NO_SPTPD']); ?>
                                </br>
                            </td>

                        </tr>
                        <tr>
                            <td width="150"><label style='cursor:pointer'><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="6" <?php echo $radio_lampiran[6] ?>> Fotocopy SIPA, KTP, SIUP</label></td>

                            <td>
                                <input type="text" name="sptpd6" value="<?= $DATA['CPM_NO_SPTPD']; ?>" hidden>
                                <input type="file" name="berkas6" style="border:0px" />
                                <input type="text" name="name6" value="6" hidden>
                                <input type="submit" name="upload6" value="upload" formaction="function/PATDA-V1/pelayanan/upload.php" />
                                <?= getImage(6, $DATA['CPM_NO_SPTPD']); ?>
                                </br>
                            </td>

                        </tr>
                        <tr>
                            <td width="150"><label style='cursor:pointer'><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="7" <?php echo $radio_lampiran[7] ?>> Foto Water Meter</label></td>

                            <td>
                                <input type="text" name="sptpd7" value="<?= $DATA['CPM_NO_SPTPD']; ?>" hidden>
                                <input type="file" name="berkas7" style="border:0px" />
                                <input type="text" name="name7" value="7" hidden>
                                <input type="submit" name="upload7" value="upload" formaction="function/PATDA-V1/pelayanan/upload.php" />
                                <?= getImage(7, $DATA['CPM_NO_SPTPD']); ?>
                                </br>
                            </td>

                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td width="150"><label style='cursor:pointer'><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="8" <?php echo $radio_lampiran[8] ?>> NPWP/NPWPD</label></td>

                        <td>
                            <input type="text" name="sptpd8" value="<?= $DATA['CPM_NO_SPTPD']; ?>" hidden>
                            <input type="file" name="berkas8" style="border:0px" />
                            <input type="text" name="name8" value="8" hidden>
                            <input type="submit" name="upload8" value="upload" formaction="function/PATDA-V1/pelayanan/upload.php" />
                            <?= getImage(8, $DATA['CPM_NO_SPTPD']); ?>
                            </br>
                        </td>


                    </tr>

                    <?php if ($DATA['CPM_JENIS_PAJAK'] != 1 && $DATA['CPM_JENIS_PAJAK'] != 6) : ?>
                        <tr>
                            <td><label style='cursor:pointer'><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="2" <?php echo $radio_lampiran[2] ?>> Laporan Omzet Harian</label></td>

                            <td>
                                <input type="text" name="sptpd2" value="<?= $DATA['CPM_NO_SPTPD']; ?>" hidden>
                                <input type="file" name="berkas2" style="border:0px" />
                                <input type="text" name="name2" value="2" hidden>
                                <input type="submit" name="upload2" value="upload" formaction="function/PATDA-V1/pelayanan/upload.php" />
                                <?= getImage(2, $DATA['CPM_NO_SPTPD']); ?>
                                </br>
                            </td>

                        </tr>
                        <tr>
                            <td><label style='cursor:pointer'><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="3" <?php echo $radio_lampiran[3] ?>> Bon Bill</label></td>

                            <td>
                                <input type="text" name="sptpd3" value="<?= $DATA['CPM_NO_SPTPD']; ?>" hidden>
                                <input type="file" name="berkas3" style="border:0px" />
                                <input type="text" name="name3" value="3" hidden>
                                <input type="submit" name="upload3" value="upload" formaction="function/PATDA-V1/pelayanan/upload.php" />
                                <?= getImage(3, $DATA['CPM_NO_SPTPD']); ?>
                                </br>
                            </td>

                        </tr>
                    <?php endif; ?>

                    <?php if ($DATA['CPM_JENIS_PAJAK'] == 6) : ?>
                        <tr>
                            <td><label style='cursor:pointer'><input type="checkbox" name="CPM_LAMPIRAN[]" id="CPM_LAMPIRAN" value="4" <?php echo $radio_lampiran[4] ?>> Rekapitulasi Kwh Penerangan Jalan</label></td>

                            <td>
                                <input type="text" name="sptpd4" value="<?= $DATA['CPM_NO_SPTPD']; ?>" hidden>
                                <input type="file" name="berkas4" style="border:0px" />
                                <input type="text" name="name4" value="4" hidden>
                                <input type="submit" name="upload4" value="upload" formaction="function/PATDA-V1/pelayanan/upload.php" />
                                <?= getImage(4, $DATA['CPM_NO_SPTPD']); ?>
                                </br>
                            </td>

                        </tr>
                    <?php endif; ?>
                </table></br>
            </td>
        </tr>
        <tr class="button-area">
            <td align="center" colspan="2">

                <?php if ($_SESSION['role'] !== 'rmPatdaWp') : ?>
                    <input type="reset" value="Reset">
                <?php endif; ?>

                <?php
                $wpid = $_SESSION['npwpd'];
                if ($berkas->_id == "") {
                    echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\"> ";
                } else {
                    if ($_SESSION['role'] !== 'rmPatdaWp') {
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui\"> ";
                    }

                    if ($berkas->_sts == 1) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_buktiterima\" value=\"Cetak Bukti Penerimaan\"> ";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_disposisi\" value=\"Cetak Disposisi\">";
                    }
                }
                ?>
            </td>
        </tr>
    </table>
</form>