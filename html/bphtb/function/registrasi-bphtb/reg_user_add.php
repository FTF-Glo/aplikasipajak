<?php
if ($data) {
    $uid = $data->uid;

    // get module
    $bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
    if (!$bOK) {
        return false;
    }

    /* ------------Setting each city/town for all--------------------- */
    $arConfig = $User->GetAreaConfig($area);
    $AreaPajak = $arConfig["AreaPajak"];
    /* ----------------------------------------------------------------- */


    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'registrasi-bphtb', '', dirname(__FILE__))) . '/';
    require_once($sRootPath . "inc/registrasi/inc-registrasi.php");
    require_once($sRootPath . "inc/payment/uuid.php");
//Simpan data form
    if (isset($simpan)) {
        $idUser = "$id";
        $idUser = htmlentities($idUser, ENT_QUOTES);
        $status = "0";
        $userId = mysqli_real_escape_string($DBLink, $userId);
        $pwd = mysqli_real_escape_string($DBLink, $pwd);
        $nm_lengkap = mysqli_real_escape_string($DBLink, $nm_lengkap);
        $keterangan = "aktif";
        $sqlSimpan = "INSERT INTO tbl_reg_user_bphtb SET uuid='$id',userId='$userId',password='$pwd',nm_lengkap='$nm_lengkap',nip='$nip',no_ktp='$no_ktp',kota='$kota',kecamatan='$kecamatan',kelurahan='$kelurahan',no_hp='$no_hp',jabatan='$jabatan',email='$email',status='$status',keterangan='$keterangan',areapajak='$AreaPajak' ";
        $username1 = "SELECT userId FROM tbl_reg_user_bphtb WHERE userId='$userId'";
        $username2 = "SELECT CTR_U_UID FROM central_user WHERE CTR_U_UID='$userId'";
        $check_1 = $dbSpec->sqlQuery($username1, $result);
        $check_2 = $dbSpec->sqlQuery($username2, $result);
        $check_for_username = mysqli_num_rows($result);
        $check_for_username2 = mysqli_num_rows($result);
        //echo $sqlSimpan;
        if (stristr($userId, "'")) {
            echo "<script>alert('Maaf, Nama ID teridentifikasi mengandung tanda kutip. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang valid!')</script>";
            $url64 = base64_encode("a=$a&m=$m");
            echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
            echo "<img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
        } else if ($check_for_username || $check_for_username2) {
            echo "<script>alert('Maaf, Nama ID teridentifikasi bahwa sudah terpakai. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang lain!')</script>";
            $url64 = base64_encode("a=$a&m=$m");
            echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
            echo "<img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
        } else {
            $bOK = $dbSpec->sqlQuery($sqlSimpan, $result);
            if ($bOK) {
                $bOK = $Setting->InsertUser($idUser, $userId, $pwd, 0, 0, 0, $arConfig["userTheme"]);
                if ($bOK) {
                    echo "Berhasil disimpan..";
                    //require_once($sRootPath . "view/Registrasi/notifikasi_email_bphtb.php");
                    if ($_POST['jabatan'] == "staff dispenda") {
                        $arConfig = $User->GetAreaConfig($area);
                        $bphtbApp = $arConfig["bphtbApp"];
                        $bphtbStaffDispen = $arConfig["bphtbStaffDispen"];
                        $Setting->ChangeRole($idUser, $bphtbApp, $bphtbStaffDispen);
                    } else if ($_POST['jabatan'] == "pejabat dispenda") {
                        $arConfig = $User->GetAreaConfig($area);
                        $bphtbApp = $arConfig["bphtbApp"];
                        $bphtbPjbtDispen = $arConfig["bphtbPjbtDispen"];
                        $Setting->ChangeRole($idUser, $bphtbApp, $bphtbPjbtDispen);
                    } else if ($_POST['jabatan'] == "pegawai bpn") {
                        $arConfig = $User->GetAreaConfig($area);
                        $bphtbApp = $arConfig["bphtbApp"];
                        $bphtbPegBpn = $arConfig["bphtbPegBpn"];
                        $Setting->ChangeRole($idUser, $bphtbApp, $bphtbPegBpn);
                    }
                } else {
                    echo "Gagal disimpan..";
                }
            } else {
                echo "Gagal disimpan..";
            }
            $url64 = base64_encode("a=$a&m=$m");
            echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
            echo " <img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
        }
    }
