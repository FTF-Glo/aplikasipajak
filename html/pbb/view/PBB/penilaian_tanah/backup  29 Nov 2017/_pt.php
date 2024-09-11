<?php
if ($data) {
    $uid = $data->uid;

    $bOk = $User->GetModuleInArea($uid, $area, $moduleIds);
    if (!$bOK) {
        die("Function access not permitted");
    }

    require_once("inc/PBB/dbUtils.php");
    $dbUtils = new DbUtils($dbSpec);

    $userDetail = $dbUtils->getUserDetailPbb($uid);
    $aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $userDetail[0]['kota']));
    $aKelurahan = $dbUtils->getKelOnKota($userDetail[0]['kota']);
    ?>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript">
		function changeKel(){
			 $( "#tbl" ).load(this.href); 
			/* var kel = $('#OP_KELURAHAN').val();
			$.ajax({
				url : "/view/PBB/penilaian_tanah/svc-znt.php",
				type: "POST",
				dataType: "json",
				data : "kel="+kel,
				success: function(data){
					alert("jsonData =" + data.dataZNT);
					$("#tbl").load();
				},
				error: function (jqXHR, textStatus, errorThrown){
				}
			}); */
		}
        function DelAll(){
            var b=confirm("Apakah anda yakin menghapus dengan mode All?");
            if(b==false){
                return false;
            }else{
                return true;
            }
        }
        function prosesDel(a,a_link){
            var b=confirm("Anda akan menghapus kode "+a+" ?");
            if(b==false){
                return false;
            }else{
                window.open(a_link,"_parent");
                return true;
            }
        }
        var n=7;
        var y=0;
        function addRows(){
            if(y==0){
                v=eval(document.getElementById("id[]").value)+5;
            }else if(y!=0){
                v++;
            }
            var row=document.getElementById("tableAdd").insertRow(n);
            row.insertCell(0).innerHTML="<input name='id[]' type='text' id='id[]' value='"+v+"' readonly='true' />";
            row.insertCell(1).innerHTML="<input name='lokasi[]' type='text' id='lokasi[]' class='lokasiall' maxlength='255' />";
            row.insertCell(2).innerHTML="<input name='znt[]' type='text' id='znt[]' maxlength='255' />";
            row.insertCell(3).innerHTML="<input name='nir[]' type='text' id='nir[]' maxlength='255' />";        
            n++;
            y++;
        }
        function addRowsMulti(){
            for(i=0;i<5;i++){
                addRows();
            }
        }
        function Check(){
            allCheckList = document.getElementById("form1").elements;
            jumlahCheckList = allCheckList.length;
            if(document.getElementById("tombolCheck").value == "Pilih Semua"){
                for(i = 0; i < jumlahCheckList; i++){
                    allCheckList[i].checked = true;
                }
                document.getElementById("tombolCheck").value = "Batal Pilih Semua";
            }else{
                for(i = 0; i < jumlahCheckList; i++){
                    allCheckList[i].checked = false;
                }
                document.getElementById("tombolCheck").value = "Pilih Semua";
            }
        }
                                            
        function showKel(x) {
            var val = x.value;
    <?php foreach ($aKecamatan as $row) { ?>
                if(val=="<?php echo $row['CPC_TKC_ID']; ?>"){
                    document.getElementById('sKel').innerHTML="<?php
        echo "<select name='OP_KELURAHAN' id='OP_KELURAHAN' onchange='changeKel(this);'><option value=''>Kelurahan</option>";
        foreach ($aKelurahan as $row2) {
            if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($OP_KELURAHAN) && $OP_KELURAHAN == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
            }
        } echo"</select>";
        ?>";
                    }
    <?php } ?>
        }
                
        function changeKel(x) {
            var val = x.value;
            $('.lokasiall').val(val);
        }
    </script>
    <div id="tbl" align="left">
        <h3>SISTEM PENILAIAN TANAH <br /> (TABEL ZNT) </h3>
        <br />
        <?php
        if (!empty($_REQUEST['btTambah'])) {
            $jArray = count($_REQUEST['lokasi']);
            for ($i = 0; $i < $jArray; $i++) {
                $id = $_REQUEST['id'][$i];
                $lokasi = $_REQUEST['lokasi'][$i];
                $znt = $_REQUEST['znt'][$i];
                $nir = $_REQUEST['nir'][$i];
                if (!empty($lokasi) and !empty($znt) and !empty($nir)) {
                    $sqlTampil = "INSERT INTO cppmod_pbb_znt (CPM_ID, CPM_KODE_LOKASI, CPM_KODE_ZNT, CPM_NIR) VALUES ('$id', '$lokasi', '$znt', '$nir');";
                    $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
                }
            }
            if ($bOK) {
                echo"<b>" . ($bOK - 1) . " data ditambahkan !</b>";
            } else {
                echo mysqli_error($DBLink);
            }
        } elseif (!empty($_REQUEST['btEdit'])) {
            $jArray = count($_REQUEST['id']);
            for ($i = 0; $i < $jArray; $i++) {
                $id = $_REQUEST['id'][$i];
                $lokasi = $_REQUEST['lokasi'][$i];
                $znt = $_REQUEST['znt'][$i];
                $nir = $_REQUEST['nir'][$i];
                $sqlTampil = "UPDATE cppmod_pbb_znt SET CPM_KODE_LOKASI = '$lokasi', CPM_KODE_ZNT = '$znt', CPM_NIR = '$nir' WHERE CPM_ID ='$id';";
                $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
            }
            if ($bOK) {
                echo"<b>" . ($bOK - 1) . " data diubah!</b>";
            } else {
                echo mysqli_error($DBLink);
            }
        } elseif (!empty($_REQUEST['btHapus'])) {
            $jArray = count($_REQUEST['ID']);
            if ($jArray == 0) {
                $jArray = 1;
            }
            for ($i = 0; $i < $jArray; $i++) {
                $id = $_REQUEST['ID'][$i];
                if (empty($id)) {
                    $id = $_REQUEST['ID2'];
                }
                $sqlTampil = "DELETE FROM cppmod_pbb_znt WHERE CPM_ID ='$id';";
                $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
            }
            if ($bOK) {
                echo"<b>" . ($bOK - 1) . " data dihapus!</b>"; //dikurangi 2 karena ada variabel $bOK di modul
            } else {
                echo mysqli_error($DBLink);
            }
        }
