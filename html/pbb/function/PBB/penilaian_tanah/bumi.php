<?php
if (!isset($data)) {
    die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if (isset($arAreaConfig['terminalColumn'])) {
    $terminalColumn = $arAreaConfig['terminalColumn'];
    $accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
    if (!$accessible) {
        echo "Illegal access";
        return;
    }
}
?>
<script type="text/javascript">
    function DelAll() {
        var b = confirm("Apakah anda yakin menghapus dengan mode All?");
        if (b == false) {
            return false;
        } else {
            return true;
        }
    }

    function prosesDel(a, b, a_link) {
        var c = confirm("Anda akan menghapus kelas " + a + " tahun " + b + " ?");
        if (c == false) {
            return false;
        } else {
            window.open(a_link, "_parent");
            return true;
        }
    }

    function iniAngka(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if ((charCode >= 48 && charCode <= 57) || charCode == 46 || charCode == 8)
            return true;

        return false;
    }

    function hNJOP(ab, id) {
        if (id == 'nilaiBawah') {
            a1 = ab.length;
            an = ab.substring(11, a1 - 1);
        } else {
            a1 = ab.length;
            an = ab.substring(10, a1 - 1);
        }
        var a = document.getElementById("nilaiBawah[" + an + "]").value;
        var b = document.getElementById("nilaiAtas[" + an + "]").value;
        c = eval(b - a) / 2;
        c = eval(a) + c;
        document.getElementById("njop[" + an + "]").value = c;
    }
    var n = 6;
    var y = 0;
    var id = 5;

    function addRows() {
        //        if(y==0){
        //            v=document.getElementById("kelas[]").value; 
        //            v=parseInt(v,10)+5;
        //        }else if(y!=0){
        //            v++;
        //        }
        //        if(v<10){ v="00"+v; }else if(v<100){ v="0"+v; }
        var row = document.getElementById("tableAdd").insertRow(n);
        row.insertCell(0).innerHTML = "<input name='kelas[]' class=\"form-control\" type='text' id='kelas[]' value='' />";
        row.insertCell(1).innerHTML = "<input name='tahunawal[]' class=\"form-control\" type='text' id='tahunawal[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)'/>";
        row.insertCell(2).innerHTML = "<input name='tahunakhir[]' class=\"form-control\" type='text' id='tahunakhir[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)'/>";
        row.insertCell(3).innerHTML = "<input name='nilaiBawah[]' class=\"form-control\" type='text' id='nilaiBawah[" + id + "]' maxlength='9' onkeyup=hNJOP(this.id,'nilaiBawah')  onkeypress='return iniAngka(event)'/>";
        row.insertCell(4).innerHTML = "<input name='nilaiAtas[]' class=\"form-control\" type='text' id='nilaiAtas[" + id + "]' maxlength='9' onkeyup=hNJOP(this.id,'nilaiAtas') onkeypress='return iniAngka(event)'/>";
        row.insertCell(5).innerHTML = "<input name='njop[]' class=\"form-control\" type='text' id='njop[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)' />";
        n++;
        y++;
        id++;
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
</script>
<div class="col-md-12">
    <h3>SISTEM PENILAIAN BUMI <br /> (TABEL KELAS BUMI) </h3>
    <?php
    if (!empty($_REQUEST['btTambah'])) {
        $jArray = count($_REQUEST['kelas']);
        for ($i = 0; $i < $jArray; $i++) {
            $kelas = $_REQUEST['kelas'][$i];
            $tahunawal = $_REQUEST['tahunawal'][$i];
            $tahunakhir = $_REQUEST['tahunakhir'][$i];
            $nilaiBawah = $_REQUEST['nilaiBawah'][$i];
            $nilaiAtas = $_REQUEST['nilaiAtas'][$i];
            $njop = $_REQUEST['njop'][$i];
            if (!empty($tahunawal) and !empty($tahunakhir) && !empty($nilaiBawah) and !empty($nilaiAtas) and !empty($njop)) {
                $sqlTampil = "INSERT INTO cppmod_pbb_kelas_bumi (CPM_KELAS, CPM_THN_AWAL, CPM_THN_AKHIR, CPM_NILAI_BAWAH, CPM_NILAI_ATAS, CPM_NJOP_M2) VALUES ('$kelas', '$tahunawal', '$tahunakhir', '$nilaiBawah', '$nilaiAtas', '$njop');";
                $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
            }
        }
        if ($bOK) {
            echo "<b>" . ($bOK - 1) . " data ditambahkan !</b>";
        } else {
            echo mysqli_error($DBLink);
        }
    } elseif (!empty($_REQUEST['btEdit'])) {
        $jArray = count($_REQUEST['kelas']);
        for ($i = 0; $i < $jArray; $i++) {
            $kelas = $_REQUEST['kelas'][$i];
            $tahunawal = $_REQUEST['tahunawal'][$i];
            $tahunakhir = $_REQUEST['tahunakhir'][$i];
            $nilaiBawah = $_REQUEST['nilaiBawah'][$i];
            $nilaiAtas = $_REQUEST['nilaiAtas'][$i];
            $njop = $_REQUEST['njop'][$i];
            $sqlTampil = "UPDATE cppmod_pbb_kelas_bumi SET CPM_THN_AKHIR='$tahunakhir', CPM_NILAI_BAWAH='$nilaiBawah', CPM_NILAI_ATAS='$nilaiAtas', CPM_NJOP_M2='$njop' WHERE CPM_KELAS ='$kelas' AND CPM_THN_AWAL='$tahunawal' ;";
            $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
        }
        if ($bOK) {
            echo "<b>" . ($bOK - 1) . " data diubah!</b>";
        } else {
            echo mysqli_error($DBLink);
        }
    } elseif (!empty($_REQUEST['btHapus'])) {
        $jArray = count($_REQUEST['kelas']);
        if ($jArray == 0) {
            $jArray = 1;
        }
        for ($i = 0; $i < $jArray; $i++) {
            $id = $_REQUEST['kelas'][$i];
            if (empty($id)) {
                $kelas = $_REQUEST['kelas2'];
                $tahunawal = $_REQUEST['tahun2'];
            } else {
                $tmp = explode("#", $id);
                $kelas = $tmp[0];
                $tahunawal = $tmp[1];
            }
            if (!empty($kelas)) {
                $sqlTampil = "DELETE FROM cppmod_pbb_kelas_bumi WHERE CPM_KELAS ='$kelas' AND CPM_THN_AWAL='$tahunawal' ;";
                $bOK += $dbSpec->sqlQuery($sqlTampil, $result);
            }
        }
        if ($bOK) {
            echo "<b>" . ($bOK - 1) . " data dihapus!</b>";
        } else {
            echo mysqli_error($DBLink);
        }
    }
    //echo "<br>"; print_r($_REQUEST);
    if (empty($_REQUEST['tambahData']) and empty($_REQUEST['editData'])) {
    ?>
        <form id="form1" name="form1" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-primary btn-orange mb5" name="tambahData" type="submit" id="tambahData" value="Tambah Data">Tambah Data</button>
                    <button class="btn btn-primary btn-blue mb5" name="editData" type="submit" id="editData" value="Ubah">Ubah</button>
                    <button class="btn btn-primary bg-maka mb5" name="btHapus" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus">Hapus</button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th class="tdheader"><button class="btn btn-primary btn-orange" onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck">Pilih Semua</button></th>
                                    <th class="tdheader">NO</th>
                                    <th class="tdheader">KELAS</th>
                                    <th class="tdheader">TAHUN AWAL</th>
                                    <th class="tdheader">TAHUN AKHIR</th>
                                    <th class="tdheader">NILAI BAWAH</th>
                                    <th class="tdheader">NILAI ATAS</th>
                                    <th class="tdheader">NJOP per M2</th>
                                    <th class="tdheader">PROSES</th>
                                </tr>
                                <?php
                                $sqlTampil = "SELECT * FROM cppmod_pbb_kelas_bumi order by CPM_THN_AWAL desc,CPM_KELAS asc;";
                                $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                                $n = 0;
                                $no = 0;
                                while ($r = mysqli_fetch_assoc($result)) {
                                    $no++;
                                    $class = $no % 2 == 0 ? "tdbody2" : "tdbody1";
                                ?>
                                    <tr>
                                        <td class="<?php echo $class; ?>"><input name="kelas[]" type="checkbox" id="kelas[<?php echo $n; ?>]" value="<?php echo $r['CPM_KELAS'] . '#' . $r['CPM_THN_AWAL']; ?>" /></td>
                                        <td class="<?php echo $class; ?>"><?php echo $no; ?></td>
                                        <td class="<?php echo $class; ?>"><?php echo $r['CPM_KELAS']; ?></td>
                                        <td class="<?php echo $class; ?>"><?php echo $r['CPM_THN_AWAL']; ?></td>
                                        <td class="<?php echo $class; ?>"><?php echo $r['CPM_THN_AKHIR']; ?></td>
                                        <td class="<?php echo $class; ?>"><?php echo number_format($r['CPM_NILAI_BAWAH'], 2, ',', '.'); ?></td>
                                        <td class="<?php echo $class; ?>"><?php echo number_format($r['CPM_NILAI_ATAS'], 2, ',', '.'); ?></td>
                                        <td class="<?php echo $class; ?>"><?php echo number_format($r['CPM_NJOP_M2'], 2, ',', '.'); ?></td>
                                        <td class="<?php echo $class; ?>"><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&kelas2=$r[CPM_KELAS]&tahun2=$r[CPM_THN_AWAL]"); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KELAS']; ?>','<?php echo $r['CPM_THN_AWAL']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&kelas2=$r[CPM_KELAS]&tahun2=$r[CPM_THN_AWAL]"); ?>')">Hapus</a></td>
                                    </tr>
                                <?php
                                    $n++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    <?php } else if (!empty($_REQUEST['tambahData'])) { ?>
        <form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
            <?php
            $sqlTampil = "SELECT MAX(CPM_KELAS) as tKelas FROM cppmod_pbb_kelas_bumi;";
            $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
            $r = mysqli_fetch_assoc($result);
            $kelas = $r['tKelas'];
            ?>

            <button class="btn btn-primary bg-maka mb5" name="" type="button" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button>
            <div class="row">
                <div class="col-md-12">
                    <label>Tambah Data</label><br />
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tableAdd">
                            <tr>
                                <td width="144">Kelas : </td>
                                <td width="144">Tahun Awal : </td>
                                <td width="144">Tahun Akhir : </td>
                                <td width="144">Nilai Bawah : </td>
                                <td width="144">Nilai Atas : </td>
                                <td width="144">NJOP per M2 : </td>
                            </tr>
                            <?php
                            for ($i = 0; $i < 5; $i++) {
                                //                    $kelas++;
                                //                    if ($kelas < 10) {
                                //                        $kelas = "00" . $kelas;
                                //                    } else if ($kelas < 100) {
                                //                        $kelas = "0" . $kelas;
                                //                    }
                            ?>
                                <tr>
                                    <td><input class="form-control" name="kelas[]" type="text" id="kelas[]" value=""></td>
                                    <td><input class="form-control" name="tahunawal[]" type="text" id="tahunawal[<?php echo $i; ?>]" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                    <td><input class="form-control" name="tahunakhir[]" type="text" id="tahunakhir[<?php echo $i; ?>]" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                    <td><input class="form-control" name="nilaiBawah[]" type="text" id="nilaiBawah[<?php echo $i; ?>]" onkeyup="hNJOP(this.id,'nilaiBawah')" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                    <td><input class="form-control" name="nilaiAtas[]" type="text" id="nilaiAtas[<?php echo $i; ?>]" onkeyup="hNJOP(this.id,'nilaiAtas')" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                    <td><input class="form-control" name="njop[]" type="text" id="njop[<?php echo $i; ?>]" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <div style="float: right;">
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
                                <td width="144">Kelas : </td>
                                <td width="144">Tahun Awal : </td>
                                <td width="144">Tahun Akhir : </td>
                                <td width="144">Nilai Bawah : </td>
                                <td width="144">Nilai Atas : </td>
                                <td width="144">NJOP per M2 : </td>
                            </tr>
                            <?php
                            $jArray = count($_REQUEST['kelas']);
                            if ($jArray == 0) {
                                $jArray = 1;
                            }
                            for ($i = 0; $i < $jArray; $i++) {
                                $id = $_REQUEST['kelas'][$i];
                                if (empty($id)) {
                                    $kelas = $_REQUEST['kelas2'];
                                    $tahunawal = $_REQUEST['tahun2'];
                                } else {
                                    $tmp = explode("#", $id);
                                    $kelas = $tmp[0];
                                    $tahunawal = $tmp[1];
                                }

                                $sqlTampil = "SELECT * FROM cppmod_pbb_kelas_bumi where CPM_KELAS='$kelas' AND CPM_THN_AWAL='$tahunawal' ;";
                                $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
                                $r = mysqli_fetch_assoc($result);
                            ?>
                                <tr>
                                    <td width="104"><input class="form-control" name="kelas[]" type="text" id="kelas[]" value="<?php echo $r['CPM_KELAS']; ?>" readonly="true" /></td>
                                    <td width="90"><input class="form-control" name="tahunawal[]" type="text" id="tahunawal[<?php echo $i; ?>]" value="<?php echo $r['CPM_THN_AWAL']; ?>" readonly="true" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                    <td width="90"><input class="form-control" name="tahunakhir[]" type="text" id="tahunakhir[<?php echo $i; ?>]" value="<?php echo $r['CPM_THN_AKHIR']; ?>" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                    <td width="3"><input class="form-control" name="nilaiBawah[]" type="text" id="nilaiBawah[<?php echo $i; ?>]" onkeyup="hNJOP(this.id,'nilaiBawah')" value="<?php echo $r['CPM_NILAI_BAWAH']; ?>" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                    <td width="290"><input class="form-control" name="nilaiAtas[]" type="text" id="nilaiAtas[<?php echo $i; ?>]" onkeyup="hNJOP(this.id,'nilaiAtas')" value="<?php echo $r['CPM_NILAI_ATAS']; ?>" maxlength="9" onkeypress="return iniAngka(event)" /></td>
                                    <td width="290"><input class="form-control" name="njop[]" type="text" id="njop[<?php echo $i; ?>]" value="<?php echo $r['CPM_NJOP_M2']; ?>" maxlength="9" onkeypress="return iniAngka(event)" /></td>
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