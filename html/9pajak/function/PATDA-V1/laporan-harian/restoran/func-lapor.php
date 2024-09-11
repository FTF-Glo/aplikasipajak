<?php
$DIR = "PATDA-V1";
$modul = "restoran";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");
require_once("function/{$DIR}/init_pelaporan.php");

$list_jenis_kamar = $lapor->get_list_jenis_kamar();
?>

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<link href="inc/<?php echo $DIR?>/datetimepicker/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />

<script src="inc/<?php echo $DIR?>/datetimepicker/php-date-formatter.min.js" type="text/javascript"></script>
<script src="inc/<?php echo $DIR?>/datetimepicker/jquery.mousewheel.js" type="text/javascript"></script>                
<script src="inc/<?php echo $DIR?>/datetimepicker/jquery.datetimepicker.js" type="text/javascript"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script language="javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/laporan-harian/<?php echo $modul?>/func-lapor.js"></script>

<form class="cmxform" autocomplete="off"id="form-lapor" method="post" action="function/<?php echo "{$DIR}/laporan-harian/{$modul}"; ?>/svc-lapor.php?param=<?php echo base64_encode($json->encode(array("a" => $lapor->_a, "m" => $lapor->_m, "mod" => $lapor->_mod, "f" => $lapor->_f))) ?>">
    <input type="hidden" name="function" id="function" value="save">
    <input type="hidden" name="param" id="param" value="<?php echo  base64_encode("a=".$lapor->_a."&m=".$lapor->_m."&f=".$lapor->_f)?>">
    <input type="hidden" id="persen_terlambat_lap" value="<?php echo $persen_terlambat_lap?>">
    <input type="hidden" id="editable_terlambat_lap" value="<?php echo $editable_terlambat_lap?>">
    <input type="hidden" name="PAJAK[CPM_ID]" value="<?php echo $DATA['pajak']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_VERSION]" value="<?php echo $DATA['pajak']['CPM_VERSION']; ?>">
    <input type="hidden" name="PAJAK[CPM_AUTHOR]" value="<?php echo $DATA['pajak']['CPM_AUTHOR']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_PROFIL]" value="<?php echo $DATA['profil']['CPM_ID']; ?>">
    <input type="hidden" name="PAJAK[CPM_ID_TARIF]" value="<?php echo $DATA['tarif']['CPM_ID']; ?>">
    <input type="hidden" name="a" id="a" value="<?php echo $lapor->_a; ?>">
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
            <td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK RESTORAN</b></td>
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
					<input type="hidden" id="TBLJNSPJK" value="RESTORAN">
					<select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width:250px;"></select>
					<label id="loading"></label>
				</td>
			</tr>
		<?php endif;?>
		   
        <tr>
            <td>Nama Wajib Pajak <b class="isi">*</b></td>
            <td>: 
				<input type="text" name="PAJAK[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_WP'] ?>" readonly placeholder="Nama Wajib Pajak">
				<input type="hidden" name="PAJAK[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" value="<?php echo $DATA['profil']['CPM_ALAMAT_WP'] ?>">
				<input type="hidden" name="PAJAK[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" value="<?php echo $DATA['profil']['CPM_KECAMATAN_WP'] ?>">
				<input type="hidden" name="PAJAK[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" value="<?php echo $DATA['profil']['CPM_KELURAHAN_WP'] ?>">
            </td>
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
            <td>: 
				<input type="text" name="PAJAK[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $DATA['profil']['CPM_NAMA_OP'] ?>" readonly placeholder="Nama Objek Pajak">
				
				<input type="hidden" name="PAJAK[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" value="<?php echo $DATA['profil']['CPM_ALAMAT_OP'] ?>">
				<input type="hidden" name="PAJAK[CPM_NAMA_KECAMATAN_OP]" id="CPM_NAMA_KECAMATAN_OP" value="<?php echo $DATA['profil']['CPM_NAMA_KECAMATAN_OP'] ?>">
				<input type="hidden" name="PAJAK[CPM_KECAMATAN_OP]" id="KECAMATAN_OP" value="<?php echo $DATA['profil']['CPM_KECAMATAN_OP'] ?>">
				<input type="hidden" name="PAJAK[CPM_NAMA_KELURAHAN_OP]" id="CPM_NAMA_KELURAHAN_OP" value="<?php echo $DATA['profil']['CPM_NAMA_KELURAHAN_OP'] ?>">
				<input type="hidden" name="PAJAK[CPM_KELURAHAN_OP]" id="KELURAHAN_OP" value="<?php echo $DATA['profil']['CPM_KELURAHAN_OP'] ?>">
			</td>
        </tr>
        <tr valign="top">
            <td>Rekening Pajak <b class="isi">*</b></td>
            <td>: <select name="PAJAK[CPM_REKENING]" id="CPM_REKENING" style="width:590px;">
                    <?php
                    if(isset($DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']])){
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
            <td colspan="2" align="center" class="subtitle"><b>TRANSAKSI RESTORAN</b></td>
        </tr>         
        <tr>
            <td colspan="2">
                <table width="100%" border="0" align="center" class="child">                    
                    <tr>
                        <th colspan="3">Data Pajak</th>
                    </tr>
                    <tr>
                        <td>Jumlah Meja <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[JumlahMeja]" id="JumlahMeja" class="number" maxlength="17" placeholder="Jumlah Meja"></td>
                    </tr>
                    <tr>
                        <td>Jumlah Kursi <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[JumlahKursi]" id="JumlahKursi" class="number" maxlength="17" placeholder="Jumlah Kursi"></td>
                    </tr>
                    <tr>
                        <td>Jumlah Pengunjung <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[JumlahPengunjung]" id="JumlahPengunjung" class="number" maxlength="17" placeholder="Jumlah Pengunjung"></td>
                    </tr>
                    <tr>
                        <td>Nominal Transaksi <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[TransactionAmount]" id="TransactionAmount" class="number SUM" placeholder="Nominal Transaksi" style="width:300px"></td>
                    </tr>
                    <tr>
                        <td>Tarif Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[CPM_TARIF_PAJAK]" id="CPM_TARIF_PAJAK" style="width: 50px;" readonly value="<?php echo $DATA['tarif']?>" placeholder="Tarif Pajak"> %</td>
                    </tr>
                    <tr>
                        <td>Nominal Pajak <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[TaxAmount]" id="TaxAmount" class="number" readonly value="0" placeholder="Nominal Pajak" style="width:300px"></td>
                    </tr>
                    <tr>
                        <td>Tanggal Transaksi <b class="isi">*</b></td>
                        <td> : <input type="text" name="PAJAK[TransactionDate]" id="TransactionDate" placeholder="Tanggal Transaksi" style="width:300px" readonly></td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <tr class="button-area">
            <td align="center" colspan="2">                
                <?php
                if (in_array($lapor->_mod, array("pel", ""))) {
                    if (in_array($lapor->_s, array(1, ""))) {
                        echo "<input type=\"reset\" value=\"Reset\">\n ";
                        echo "<input type=\"submit\" class=\"btn-submit\" action=\"save\" value=\"Simpan\">";
                        
                    }
                } 
                ?>
            </td>
        </tr>
    </table>
</form>
