<?php
$DIR = "PATDA-V1";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/penetapan/class-penetapan.php");

$penetapan = new PenetapanPajak();
$penetapan->read_dokumen();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$DATA = $penetapan->get_data($penetapan->_type);

$SKPDKB = $penetapan->get_pajak($DATA, $penetapan->_tambahan);
$SKPDKB['CPM_AUTHOR'] = $SKPDKB['CPM_AUTHOR'] == "" ? $data->uname : $SKPDKB['CPM_AUTHOR'];

$edit = ($SKPDKB['ACTION'] == 1) ? true : false;
$readonly = ($edit) ? "readonly" : "";

if(!isset($_REQUEST['flg'])){
    $penetapan->_type = $penetapan->arr_pajak_gw_no[$penetapan->_type];
}

?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/penetapan/func-penetapan.js"; ?>"></script>

<form class="cmxform" id="form-lapor" method="post" action="function/<?php echo "{$DIR}" ?>/penetapan/svc-penetapan.php?param=<?php echo base64_encode($json->encode(array("a" => $penetapan->_a, "m" => $penetapan->_m, "mod" => $penetapan->_mod, "f" => $penetapan->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="type" value="<?php echo $penetapan->_type; ?>">
    <input type="hidden" name="SKPDKB[CPM_ID]" value="<?php echo $SKPDKB['CPM_ID']; ?>">
    <input type="hidden" name="SKPDKB[CPM_ID_PROFIL]" value="<?php echo $SKPDKB['CPM_ID_PROFIL']; ?>">
    <input type="hidden" name="SKPDKB[CPM_VERSION]" value="<?php echo $SKPDKB['CPM_VERSION']; ?>">
    <input type="hidden" name="SKPDKB[CPM_NO_SPTPD]" value="<?php echo $SKPDKB['CPM_NO_SPTPD']; ?>">
    <input type="hidden" name="SKPDKB[CPM_NO]" value="<?php echo $SKPDKB['CPM_NO_SPTPD']; ?>"><!--UNTUK SAVE BERKAS -->
    <input type="hidden" name="SKPDKB[CPM_AUTHOR]" value="<?php echo $SKPDKB['CPM_AUTHOR']; ?>">
    <input type="hidden" name="SKPDKB[CPM_NAMA_OP]" value="<?php echo $SKPDKB['CPM_NAMA_OP']; ?>">
    <input type="hidden" name="SKPDKB[CPM_ALAMAT_OP]" value="<?php echo $SKPDKB['CPM_ALAMAT_OP']; ?>">
    <?php
    if (isset($_REQUEST['flg'])) {
        echo "<div class=\"message-box\">";
        echo "<div style=\"margin-button:0px;\"><b>Versi Dokumen :</b> {$SKPDKB['CPM_VERSION']}</div>";
        echo "<div style=\"margin-button:0px;\"><b>Status :</b> {$penetapan->arr_status[$penetapan->_s]}</div>";
        echo ($penetapan->_s == 4) ? "<div style=\"margin:auto\"><b>Ditolak karena :</b> {$penetapan->_info}</div>" : "";
        echo ($penetapan->_s == 4 && $penetapan->_flg == 1) ? "<div style=\"margin:auto\"><b>Keterangan : </b>Sudah dibuatkan versi " . ($SKPDKB['CPM_VERSION'] + 1) . " nya</div>" : "";
        echo "</div>";
    }
    ?>
    <table class="main" width="900">              
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK</b></td>
        </tr>        
        <tr>
            <td width="200">NPWPD <b class="isi">*</b></td>
            <td>: <input type="text" name="SKPDKB[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($SKPDKB['CPM_NPWPD']) ?>" readonly></td>
        </tr>
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: <input type="text" name="SKPDKB[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $SKPDKB['CPM_NAMA_WP'] ?>" readonly></td>
        </tr>
        <tr valign="top">
            <td>Alamat Wajib Pajak <b class="isi">*</b></td>
            <td>: <textarea name="SKPDKB[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="80" rows="3" readonly><?php echo $SKPDKB['CPM_ALAMAT_WP'] ?></textarea></td>
        </tr>     
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>KURANG BAYAR</b></td>
        </tr>         
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child" cellpadding="0" cellspacing="0">   
                    <tr>
                        <th colspan="2">Data Pajak</th>
                    </tr>
                    <tr>
                        <td width="200">Jenis Kurang Bayar <b class="isi">*</b></td>
                        <td> : 
                            <?php
                            if ($penetapan->_i == 1) {
                                echo "<select name=\"SKPDKB[CPM_TAMBAHAN]\">";
                                foreach ($penetapan->arr_kurangbayar as $a => $b) {
                                    echo ($SKPDKB['CPM_TAMBAHAN'] == $a) ? "<option value='{$a}' selected>{$b}</option>" : "<option value='{$a}'>{$b}</option>";
                                }
                                echo "</select>";
                            } else {
                                echo "<input type=\"hidden\" name=\"SKPDKB[CPM_TAMBAHAN]\" id=\"CPM_TAMBAHAN\" value=\"{$SKPDKB['CPM_TAMBAHAN']}\">";
                                echo "<input type=\"text\" style=\"width: 90px;\" value=\"{$penetapan->arr_kurangbayar[$SKPDKB['CPM_TAMBAHAN']]}\" readonly>";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="150">Jenis Pajak <b class="isi">*</b></td>
                        <td width="700">: <input type="hidden" name="SKPDKB[CPM_JENIS_PAJAK]" id="CPM_JENIS_PAJAK" value="<?php echo $SKPDKB['CPM_JENIS_PAJAK'] ?>">
                            <input type="text" style="width: 250px;" value="<?php echo $penetapan->arr_pajak[$SKPDKB['CPM_JENIS_PAJAK']] ?>" readonly>                             
                        </td>
                    </tr>
                    <tr>
                        <td>Masa Pajak <b class="isi">*</b></td>
                        <td> : <input type="hidden" name="SKPDKB[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK" value="<?php echo $SKPDKB['CPM_MASA_PAJAK'] ?>">
                            <input type="text" style="width: 120px;" value="<?php echo isset($penetapan->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']])? $penetapan->arr_bulan[$SKPDKB['CPM_MASA_PAJAK']] : '-'?>" readonly>                            
                        </td>
                    </tr>
                    <tr>
                        <td width="200">Tahun Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="SKPDKB[CPM_TAHUN_PAJAK]" style="width: 120px;" id="CPM_TAHUN_PAJAK" value="<?php echo $SKPDKB['CPM_TAHUN_PAJAK'] ?>" readonly></td>
                    </tr>
                    <tr>
                        <td width="200">Tanggal Jatuh Tempo <b class="isi">*</b></td>
                        <td> : <input type="text" name="SKPDKB[CPM_TGL_JATUH_TEMPO]" style="width: 120px;" <?php echo $edit? "" : "id=\"CPM_TGL_JATUH_TEMPO\""?> value="<?php echo $SKPDKB['CPM_TGL_JATUH_TEMPO'] ?>" readonly></td>
                    </tr>                    
                    <tr>
                        <td width="200">No. <?php echo ($penetapan->_i == 2) ? "SKPDKB" : "SKPDKBT" ?> <b class="isi">*</b></td>
                        <td> : <?php echo ($SKPDKB['CPM_NO_SKPDKB'] != "") ? "<input type=\"text\" name=\"SKPDKB[CPM_NO_SKPDKB]\" id=\"CPM_NO_SKPDKB\" style=\"width: 250px;\" value=\"{$SKPDKB['CPM_NO_SKPDKB']}\" readonly>" : "..." ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%" align="center" cellpadding="0" cellspacing="0" class="child">                            
                                <tr>
                                    <td align="left" colspan="2">
                                        <table cellpadding="0" width="850" cellspacing="0" >
                                            <tr>
                                                <th width="150" >Pemeriksaan Pajak (Rp)</th>
                                                <th width="150" >Sanksi Denda</th>
                                                <th width="150" >Penyetoran (Rp)</th>
                                                <th width="150" >Kekurangan Setor (Rp)</th>
                                            </tr>
                                            <tr>
                                                <td><input type="text" name="SKPDKB[CPM_PEMERIKSAAN_PAJAK]" id="CPM_PEMERIKSAAN_PAJAK" class="number SUM" style="text-align:right" size="20" value="<?php echo $SKPDKB['CPM_PEMERIKSAAN_PAJAK'] ?>" <?php echo ($penetapan->_s == 1 || $penetapan->_s == "" || ($penetapan->_s == "4" && $penetapan->_mod == "pel")) ? "" : "readonly"; ?>></td>
                                                <td><input type="text" name="SKPDKB[CPM_DENDA]" id="CPM_DENDA" class="number SUM" style="text-align:right" size="20" value="<?php echo $SKPDKB['CPM_DENDA'] ?>" <?php echo ($penetapan->_s == 1 || $penetapan->_s == "" || ($penetapan->_s == "4" && $penetapan->_mod == "pel")) ? "" : "readonly"; ?>></td>
                                                <td><input type="text" name="SKPDKB[CPM_TOTAL_PAJAK]" id="CPM_TOTAL_PAJAK" readonly class="number" value="<?php echo $SKPDKB['CPM_TOTAL_PAJAK'] ?>" <?php echo ($penetapan->_s == 1 || $penetapan->_s == "" || ($penetapan->_s == "4" && $penetapan->_mod == "pel")) ? "" : "readonly"; ?>></td>
                                                <td><input type="text" name="SKPDKB[CPM_KURANG_BAYAR]" id="CPM_KURANG_BAYAR" style="text-align:right" maxlength="15" size="20" value="<?php echo $SKPDKB['CPM_KURANG_BAYAR'] ?>" readonly></td>
                                            </tr>                                            
                                            <tr>
                                                <td colspan="5">
                                                    Dengan Huruf : <input type="text" name="SKPDKB[CPM_TERBILANG]" id="CPM_TERBILANG" readonly style="width: 800px;" value="<?php echo $SKPDKB['CPM_TERBILANG'] ?>">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php
        if ($penetapan->_mod == "ver" && $penetapan->_s == 2) {
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>VERIFIKASI</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"SKPDKB[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"SKPDKB[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\"><textarea name=\"SKPDKB[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea></td>                        
                            </tr>
                        </table>
                    </td>
                </tr>";
        } else if ($penetapan->_mod == "per" && $penetapan->_s == 3) {
            echo "<tr>
                    <td colspan=\"2\" align=\"center\" class=\"subtitle\"><b>PERSETUJUAN</b></td>
                </tr>  
                <tr>
                    <td colspan=\"2\">
                        <table width=\"100%\" border=\"0\" align=\"center\" class=\"child\">                    
                            <tr>
                                <td width=\"100\"><label><input type=\"radio\" name=\"SKPDKB[AUTHORITY]\" class=\"AUTHORITY\" value=\"1\" checked> Setujui</label></td>
                                <td><label><input type=\"radio\" name=\"SKPDKB[AUTHORITY]\" class=\"AUTHORITY\" value=\"0\"> Tolak</label></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\"><textarea name=\"SKPDKB[CPM_TRAN_INFO]\" id=\"CPM_TRAN_INFO\" cols=\"80\" rows=\"3\" readonly placeholder=\"Alasan penolakan\"></textarea></td>                        
                            </tr>
                        </table>
                    </td>
                </tr>";
        }
        ?>
        <tr class="button-area">
            <td align="center" colspan="2">                
                <?php
                if (in_array($penetapan->_mod, array("pel", ""))) {
                    if (in_array($penetapan->_s, array(1, ""))) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        if ($edit) {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"update\" value=\"Perbaharui data\">\n
                                          <input type=\"button\" class=\"btn-submit\" action=\"delete\" value=\"Hapus\">";
                        } else {
                            echo "<input type=\"button\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                        }
                    } elseif ($penetapan->_s == 4 && $penetapan->_flg == 0) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"new_version\" value=\"Simpan versi Baru\">";
                    } elseif (in_array($penetapan->_s, array(2, 3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpdkb\" value=\"Cetak\">";
                    }
                } elseif ($penetapan->_mod == "ver") {
                    if ($penetapan->_s == 2)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"verifikasi\" value=\"Proses Dokumen\">";
                } elseif ($penetapan->_mod == "per") {
                    if ($penetapan->_s == 3)
                        echo "<input type=\"button\" class=\"btn-submit\" action=\"persetujuan\" value=\"Proses Dokumen\">";
                } elseif ($penetapan->_mod == "ply") {
                    if ($penetapan->_s == 5)
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sspd\" value=\"Cetak SSPD\">";
                }
                ?>
            </td>
        </tr>
    </table>
</form>