//echo "<br>"; print_r($_REQUEST);
        if (empty($_REQUEST['tambahData']) and empty($_REQUEST['editData'])) {
            ?>
            <form id="form1" name="form1" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
				<?php 	echo "&nbsp;&nbsp;Filter <select name=\"kec\" id=\"kec\" onchange=\"showKel(this)\">";
						echo "<option value=\"\">Kecamatan</option>";           
						foreach($aKecamatan as $row) 
						echo "<option value='".$row['CPC_TKC_ID']."' ".((isset($kec) && $kec==$row['CPC_TKC_ID']) ? "selected" : "").">".$row['CPC_TKC_KECAMATAN']."</option>";
						echo "</select>";
						echo "<div id=\"sKel\" style=\"margin-left:5px; display:inline-block;\" >";
						echo "</div>";
				?>
				<br>
				<input name="tambahData" type="submit" id="tambahData" value="Tambah Data" />
                <input name="editData" type="submit" id="editData" value="Ubah" />
                <input name="btHapus" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus" />
                <table width="" border="1" cellspacing="0" cellpadding="3">
                    <tr>
                        <th scope="col"><input onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck" /></th>
                        <!--<th scope="col">NO</th>-->
                        <th scope="col">ID</th>
                        <th scope="col">KODE LOKASI</th>
                        <th scope="col">KODE ZNT</th>
                        <th scope="col">NIR</th>
                        <th scope="col">PROSES</th>
                    </tr>
                    <?php
                    $sqlTampil = "SELECT * FROM cppmod_pbb_znt ";
                    $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                    $n = 0;
                    $no = 0;
                    while ($r = mysqli_fetch_assoc($result)) {
                        $no++;
                        ?>  
                        <tr>
                            <td><input name="ID[]" type="checkbox" id="ID[<?php echo $n; ?>]" value="<?php echo $r['CPM_ID']; ?>" /></td>
                            <!--<td><?php //echo $no;                                                    ?></td>-->
                            <td><?php echo $r['CPM_ID']; ?></td>
                            <td><?php echo $r['CPM_KODE_LOKASI']; ?></td>
                            <td><?php echo $r['CPM_KODE_ZNT']; ?></td>
                            <td><?php echo number_format($r['CPM_NIR'], null, null, '.'); ?></td>                        
                            <td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&ID2=$r[CPM_ID]"); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_ID']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&ID2=$r[CPM_ID]"); ?>')">Hapus</a></td>
                        </tr>
                        <?php
                        $n++;
                    }
                    ?>  
                </table>
            </form>
        <?php } else if (!empty($_REQUEST['tambahData'])) { ?>
            <form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
                <?php
                $sqlTampil = "SELECT MAX(CPM_ID) as tId FROM cppmod_pbb_znt;";
                $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                $r = mysqli_fetch_assoc($result);
                $tId = $r['tId'];
                ?><input name="" type="button" value="Tambah Baris" onclick="addRowsMulti()" />
                <table width="300" border="0" cellpadding="3" cellspacing="0" id="tableAdd">
                    <tr>
                        <th>Tambah Data</th> 
                        <th colspan="3">
                            <select name="OP_KECAMATAN" id="OP_KECAMATAN" onchange="showKel(this)" style="float:left;">
                                <option value="">Kecamatan</option>
                                <?php
                                foreach ($aKecamatan as $row)
                                    echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($OP_KECAMATAN) && $OP_KECAMATAN == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
                                ?>
                            </select>
                    <div id="sKel" style="float:left; margin-left:5px;">
                        <select name="OP_KELURAHAN" id="OP_KELURAHAN">
                            <option value="">Kelurahan</option>
                        </select>
                    </div>
                    </th>
                    </tr>
                    <tr>
                        <td width="144">ID : </td>
                        <td width="144">Kode Lokasi : </td>
                        <td width="144">Kode ZNT: </td>
                        <td width="144">NIR : </td>
                    </tr>
                    <?php
                    for ($i = 0; $i < 5; $i++) {
                        $tId++;
                        ?>
                        <tr>
                            <td><input name="id[]" type="text" id="id[]" readonly="true" value="<?php echo $tId; ?>"></td>
                            <td><input name="lokasi[]" type="text" id="lokasi[]" class="lokasiall" maxlength="255" /></td>
                            <td><input name="znt[]" type="text" id="znt[]" maxlength="255" /></td>
                            <td><input name="nir[]" type="text" id="nir[]" maxlength="255" /></td>                    
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                        <td><div align="right">
                                <input name="btTambah" type="submit" id="btTambah" value="Simpan" />
                            </div></td>
                    </tr>
                </table>
            </form>
        <?php } else if (!empty($_REQUEST['editData'])) { ?>
            <form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
                <table width="300" border="0" cellpadding="3" cellspacing="0">
                    <tr>
                        <th colspan="4">Ubah Data </th>
                    </tr>
                    <tr>
                        <td width="144">ID : </td>
                        <td width="144">Kode Lokasi : </td>
                        <td width="144">Kode ZNT: </td>
                        <td width="144">NIR : </td>
                    </tr>
                    <?php
                    $jArray = count($_REQUEST['ID']);
                    if ($jArray == 0) {
                        $jArray = 1;
                    }
                    for ($i = 0; $i < $jArray; $i++) {
                        $id = $_REQUEST['ID'][$i];
                        if (empty($id)) {
                            $id = $_REQUEST['ID2'];
                        }
                        $sqlTampil = "SELECT * FROM cppmod_pbb_znt where CPM_ID='$id';";
                        $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                        $r = mysqli_fetch_assoc($result);
                        ?>
                        <tr>
                            <td><input name="id[]" type="text" id="id[]" readonly="true" value="<?php echo $r['CPM_ID']; ?>"></td>
                            <td><input name="lokasi[]" type="text" id="lokasi[]" value="<?php echo $r['CPM_KODE_LOKASI']; ?>" maxlength="255" /></td>
                            <td><input name="znt[]" type="text" id="znt[]" value="<?php echo $r['CPM_KODE_ZNT']; ?>" maxlength="255" /></td>
                            <td><input name="nir[]" type="text" id="nir[]" value="<?php echo $r['CPM_NIR']; ?>" maxlength="255" /></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                        <td><div align="right">
                                <input name="btEdit" type="submit" id="btEdit" value="Simpan" />
                            </div></td>
                    </tr>
                </table>
            </form>
        <?php } ?>
    </div>
<?php } ?>
