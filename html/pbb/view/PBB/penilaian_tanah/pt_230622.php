<?php
// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";
// exit;
if ($data) {
    $uid = $data->uid;

    $bOk = $User->GetModuleInArea($uid, $area, $moduleIds);
    if (!$bOK) {
        die("Function access not permitted");
    }

    require_once("inc/PBB/dbUtils.php");

    $User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
    $appConfig = $User->GetAppConfig($a);

    $dbUtils = new DbUtils($dbSpec);

    //    print_r($appConfig);
    $userDetail = $dbUtils->getUserDetailPbb($uid);
    $aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));

    $aKelurahan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);

    $kec = (isset($_REQUEST['kec'])) ? $_REQUEST['kec'] : $aKecamatan[0]['CPC_TKC_ID'];

    //    $kel = (isset($_REQUEST['OP_KELURAHAN']))? $_REQUEST['OP_KELURAHAN']: $aKelurahan[0]['CPC_TKL_ID'];
    $kel = (isset($_REQUEST['OP_KELURAHAN'])) ? $_REQUEST['OP_KELURAHAN'] : 'none';
    $thn = (isset($_REQUEST['TAHUN_PAJAK'])) ? $_REQUEST['TAHUN_PAJAK'] : $appConfig['tahun_tagihan'];
    //    echo $kel.' - '.$_REQUEST['OP_KELURAHAN'];
