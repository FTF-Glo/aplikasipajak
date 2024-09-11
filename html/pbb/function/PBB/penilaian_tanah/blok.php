<?php
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

    $kel = (isset($_REQUEST['OP_KELURAHAN'])) ? $_REQUEST['OP_KELURAHAN'] : 'none';

    $arrStatusPeta = array('0' => 'Tidak Ada', '1' => 'Ada');

?>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript">
        function changeKelFilter(kel) {
            $('#form1').submit();
        }

        function DelAll() {
            var b = confirm("Apakah anda yakin menghapus dengan mode All?");
            if (b == false) {
                return false;
            } else {
                return true;
            }
        }

        function prosesDel(a, a_link) {
            var b = confirm("Anda akan menghapus kode " + a + " ?");
            if (b == false) {
                return false;
            } else {
                window.open(a_link, "_parent");
                return true;
            }
        }
        var n = 6;
        var y = 0;

        function addRows() {
            if (y == 0) {
                v = eval(document.getElementById("lokasi[]").value) + 5;
            } else if (y != 0) {
                v++;
            }
            var row = document.getElementById("tableAdd").insertRow(n);
            row.insertCell(0).innerHTML = "<input class=\"form-control\" name='lokasi[]' type='hidden' id='lokasi[]' class='lokasiall' maxlength='255' value='" + document.getElementById("OP_KELURAHAN").value + "' /> <input class=\"form-control\" name='znt[]' type='text' id='znt[]' maxlength='2' />";
            row.insertCell(1).innerHTML = "<input class=\"form-control\" name='nir[]' type='text' id='nir[]' maxlength='10' />";
            n++;
            y++;
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
                                                                    echo "<select name='OP_KELURAHAN' class='form-control' id='OP_KELURAHAN' onchange='changeKelFilter(this);'><option value=''>Kelurahan</option>";
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

        function showKel(x) {
            var val = x.value;
            <?php foreach ($aKecamatan as $row) { ?>
                if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                    document.getElementById('sKel').innerHTML = "<?php
                                                                    echo "<select name='OP_KELURAHAN' class='form-control' id='OP_KELURAHAN' onchange='changeKel(this);'><option value=''>Kelurahan</option>";
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
    </script>
    <div class="col-md-12" id="tbl">
        <h3>SISTEM PENILAIAN TANAH <br /> (TABEL BLOK) </h3>
        <?php
        if (!empty($_REQUEST['btTambah'])) {
            $jArray = count($_REQUEST['lokasi']);
            for ($i = 0; $i < $jArray; $i++) {
                $lokasi = $_REQUEST['lokasi'][$i];
                $blok = $_REQUEST['blok'][$i];
                if (!empty($lokasi) and !empty($blok)) {
                    $status = isset($_REQUEST['status' . $i]) ? '1' : '0';
                    $propinsi = substr($lokasi, 0, 2);
                    $kabkota = substr($lokasi, 2, 2);
                    $kecamatan = substr($lokasi, 4, 3);
                    $kelurahan = substr($lokasi, 7, 3);
                    $sqlTampil = "INSERT INTO cppmod_pbb_blok (CPM_KD_PROPINSI, CPM_KD_DATI2, CPM_KD_KECAMATAN, CPM_KD_KELURAHAN, CPM_KD_BLOK, CPM_STATUS_PETA_BLOK) VALUES ('$propinsi', '$kabkota', '$kecamatan', '$kelurahan', '$blok','$status')";
                    $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
                }
            }
            if ($bOK) {
                echo "<b>" . ($bOK - 1) . " data ditambahkan !</b>";
            } else {
                echo mysqli_error($DBLink);
            }
        } elseif (!empty($_REQUEST['btEdit'])) {
            $jArray = count($_REQUEST['lokasi']);
            for ($i = 0; $i < $jArray; $i++) {
                $lokasi = $_REQUEST['lokasi'][$i];
                $blok = $_REQUEST['blok'][$i];
                $status = isset($_REQUEST['status' . $i]) ? '1' : '0';
                $propinsi = substr($lokasi, 0, 2);
                $kabkota = substr($lokasi, 2, 2);
                $kecamatan = substr($lokasi, 4, 3);
                $kelurahan = substr($lokasi, 7, 3);
                $sqlTampil = "UPDATE cppmod_pbb_blok SET CPM_STATUS_PETA_BLOK='$status' WHERE  CPM_KD_PROPINSI = '$propinsi' AND CPM_KD_DATI2 = '$kabkota' AND CPM_KD_KECAMATAN = '$kecamatan' AND CPM_KD_KELURAHAN = '$kelurahan' AND CPM_KD_BLOK = '$blok' ;";
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
                if (empty($id)) {
                    $id = $_REQUEST['ID2'];
                }
                $propinsi = substr($id, 0, 2);
                $kabkota = substr($id, 2, 2);
                $kecamatan = substr($id, 4, 3);
                $kelurahan = substr($id, 7, 3);
                $blok = substr($id, 10, 3);
                $sqlTampil = "DELETE FROM cppmod_pbb_blok WHERE CPM_KD_PROPINSI = '$propinsi' AND CPM_KD_DATI2 = '$kabkota' AND CPM_KD_KECAMATAN = '$kecamatan' AND CPM_KD_KELURAHAN = '$kelurahan' AND CPM_KD_BLOK = '$blok';";
                $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
            }
            if ($bOK) {
                echo "<b>" . ($bOK - 1) . " data dihapus!</b>"; //dikurangi 2 karena ada variabel $bOK di modul
            } else {
                echo mysqli_error($DBLink);
            }
        }

        if (empty($_REQUEST['tambahData']) and empty($_REQUEST['editData'])) {
        ?>
            <form id="form1" name="form1" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
                <div class="row">
                    <div class="col-md-1">Filter</div>
                    <div class="col-md-3">
                        <?php
                        echo "<select class=\"form-control\" name=\"kec\" id=\"kec\" onchange=\"showKelFilter(this)\">";
                        echo "<option value=\"\">Kecamatan</option>";
                        foreach ($aKecamatan as $row)
                            echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
                        echo "</select>";
                        ?>
                    </div>
                    <div class="col-md-3">
                        <?php
                        echo "<div id=\"sKel\" style=\"margin-left:5px; display:inline-block;\" >";
                        foreach ($aKecamatan as $row) {
                            if ($kec == $row['CPC_TKC_ID']) {
                                echo "<select class=\"form-control\" name='OP_KELURAHAN' id='OP_KELURAHAN' onchange='changeKelFilter(this);'><option value=''>Kelurahan</option>";
                                foreach ($aKelurahan as $row2) {
                                    if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                        echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
                                    }
                                }
                                echo "</select>";
                            }
                        }
                        echo "</div>";
                        ?>
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-12">
                        <button class="btn btn-primary btn-orange mb5" name="tambahData" type="submit" id="tambahData" value="Tambah Data">Tambah Data</button>
                        <button class="btn btn-primary btn-blue mb5" name="editData" type="submit" id="editData" value="Ubah">Ubah</button>
                        <button class="btn btn-primary bg-maka mb5" name="btHapus" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus">Hapus</button>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th class="tdheader"><button class="btn btn-primary btn-orange" onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck">Pilih Semua</button></th>
                                    <th class="tdheader">BLOK</th>
                                    <th class="tdheader">STATUS PETA</th>
                                    <th class="tdheader">PROSES</th>
                                </tr>
                                <?php
                                $propinsi = substr($kel, 0, 2);
                                $kabkota = substr($kel, 2, 2);
                                $kecamatan = substr($kel, 4, 3);
                                $kelurahan = substr($kel, 7, 3);
                                $blok = substr($id, 10, 3);

                                $sqlTampil = "SELECT CONCAT(CPM_KD_PROPINSI,CPM_KD_DATI2,CPM_KD_KECAMATAN,CPM_KD_KELURAHAN) AS CPM_KODE_LOKASI, CPM_KD_BLOK, CPM_STATUS_PETA_BLOK FROM cppmod_pbb_blok ";
                                if ($kel)
                                    $sqlTampil .= "WHERE CPM_KD_PROPINSI = '$propinsi' AND CPM_KD_DATI2 = '$kabkota' AND CPM_KD_KECAMATAN = '$kecamatan' AND CPM_KD_KELURAHAN = '$kelurahan'  ";

                                $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                                $n = 0;
                                $no = 0;
                                while ($r = mysqli_fetch_assoc($result)) {
                                    $no++;
                                    $class = $no % 2 == 0 ? "tdbody2" : "tdbody1";
                                ?>
                                    <tr>
                                        <td class="<?php echo $class; ?>"><input name="ID[]" type="checkbox" id="ID[<?php echo $n; ?>]" value="<?php echo $r['CPM_KODE_LOKASI'] . $r['CPM_KD_BLOK']; ?>" /></td>
                                        <td class="<?php echo $class; ?>" align='center'><?php echo $r['CPM_KD_BLOK']; ?></td>
                                        <td class="<?php echo $class; ?>" align='center'><?php echo $arrStatusPeta[$r['CPM_STATUS_PETA_BLOK']]; ?></td>
                                        <td class="<?php echo $class; ?>" align='center'>
                                            <a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&ID2=$r[CPM_KODE_LOKASI]$r[CPM_KD_BLOK]"); ?>">Ubah</a> |
                                            <a href="#" onclick="prosesDel('<?php echo substr($r['CPM_KODE_LOKASI'], 0, 2) . '.' . substr($r['CPM_KODE_LOKASI'], 2, 2) . '.' . substr($r['CPM_KODE_LOKASI'], 4, 3) . '.' . substr($r['CPM_KODE_LOKASI'], 7, 3) . '.' . $r['CPM_KD_BLOK']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&ID2=$r[CPM_KODE_LOKASI]$r[CPM_KD_BLOK]"); ?>')">Hapus</a>
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
                <button class="btn btn-primary bg-maka mb5" name="" type="button" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button>
                <div class="row">
                    <div class="col-md-12">
                        <label>Tambah Data</label><br />
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-md-4">
                                <select class="form-control" name="OP_KECAMATAN" id="OP_KECAMATAN" onchange="showKel(this)" style="float:left;">
                                    <option value="">Kecamatan</option>
                                    <?php
                                    foreach ($aKecamatan as $row)
                                        echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($OP_KECAMATAN) && $OP_KECAMATAN == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div id="sKel">
                                    <select class="form-control" name="OP_KELURAHAN" id="OP_KELURAHAN">
                                        <option value="">Kelurahan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tableAdd">
                                <tr>
                                    <td width="200">BLOK: </td>
                                    <td width="200">STATUS PETA : </td>
                                </tr>
                                <?php
                                for ($i = 0; $i < 5; $i++) {
                                ?>
                                    <tr>
                                        <td>
                                            <input class="form-control" name="lokasi[]" type="hidden" id="lokasi[]" class="lokasiall" value="<?php echo $kel ?>" maxlength="255" />
                                            <input class="form-control" name="blok[]" type="text" id="blok[]" maxlength="3" />
                                        </td>
                                        <td><input type="checkbox" name="status<?php echo $i; ?>" value=""> Ada</td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                        <div style="float: right">
                            <button class="btn btn-primary bg-maka" name="btTambah" type="submit" id="btTambah" value="Simpan">Simpan</button>
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
                                    <td width="200">BLOK: </td>
                                    <td width="200">STATUS PETA : </td>
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

                                    $propinsi = substr($id, 0, 2);
                                    $kabkota = substr($id, 2, 2);
                                    $kecamatan = substr($id, 4, 3);
                                    $kelurahan = substr($id, 7, 3);
                                    $blok = substr($id, 10, 3);
                                    $sqlTampil = "SELECT CONCAT(CPM_KD_PROPINSI,CPM_KD_DATI2,CPM_KD_KECAMATAN,CPM_KD_KELURAHAN) AS CPM_KODE_LOKASI, CPM_KD_BLOK, CPM_STATUS_PETA_BLOK FROM cppmod_pbb_blok where  CPM_KD_PROPINSI = '$propinsi' AND CPM_KD_DATI2 = '$kabkota' AND CPM_KD_KECAMATAN = '$kecamatan' AND CPM_KD_KELURAHAN = '$kelurahan' AND CPM_KD_BLOK = '$blok';";
                                    $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                                    $r = mysqli_fetch_assoc($result);
                                    $check = "";
                                    if ($r['CPM_STATUS_PETA_BLOK'] == '1') $check = "checked";
                                ?>
                                    <tr>
                                        <input name="lokasi[]" class="form-control" type="hidden" id="lokasi[]" value="<?php echo $r['CPM_KODE_LOKASI']; ?>" maxlength="255" />
                                        <td><input name="blok[]" class="form-control" type="text" id="blok[]" value="<?php echo $r['CPM_KD_BLOK']; ?>" maxlength="2" /></td>
                                        <td><input type="checkbox" name="status<?php echo $i; ?>" value="<?php echo $r['CPM_STATUS_PETA_BLOK']; ?>" <?php echo $check; ?>> Ada</td>

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
<?php } ?>