//echo "<pre>";
//print_r($_REQUEST);
//echo "</pre>";
    ?>
    <script language="JavaScript">
        function cekform() {
            if (document.formReg.userId.value == "")
            {
                alert("Mohon perikasa Nama ID");
                return false;
            }
            else if (document.formReg.pwd.value == "")
            {
                alert("Mohon periksa Password");
                return false;
            }
            else if (document.formReg.nm_lengkap.value == "")
            {
                alert("Mohon periksa Nama Lengkap");
                return false;
            }
            else if (document.formReg.nip.value == "")
            {
                alert("Mohon periksa NIP");
                return false;
            }
            else if (document.formReg.no_ktp.value == "")
            {
                alert("Mohon periksa NO.KTP");
                return false;
            }
            else if (document.formReg.kota.value == "pilih")
            {
                alert("Mohon periksa Kota");
                return false;
            }
            else if (document.formReg.kecamatan.value == "pilih")
            {
                alert("Mohon periksa Kecamatan");
                return false;
            }
            else if (document.formReg.kelurahan.value == "pilih")
            {
                alert("Mohon periksa Kelurahan");
                return false;
            }
            else if (document.formReg.jabatan.value == "")
            {
                alert("Mohon periksa jabatan");
                return false;
            }
            else if (document.formReg.email.value == "")
            {
                alert("Mohon periksa email");
                return false;
            }
            else
            {
                return true;
            }
        }
    </script>
    <form method="POST" action="main.php?param=<?php echo  base64_encode("a=$a&m=$m&f=$f") ?>" name="formReg" id="formReg" onSubmit="return cekform();">
    <div class='content-wrapper' style='padding-top:0px;padding-bottom:0px'>
        <table border="0" cellspacing="1" cellpadding="1">
            <tr>
                <th colspan="2" align="left">Form Data Pengguna</th>
            </tr>
            <tr>
                <td>Nama ID</td>
                <td><input type="text" name="userId" id="userId">&nbsp;&nbsp;<input type="button" id="tombol" value="cek ketersediaan" onclick="cek_id();
                        return false;">&nbsp;<div id="loading"></div>
                    <!-- <script type="text/javascript" src="jquery-1.4.2.min.js"></script> -->
                    <script language=\"javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\" type=\"text/javascript\"></script>

                    <script type="text/javascript">
                    function cek_id() {
                        $("#loading").html('<img src=image/icon/loadinfo.net.gif></img><font size=1>memeriksa..</font>');
                        $.post('view/Registrasi/cek_id_bphtb.php', $("#userId").serialize()+"&app=<?php echo $a?>&mod=<?php echo $m?>", function (hasil) {
                            $("#loading").html(hasil);
                        });
                    }
                    </script>
                </td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="pwd"></td>
            </tr>
            <tr>
                <td>Nama Lengkap</td>
                <td><input type="text" name="nm_lengkap" size="40"></td>
            </tr>
            <tr>
                <td>NIP</td>
                <td><input type="text" name="nip" size="25"></td>
            </tr>
            <tr>
                <td>No.KTP</td>
                <td><input type="text" name="no_ktp" size="25"></td>
            </tr>
            <tr>
                <td>Kelurahan</td>
                <td><input type="text" name="kelurahan" id="kelurahan"size="25"></td>
            </tr>
            <tr>
                <td>Kecamatan</td>
                <td><input type="text" name="kecamatan" id="kecamatan"size="25"></td>
            </tr>
            <tr>
                <td>Kota/Kabupaten</td>
                <td><input type="text" name="kota" id="kota"size="25"></td>
            </tr>
            <tr>
                <td valign="top">Bekerja sebagai</td>
                <td>
                    <div>
                        <input type="radio" name="jabatan" value="staff dispenda">Staff DISPENDA&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="jabatan" value="pejabat dispenda">Pejabat DISPENDA<br>
                        <input type="radio" name="jabatan" value="pegawai bpn">Pegawai BPN&nbsp;&nbsp;&nbsp;&nbsp;<font size="1" color="#FF0000">*harap dipilih salah satu</font>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2"><b>Dibawah ini salah satunya harus diisi</b></td>
            </tr>
            <tr>
                <td>No. Handphone</td>
                <td><input type="text" name="no_hp"></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="text" name="email">&nbsp;&nbsp;<font size="1" color="#FF0000">*utamakan email diisi</font></td>
            </tr>
            <tr>
                <td colspan="2"><input type="hidden" name="id" value="<?php echo c_uuid(); ?>"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" value="Simpan" name="simpan">&nbsp;&nbsp;<input type="reset" value="Batalkan"></td>
            </tr>
        </table>
        </div>
    </form>


    <?php
}
?>