?>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript">
        function changeKelFilter(kel) {
            //                var  formData = "kel="+kel.value;
            $('#form1').trigger("submit");
            //                $.ajax({
            //                        url : "",
            //                        type: "POST",
            //                        data : formData,
            //                        success: function(data, textStatus, jqXHR){
            //                            alert('tes');
            //                                //$("#tbl").load();
            //                        },
            //                        error: function (jqXHR, textStatus, errorThrown){
            //                        }
            //                });
        }

        function DelAll() {
            var b = confirm("Apakah anda yakin menghapus dengan mode All?");
            if (b == false) {
                return false;
            } else {
                return true;
            }
        }

        function prosesDel(a, thn, a_link) {
            var b = confirm("Anda akan menghapus kode ZNT " + a + " untuk tahun " + thn + " ?");
            if (b == false) {
                return false;
            } else {
                // alert(a_link);
                window.open(a_link, "_parent");
                return true;
            }
        }
        var n = 6;
        var y = 0;

        function addRows() {
            // if(y==0){
            //     v=eval(document.getElementById("lokasi[]").value)+5;
            // }else if(y!=0){
            //     v++;
            // }
            var row = document.getElementById("tableAdd").insertRow(n);
            row.insertCell(0).innerHTML = "<input name='lokasi[]' type='hidden' id='lokasi[]' class='lokasiall' maxlength='255' value='" + document.getElementById("OP_KELURAHAN").value + "' /> <input name='znt[]' class=\"form-control\" type='text' id='znt[]' maxlength='2' />";
            row.insertCell(1).innerHTML = "<input name='nir[]' class=\"form-control\" type='text' id='nir[]' maxlength='10' />";
            row.insertCell(2).innerHTML = "<input name='thn[]' class=\"form-control\" type='text' id='thn[]' maxlength='4' />";
            // n++;
            // y++;
            // alert("123");
        }

        function addRowsMulti() {
            for (i = 0; i < 5; i++) {
                addRows();
            }
        }

        function Check() {
            allCheckList = document.getElementById("form1").elements;
            jumlahCheckList = allCheckList.length;
            if (document.getElementById("tombolCheck").value == "Pilih Semua") {
                for (i = 0; i < jumlahCheckList; i++) {
                    allCheckList[i].checked = true;
                }
                document.getElementById("tombolCheck").value = "Batal Pilih Semua";
            } else {
                for (i = 0; i < jumlahCheckList; i++) {
                    allCheckList[i].checked = false;
                }
                document.getElementById("tombolCheck").value = "Pilih Semua";
            }
        }

        function showKelFilter(x) {
            var val = x.value;
            <?php foreach ($aKecamatan as $row) { ?>
                if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                    document.getElementById('sKel').innerHTML = "<?php
                                                                    echo "<select class='form-control' name='OP_KELURAHAN' id='OP_KELURAHAN' onchange='changeKelFilter(this);'><option>Pilih</option>";
                                                                    foreach ($aKelurahan as $row2) {
                                                                        if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                            $digit3 = substr($row2['CPC_TKL_ID'], 7, 3);
                                                                            echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($OP_KELURAHAN) && $OP_KELURAHAN == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . " - $digit3" . "</option>";
                                                                        }
                                                                    }
                                                                    echo "</select>";
                                                                    ?>";
                }
            <?php } ?>
        }

        function showKel(x) {
            var val = x.value;
            <?php foreach ($aKecamatan as $row) { ?>
                if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                    document.getElementById('sKel').innerHTML = "<?php
                                                                    echo "<select class='form-control' name='OP_KELURAHAN' id='OP_KELURAHAN' onchange='changeKel(this);'>";
                                                                    foreach ($aKelurahan as $row2) {
                                                                        if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                            echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($OP_KELURAHAN) && $OP_KELURAHAN == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
                                                                        }
                                                                    }
                                                                    echo "</select>";
                                                                    ?>";
                }
            <?php } ?>
        }

        function changeKel(x) {
            var val = x.value;
            $('.lokasiall').val(val);
        }


        function excelKlasifikasiZNT() {
            var namakota = '<?php echo $appConfig['NAMA_KOTA']; ?>';
            // var thn = '<?php echo $appConfig['tahun_tagihan']; ?>';
            var tahun = $("#TAHUN_PAJAK option:selected").val();
            // alert(thn);
            // alert(tahun);
            var kelurahan = $("#OP_KELURAHAN").val();

            var namakec = $("#kec option:selected").text();
            var namakel = $("#OP_KELURAHAN option:selected").text();
            window.open("function/PBB/penilaian_tanah/print-excel-klasifikasiznt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m'}"); ?>&nkel=" + namakel + "&nkec=" + namakec + "&kel=" + kelurahan + "&th=" + tahun + "&kota=" + namakota + "&tahun=" + tahun);
        }

        function excelZNT() {
            var namakota = '<?php echo $appConfig['NAMA_KOTA']; ?>';
            var thn = '<?php echo $appConfig['tahun_tagihan']; ?>';
            var tahun = $("#TAHUN_PAJAK option:selected").val();
            var kelurahan = $("#OP_KELURAHAN").val();
            var namakec = $("#kec option:selected").text();
            var namakel = $("#OP_KELURAHAN option:selected").text();
            window.open("function/PBB/penilaian_tanah/print-excel-znt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m'}"); ?>&nkel=" + namakel + "&nkec=" + namakec + "&kel=" + kelurahan + "&th=" + thn + "&kota=" + namakota + "&tahun=" + tahun);
        }
    </script>
    <div class="col-md-12">
        <div id="tbl">
            <h3>SISTEM PENILAIAN TANAH <br /> (TABEL ZNT) </h3>
            <?php
            if (!empty($_REQUEST['btTambah'])) {
                $jArray = count($_REQUEST['lokasi']);
                for ($i = 0; $i < $jArray; $i++) {
                    $id = $_REQUEST['lokasi'][$i] . $_REQUEST['znt'][$i];
                    $lokasi = $_REQUEST['lokasi'][$i];
                    $znt = $_REQUEST['znt'][$i];
                    $nir = $_REQUEST['nir'][$i];
                    $thn = $_REQUEST['thn'][$i];
                    if (!empty($lokasi) and !empty($znt) and !empty($nir)) {
                        $sqlTampil = "INSERT INTO cppmod_pbb_znt (CPM_ID, CPM_KODE_LOKASI, CPM_KODE_ZNT, CPM_NIR, CPM_TAHUN) VALUES ('$id', '$lokasi', '$znt', '$nir', '$thn');";
                        $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
                    }
                }
                if ($bOK) {
                    echo "<b>" . ($bOK - 1) . " data ditambahkan !</b>";
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
                    $thn = $_REQUEST['thn'][$i];
                    $sqlTampil = "UPDATE cppmod_pbb_znt SET CPM_KODE_LOKASI = '$lokasi', CPM_KODE_ZNT = '$znt', CPM_NIR = '$nir' WHERE CPM_ID ='$id' AND CPM_TAHUN = '$thn';";
                    //echo $sqlTampil;exit;
                    $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
                }
                if ($bOK) {
                    echo "<b>" . ($bOK - 1) . " data diubah!</b>";
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
                    $thn = $_REQUEST['thn'][$i];
                    if (empty($id)) {
                        $id = $_REQUEST['ID2'];
                        $thn = $_REQUEST['THN2'];
                    }
                    $sqlTampil = "DELETE FROM cppmod_pbb_znt WHERE CPM_ID ='$id' AND CPM_TAHUN ='$thn';";
                    // echo $sqlTampil;exit;
                    $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
                }
                if ($bOK) {
                    echo "<b>" . ($bOK - 1) . " data dihapus!</b>"; //dikurangi 2 karena ada variabel $bOK di modul
            ?>
                    <script type="text/javascript">
                        setTimeout(function(e) {
                            $('#form1').trigger("submit");
                        }, 1000);
                    </script>
                <?php
                } else {
                    echo mysqli_error($DBLink);
                }
            }
            //echo "<br>"; print_r($_REQUEST);
            // echo "a=$a&m=$m&f=$f";
            if (empty($_REQUEST['tambahData']) and empty($_REQUEST['editData'])) {
                ?>
                <form id="form1" name="form1" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-1">Filter</div>
                                <div class="col-md-3">
                                    <?php
                                    echo "
                                    <select class=\"form-control\" name=\"kec\" id=\"kec\" onchange=\"showKelFilter(this)\">
                                        <option value=\"\">Kecamatan</option>";
                                    foreach ($aKecamatan as $row) {
                                        $digit3 = substr($row['CPC_TKC_ID'], 4, 3);

                                        echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . " - $digit3" . "</option>";
                                    }

                                    echo "</select>";
                                    ?>
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    echo "<div id=\"sKel\" style=\"margin-left:5px; display:inline-block;\" >";
                                    foreach ($aKecamatan as $row) {
                                        if ($kec == $row['CPC_TKC_ID']) {

                                            echo "<select class=\"form-control\" name='OP_KELURAHAN' id='OP_KELURAHAN' onchange='changeKelFilter(this);'>
                                                <option>Pilih</option>";
                                            foreach ($aKelurahan as $row2) {
                                                if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                    $digit3 = substr($row2['CPC_TKL_ID'], 7, 3);
                                                    echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . " - $digit3" . "</option>";
                                                }
                                            }
                                            echo "</select>";
                                        }
                                    }
                                    echo "</div>";
                                    ?>
                                </div>
                                <div class="col-md-2">
                                    <?php
                                    echo "<select name=\"TAHUN_PAJAK\" class=\"form-control\" id=\"TAHUN_PAJAK\" onchange=\"changeKelFilter(this);\">
                                        ";
                                    for ($t = $appConfig['tahun_tagihan']; $t > 1993; $t--) {
                                        echo "<option value=\"$t\" " . ((isset($t) && $t == $thn) ? "selected" : "") . ">" . $t . "</option>";
                                    }

                                    echo "</select>";
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-12" style="margin-bottom: 10px;">
                            <button class="btn btn-primary btn-orange mb5" name="tambahData" type="submit" id="tambahData" value="Tambah Data">Tambah Data</button>
                            <button class="btn btn-primary btn-blue mb5" name="editData" type="submit" id="editData" value="Ubah">Ubah</button>
                            <button class="btn btn-primary bg-maka mb5" name="btHapus" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus">Hapus</button>
                            <button class="btn btn-primary btn-orange mb5" type="button" name="buttonToExcelKlasifikasi" value="Ekspor ke xls ZNT" onClick="excelZNT()">Ekspor ke xls ZNT</button>
                            <button class="btn btn-primary btn-blue mb5" type="button" name="buttonToExcelKlasifikasi" value="Ekspor ke xls Klasifikasi ZNT" onClick="excelKlasifikasiZNT()">Ekspor ke xls Klasifikasi ZNT</button>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="tdheader"><button class="btn btn-primary bg-maka" onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck">Pilih Semua</button></th><!--                        <th scope="col">ID</th>-->
                                        <!--                        <th scope="col" width='120'>KODE KELURAHAN</th>-->
                                        <th class="tdheader">KODE ZNT</th>
                                        <th class="tdheader">NIR</th>
                                        <th class="tdheader">TAHUN</th>
                                        <th class="tdheader">PROSES</th>
                                    </tr>
                                    <?php
                                    // IFNULL(CPM_NIR2,CPM_NIR) AS
                                    $sqlTampil = "SELECT CPM_ID, CPM_KODE_ZNT,  CPM_NIR, CPM_TAHUN FROM (
                            SELECT CPM_ID, A.CPM_KODE_ZNT,A.CPM_NIR, B.CPM_NJOP_M2 as CPM_NIR2, A.CPM_TAHUN FROM cppmod_pbb_znt A
                            LEFT JOIN cppmod_pbb_kelas_bumi B 
                            ON rpad(B.CPM_KELAS,3,' ')= rpad(A.CPM_KODE_ZNT,3,' ') ";

                                    if ($kel)
                                        $sqlTampil .= "WHERE A.CPM_KODE_LOKASI = '" . $kel . "'  ";

                                    if ($thn)
                                        $sqlTampil .= " AND A.CPM_TAHUN = '" . $thn . "'  ";
                                    $sqlTampil .= ") TBL ORDER BY CPM_KODE_ZNT";
                                    // echo $sqlTampil;
                                    // exit;
                                    $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                                    $n = 0;
                                    $no = 0;
                                    while ($r = mysqli_fetch_assoc($result)) {
                                        $no++;
                                        $class = $no % 2 == 0 ? "tdbody2" : "tdbody1";
                                    ?>
                                        <tr>
                                            <td class="<?php echo $class; ?>"><input name="ID[]" type="checkbox" id="ID[<?php echo $n; ?>]" value="<?php echo $r['CPM_ID']; ?>" /></td>
                                            <!--                            <td align='center'><?php echo $r['CPM_KODE_LOKASI']; ?></td>-->
                                            <td class="<?php echo $class; ?>" align='center'><?php echo $r['CPM_KODE_ZNT']; ?></td>
                                            <td class="<?php echo $class; ?>" align='right'><?php echo number_format($r['CPM_NIR'], 2, ',', '.'); ?></td>
                                            <td class="<?php echo $class; ?>" align='right'><?php echo $r['CPM_TAHUN']; ?></td>
                                            <td class="<?php echo $class; ?>" align='center'>
                                                <?php //if(substr($r['CPM_KODE_LOKASI'], 0,1) == )
                                                $idx = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
                                                if (!in_array(substr($r['CPM_KODE_ZNT'], 0, 1), $idx)) {
                                                ?>
                                                    <a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&ID2=$r[CPM_ID]&TAHUN_PAJAK=$_REQUEST[TAHUN_PAJAK]"); ?>">Ubah</a> |
                                                <?php }
                                                ?>

                                                <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE_ZNT']; ?>','<?php echo $r['CPM_TAHUN']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&ID2=$r[CPM_ID]&THN2=$r[CPM_TAHUN]&TAHUN_PAJAK=$_REQUEST[TAHUN_PAJAK]"); ?>')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php
                                        $n++;
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            <?php } else if (!empty($_REQUEST['tambahData'])) { ?>
                <form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
                    <?php
                    $sqlTampil = "SELECT MAX(CPM_ID) as tId FROM cppmod_pbb_znt;";
                    $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                    $r = mysqli_fetch_assoc($result);
                    $tId = $r['tId'];
                    ?>
                    <button class="btn btn-primary bg-maka" type="button" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button><br />
                    <div class="row">
                        <div class="col-md-12" style="margin-top: 15px; margin-bottom: 15px;">
                            <label>Tambah Data</label><br />
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-control" name="OP_KECAMATAN" id="OP_KECAMATAN" onchange="showKel(this)">
                                        <option value="">Kecamatan</option>
                                        <?php
                                        foreach ($aKecamatan as $row)
                                            echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($OP_KECAMATAN) && $OP_KECAMATAN == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div id="sKel">
                                        <select class="form-control" name="OP_KELURAHAN" id="OP_KELURAHAN">
                                            <option value="">Kelurahan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tableAdd">
                                    <tr>
                                        <!--                        <td width="144">ID : </td>-->
                                        <!--                        <td width="144">Kode Kelurahan : </td>-->
                                        <td width="200">Kode ZNT: </td>
                                        <td width="200">NIR : </td>
                                        <td width="200">TAHUN : </td>
                                    </tr>
                                    <?php
                                    for ($i = 0; $i < 5; $i++) {
                                        //                        $tId++;
                                    ?>
                                        <tr>
                                            <!--                            <td><input name="id[]" type="text" id="id[]" readonly="true" value="<?php echo $tId; ?>"></td>-->
                                            <!--                            <td><input name="lokasi[]" type="text" id="lokasi[]" class="lokasiall" maxlength="255" /></td>-->
                                            <td>
                                                <input name="lokasi[]" class="form-control" type="hidden" id="lokasi[]" class="lokasiall" value="<?php echo $kel ?>" maxlength="255" />
                                                <input name="znt[]" class="form-control" type="text" id="znt[]" maxlength="2" />
                                            </td>
                                            <td><input name="nir[]" class="form-control" type="text" id="nir[]" maxlength="10" /></td>
                                            <td><input name="thn[]" class="form-control" type="text" id="thn[]" maxlength="4" /></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                            <div style="float: right; margin-bottom: 15px;">
                                <button name="btTambah" class="btn btn-primary bg-maka" type="submit" id="btTambah" value="Simpan">Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php } else if (!empty($_REQUEST['editData'])) { ?>
                <form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
                    <div class="row">
                        <div class="col-md-12" style="margin-top: 15px; margin-bottom: 15px;">
                            <label>Ubah Data</label><br />
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <!--                        <td width="144">ID : </td>-->
                                        <!--                        <td width="144">Kode Kelurahan : </td>-->
                                        <td width="200">Kode ZNT: </td>
                                        <td width="200">NIR : </td>
                                        <td width="200">TAHUN : </td>
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

                                        $cpm_tahun = $_REQUEST['TAHUN_PAJAK'];
                                        $sqlTampil = "SELECT * FROM cppmod_pbb_znt where CPM_ID='$id' and CPM_TAHUN = '$cpm_tahun' ";
                                        // echo $sqlTampil;
                                        $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                                        $r = mysqli_fetch_assoc($result);
                                    ?>
                                        <tr>
                                            <input name="id[]" type="hidden" id="id[]" readonly="true" value="<?php echo $r['CPM_ID']; ?>">
                                            <input name="lokasi[]" type="hidden" id="lokasi[]" value="<?php echo $r['CPM_KODE_LOKASI']; ?>" maxlength="255" />
                                            <td><input name="znt[]" class="form-control" type="text" id="znt[]" value="<?php echo $r['CPM_KODE_ZNT']; ?>" maxlength="2" /></td>
                                            <td><input name="nir[]" class="form-control" type="text" id="nir[]" value="<?php echo $r['CPM_NIR']; ?>" maxlength="10" /></td>
                                            <td><input name="thn[]" class="form-control" type="text" id="thn[]" readonly="readonly" value="<?php echo $r['CPM_TAHUN']; ?>" maxlength="10" /></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                            <div class="float: right; margin-bottom: 15px;">
                                <button class="btn btn-primary bg-maka" name="btEdit" type="submit" id="btEdit" value="Simpan">Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
<?php } ?>
<div class="col-md-12">
    <label>Ket : Untuk kode ZNT berawalan 0,1,2,3,4,5,6,7,8,9 karena merupakan kode ZNT lama, sehingga nilai NIR mengacu ke data kelas bumi dengan kode kelas yang sama.</label>
</div>