<?php
$DIR = "PATDA-V1";
$modul = "reklame";
require_once("inc/payment/json.php");
require_once("inc/payment/sayit.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");

$list_type_masa = $lapor->get_type_masa();

?>

<link href="inc/<?php echo  $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<link href="inc/<?php echo  $DIR; ?>/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/js/terbilang.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/lapor/func-lapor.js"></script>
<script type="text/javascript" src="inc/<?php echo "{$DIR}"; ?>/select2/js/select2.min.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>
	
<form class="cmxform" autocomplete="off"id="form-lapor" method="post" action="function/<?php echo  "{$DIR}/{$modul}"; ?>/lapor/svc-lapor.php?param=<?php echo  base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="param" id="param" value="<?php echo  base64_encode("a=".$lapor->_a."&m=".$lapor->_m."&f=".$lapor->_f)?>">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo  $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" id="persen_terlambat_lap" value="<?php echo $persen_terlambat_lap?>">
    <input type="hidden" id="editable_terlambat_lap" value="<?php echo $editable_terlambat_lap?>">
    <input type="hidden" name="PAJAK[CPM_VERSION]" value="<?php echo  $DATA['pajak']['CPM_VERSION']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo  $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_PROFIL]" value="<?php echo  $DATA['profil']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" value="<?php echo  $DATA['pajak']['CPM_TARIF_PAJAK']; ?>">
    <input type="hidden" name="PAJAK[CPM_MASA_PAJAK]" id="CPM_MASA_PAJAK" value="<?php echo  $DATA['pajak']['CPM_MASA_PAJAK']; ?>"/>
    <input type="hidden" name="PAJAK[CPM_MASA_PAJAK1]" value="<?php echo  $DATA['pajak']['CPM_MASA_PAJAK1']; ?>"/>
    <input type="hidden" name="PAJAK[CPM_MASA_PAJAK2]" value="<?php echo  $DATA['pajak']['CPM_MASA_PAJAK2']; ?>"/>
    <input type="hidden" name="PAJAK[CPM_JNS_MASA_PAJAK]" id="CPM_JNS_MASA_PAJAK" value="<?php echo  $DATA['pajak']['CPM_JNS_MASA_PAJAK']; ?>"/>

    <input type="hidden" name="PAJAK[CPM_TERBILANG]" id="CPM_TERBILANG" />
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TOTAL][]" id="CPM_ATR_TOTAL" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TOTAL'] ?>"/>
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_TARIF][]" id="CPM_ATR_TARIF" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TARIF'] ?>"/>
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_BIAYA][]" id="CPM_ATR_BIAYA" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BIAYA'] ?>"/>
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_JUMLAH_TAHUN][]" id="CPM_ATR_JUMLAH_TAHUN" value="
           <?php echo  isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_TAHUN']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_TAHUN'] : 0 ?>"/>
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_JUMLAH_BULAN][]" id="CPM_ATR_JUMLAH_BULAN" value="
           <?php echo  isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_BULAN']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_BULAN'] : 0 ?>"/>
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_JUMLAH_MINGGU][]" id="CPM_ATR_JUMLAH_MINGGU" value="
           <?php echo  isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_MINGGU']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_MINGGU'] : 0 ?>"/>
    <input type="hidden" name="PAJAK_ATR[CPM_ATR_JUMLAH_HARI][]" id="CPM_ATR_JUMLAH_HARI" value="
           <?php echo  isset($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI']) ? $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'] : 0 ?>"/>   
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
           ?>
    <table class="main" width="900">        
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK REKLAME</b></td>
        </tr>
        <?php if(!empty($npwpd)):?>
			<tr>
				<td width="200">NPWPD <b class="isi">*</b></td>
				<td>: 
					<input type="text" name="PAJAK[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) ?>" readonly>
					<?php if(empty($DATA['pajak']['CPM_ID'])):?>
						<?php
						if(empty($_SESSION['npwpd'])):
							$prm = 'main.php?param='.base64_encode('a='.$a.'&m='.$m.'&f='.$f);
							?>
							<input type="button" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm?>'">
						<?php endif;?>
					<?php endif;?>
				</td>
			</tr>
		<?php else:?>
			<tr>
				<td width="200">NPWPD <b class="isi">*</b></td>
				<td>: 
					<input type="hidden" id="TBLJNSPJK" value="REKLAME">
					<select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width:250px;"></select>
					<label id="loading"></label>
				</td>
			</tr>
		<?php endif;?>
		   
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
            if (!empty($npwpd) && 
                (
                    empty($DATA['profil']['CPM_KECAMATAN_WP']) || 
                    empty($DATA['profil']['CPM_KELURAHAN_WP'])
                )
            ) :
                $prm = 'main.php?param=' . 
                base64_encode('a=' . $a . '&m=mPatdaPelayananRegWP&mod=&f=fPatdaPelayananRegWp&id='.$npwpd.'&s=1&i=1');
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
			
			<?php if(empty($DATA['pajak']['CPM_ID'])):?>
				<select name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width:200px;" onchange="javascript:selectOP()">
                    <?php
                    if(count($DATA['list_nop']) == 0) echo "<option value=''>NOP Tidak tersedia</option>";
                    else echo (empty($nop))? "<option value='' selected disabled>Pilih NOP</option>" : ""; 
                    
                    foreach($DATA['list_nop'] as $list){
						echo "<option value='{$list['CPM_NOP']}' ".($nop == $list['CPM_NOP']? 'selected' : '').">{$list['CPM_NOP']}</option>";
					}
					
                    ?>
                </select>
                
                <?php
				if(count($DATA['list_nop']) == 0 && !empty($npwpd)):
					$addOp = substr($f,0,strlen($f)-1).'OP'.substr($f,-1);
					$prm = 'main.php?param='.base64_encode('a='.$a.'&m='.$m.'&f='.$addOp.'&npwpd='.$npwpd.'&nop=').'#CPM_TELEPON_WP';
					?>
					<input type="button" value="Tambah NOP" onclick="location.href='<?php echo $prm?>'">
				<?php endif;?>
                
			<?php else:?>
				<input type="text" name="PAJAK[CPM_NOP]" id="CPM_NOP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NOP'] ?>" readonly placeholder="NOP">
			<?php endif;?>
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
        <tr>
            <td colspan="2" align="center" class="subtitle"><b>LAPOR PAJAK REKLAME</b></td>
        </tr>         
        <tr>
            <td colspan="2">
                <table width="900" border="0" align="center" class="child">                    
                    <tr>
                        <th colspan="3">Data Pajak</th>
                    </tr>
                    <tr valign="top">
                        <td width="234">No Pelaporan Pajak <b class="isi">*</b></td>
                        <td width="302"> : <?php echo ($DATA['pajak']['CPM_NO'] != "") ? "<input type=\"text\" name=\"PAJAK[CPM_NO]\" id=\"CPM_NO\" maxlength=\"25\" value=\"{$DATA['pajak']['CPM_NO']}\" readonly>" : "..." ?></td>
                        <td width="300" rowspan="5">Keterangan :</br> 
                            <textarea name="PAJAK[CPM_KETERANGAN]" id="CPM_KETERANGAN" rows="4" cols="40" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>><?php echo  $DATA['pajak']['CPM_KETERANGAN'] ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td width="234">Tahun Pajak <b class="isi">*</b></td>
                        <td> : 
                            <select name="PAJAK[CPM_TAHUN_PAJAK]">
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
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AWAL][]" readonly style="width: 100px;" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AWAL'] ?>" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id='CPM_ATR_BATAS_AWAL'" : "readonly"; ?> placeholder="Batas Awal" title="Batas Awal">
                            s/d
                            <input type="text" name="PAJAK_ATR[CPM_ATR_BATAS_AKHIR][]" readonly style="width: 100px;" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BATAS_AKHIR'] ?>" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "id='CPM_ATR_BATAS_AKHIR'" : "readonly"; ?> placeholder="Batas Akhir" title="Batas Akhir"></td>
                    </tr>
                </table>
                <table width="900" class="child" border="0">
                    <tr>
                        <th colspan="2">Reklame</th>
                        <th colspan="2">Dimensi Reklame</th>
                        <th width="80">Jumlah (Qty)</th>
                        <th width="111">Jangka Waktu</th>
                    </tr>
                    <tr>
                        <td width="198" align="left" valign="top">Pilih rekening <b class="isi">*</b></td>
                        <td width="240" align="left" valign="top">
							<?php
							if($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")){
								$opt = empty($DATA['pajak_atr'][0]['CPM_ATR_REKENING'])? "<option></option>" : "<option value='{$DATA['pajak_atr'][0]['CPM_ATR_REKENING']}'>{$DATA['pajak_atr'][0]['nmrek']}</option>";
								echo "<select name=\"PAJAK_ATR[CPM_ATR_REKENING][]\" id=\"CPM_ATR_REKENING\" class=\"form-control\" style=\"width:260px\" >{$opt}</select>";
							}else{
								echo "<input type=\"text\" readonly  name=\"PAJAK_ATR[CPM_ATR_REKENING][]\" id=\"CPM_ATR_REKENING\" placeholder=\"Pilih Rekening\" size=\"30px\" value=\"{$DATA['pajak_atr'][0]['CPM_ATR_REKENING']}\" >";
							}
							?>						
							<label id="load-lokasi_1"></label>
                        </td>
                        <td width="106" align="left" valign="top">Panjang <b class="isi">*</b></td>
                        <td width="139" align="center" valign="top"><label id="load-type_1"></label>
                            <input name="PAJAK_ATR[CPM_ATR_TINGGI][]" type="text" class="number" tabindex="1" id="CPM_ATR_TINGGI" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TINGGI'] ?>" size="11" maxlength="11" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                        <td rowspan="5" align="center" valign="top"> 
                            <input name="PAJAK_ATR[CPM_ATR_JUMLAH][]" type="text" class="number" tabindex="4" id="CPM_ATR_JUMLAH" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH'] ?>" size="11" maxlength="11" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>
                            <b class="isi">*</b>
                        </td>
                        <td rowspan="5" align="center" valign="top"><span id="jangka-waktu"><?php echo  $DATA['pajak']['CPM_MASA_PAJAK'] . " " . $DATA['pajak']['CPM_JNS_MASA_PAJAK'] ?></span></td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Nama rekening</td>
                        <td align="left" valign="top"><span id="nama-rekening" style="text-align:left;color:#1B1389;font-weight:bold"><?php echo  $DATA['pajak_atr'][0]['nmrek'] ?></span><br /><span id="warning-rekening"></span></td>
                        <td align="left" valign="top">Lebar <b class="isi">*</b></td>
                        <td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_LEBAR][]" tabindex="2" type="text" class="number" id="CPM_ATR_LEBAR" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_LEBAR'] ?>" size="11" maxlength="11" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>/></td>
                    </tr>
                    <tr>
                        <td>Jenis Waktu Pemakaian</td>
                        <td>
							<select id="CPM_ATR_TYPE_MASA" name="PAJAK_ATR[CPM_ATR_TYPE_MASA][]">
								<?php
								$type_masa = $DATA['pajak_atr'][0]['CPM_ATR_TYPE_MASA'];
                                if (in_array($lapor->_mod, array("pel", ""))) {
                                    if (!in_array($lapor->_i, array(1, 3, ""))) {
                                        echo "<option value='{$type_masa}' selected>{$list_type_masa[$type_masa]}</option>";
                                    } else {
                                        foreach($list_type_masa as $key=>$val){
											
											echo "<option value='{$key}' ".($type_masa == $key? 'selected':'').">$val</option>";
										}
                                    }
                                } else {
                                    echo "<option value='{$type_masa}' selected>{$list_type_masa[$type_masa]}</option>";
                                }
                                
								?>
							</select>
                        </td>                        
                        <td align="left" valign="top">Muka <b class="isi">*</b></td>
                        <td align="center" valign="top"><input name="PAJAK_ATR[CPM_ATR_MUKA][]" tabindex="3" type="text" class="number" id="CPM_ATR_MUKA"  size="11" maxlength="11" value="<?php echo  $DATA['pajak_atr'][0]['CPM_ATR_MUKA'] ?>" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>/></td>
                    </tr>
                    <tr>
                        <td>Harga dasar</td>
                        <td><span id="harga-dasar"><?php echo  number_format((float) $DATA['pajak_atr'][0]['CPM_ATR_BIAYA'], 2, ".", ",") ?></span></td>                        
                        <td align="left" valign="top">&nbsp;</td>
                        <td align="center" valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Judul reklame <b class="isi">*</b></td>
                        <td align="left" valign="top"><div align="left">
                                <textarea name="PAJAK_ATR[CPM_ATR_JUDUL][]" id="CPM_ATR_JUDUL" style="width: 260px;" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Judul Reklame"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_JUDUL'] ?></textarea>
                            </div></td>
                        <td align="left" valign="top">&nbsp;</td>
                        <td align="center" valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Lokasi <b class="isi">*</b></td>
                        <td align="left" valign="top"><div align="left">
                                <textarea name="PAJAK_ATR[CPM_ATR_LOKASI][]" id="CPM_ATR_LOKASI" style="width: 260px;" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?> placeholder="Lokasi"><?php echo  $DATA['pajak_atr'][0]['CPM_ATR_LOKASI'] ?></textarea>
                            </div></td>
                        <td colspan="4" rowspan="2" align="center" valign="top"></td>
                    </tr>
                    <tr>
                        <td colspan="6" align="center" valign="top"><label id="perhitungan_1"></label>                      </td>
                    </tr>
                </table>                 
                </br>                                            
                <table width="100%" border="0" align="center" class="child">
                    <tr>
                        <td width="250">Pembayaran Pemakaian Objek Pajak</td>
                        <td> : <input name="PAJAK[CPM_TOTAL_OMZET]" type="text" class="number" id="CPM_TOTAL_OMZET" value="<?php echo  $DATA['pajak']['CPM_TOTAL_OMZET'] ?>" readonly <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                    </tr>
                    <tr>
                        <td>Sanksi Telat Lapor <?php echo ($persen_terlambat_lap == 0? '' : "({$persen_terlambat_lap}%)")?></td>
                        <td> : <input type="text" name="PAJAK[CPM_DENDA_TERLAMBAT_LAP]" id="CPM_DENDA_TERLAMBAT_LAP" <?php echo ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? ($editable_terlambat_lap==1?"":"readonly")  : "readonly"; ?> class="number" value="<?php echo $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] ?>" placeholder="Sanksi Telat Lapor"></td>
                    </tr>
                    <tr>
                        <td>SK Pengurangan</td>
                        <td>: 
                            <input type="text" name="PAJAK[CPM_SK_DISCOUNT]" id="CPM_SK_DISCOUNT" class="" maxlength="25" value="<?php echo  $DATA['pajak']['CPM_SK_DISCOUNT'] ?>" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>                     
                    </tr>
                    <tr>
                        <td>Persentase Pengurangan</td>
                        <td>:
                            <input name="PAJAK[CPM_DISCOUNT]" type="text" class="number" id="CPM_DISCOUNT" value="<?php echo  $DATA['pajak']['CPM_DISCOUNT'] ?>" maxlength="17" <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>>                                          
                    </tr>
                    <tr>
                        <td>Total Pembayaran</td>
                        <td>: <input name="PAJAK[CPM_TOTAL_PAJAK]" type="text" class="number" id="CPM_TOTAL_PAJAK" value="<?php echo  number_format((float) $DATA['pajak']['CPM_TOTAL_PAJAK'], 2) ?>" readonly <?php echo  ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? "" : "readonly"; ?>></td>
                    </tr>
                    <tr> <td>Terbilang</td> <td> : <span id="terbilang"><?php echo  ucwords(SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])) . " Rupiah" ?></span>
                    </tr>
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
                    } elseif (in_array($lapor->_s, array(2, 3, 5))) {
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_sptpd\" value=\"Cetak SPTPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_skpd\" value=\"Cetak SKPD\">";
                        echo "<input type=\"button\" class=\"btn-print\" action=\"print_nota_hitung\" value=\"Cetak Nota Hitung\">";
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
    
    var harga_dasar= <?php echo  $DATA['pajak_atr'][0]['CPM_ATR_BIAYA'] != "" ? $DATA['pajak_atr'][0]['CPM_ATR_BIAYA'] : 0; ?>;
	var tarif_kawasan= <?php echo  $DATA['pajak_atr'][0]['CPM_ATR_TARIF'] != "" ? $DATA['pajak_atr'][0]['CPM_ATR_TARIF'] : 0; ?>;
	var persen_pajak= <?php echo  $DATA['pajak']['CPM_TARIF_PAJAK'] != "" ? $DATA['pajak']['CPM_TARIF_PAJAK'] : 0; ?>;
	var type_masa= <?php echo  $DATA['pajak_atr'][0]['type_masa'] != "" ? $DATA['pajak_atr'][0]['type_masa'] : 0; ?>;
	
	<?php
    if($DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI']!=0){
		$tahun = $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_TAHUN'];
		$bulan = $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_BULAN'];
		$minggu = $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_MINGGU'];
		$hari = $DATA['pajak_atr'][0]['CPM_ATR_JUMLAH_HARI'];
		$triwulan = round($bulan/3,2);
		$semester = round($bulan/6,2);
		
		echo "var waktu = [{$tahun},{$semester},{$triwulan},{$bulan},{$minggu},{$hari}];";
		echo "calculation();";
	}else{
		echo "var waktu = [];";
	}?>
	
</script>
