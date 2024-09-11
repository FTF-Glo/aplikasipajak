<?php
error_reporting(1);
ini_set('display_errors', E_ALL);

require_once("../../../../inc/central/dbspec-central.php");
require_once("../../../../inc/PBB/dbWajibPajak.php");
require_once("../../../../inc/payment/comm-central.php");
require_once("../../../../inc/payment/inc-payment-db-c.php");
require_once("../../../../inc/payment/db-payment.php");
require_once("../../../../inc/central/user-central.php");
require_once("../../../../inc/payment/json.php");

if (!isset($_REQUEST['id']) || !isset($_REQUEST['a'])) exit('direct access not allowed!');

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($_REQUEST['a']);
$dbWajibPajak = new DbWajibPajak($dbSpec);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $case = @isset($_POST['case']);

    $content = array();
    foreach ($_POST as $key => $val) {
        $$key = mysqli_escape_string($DBLink, $val);
    }
    $contentWP['CPM_WP_PEKERJAAN'] = $WP_PEKERJAAN;
    // $contentWP['CPM_WP_NAMA'] = strtoupper($WP_NAMA);
    $contentWP['CPM_WP_NAMA'] = strtoupper(preg_replace('/[^a-zA-Z0-9_ %\,\/\'\[\]\.\(\)%&-]/s', '', $WP_NAMA));

    $contentWP['CPM_WP_ALAMAT'] = strtoupper($WP_ALAMAT);
    $contentWP['CPM_WP_KELURAHAN'] = strtoupper($WP_KELURAHAN);
    $contentWP['CPM_WP_RT'] = strtoupper($WP_RT);
    $contentWP['CPM_WP_RW'] = strtoupper($WP_RW);
    $contentWP['CPM_WP_PROPINSI'] = strtoupper($WP_PROPINSI);
    $contentWP['CPM_WP_KOTAKAB'] = strtoupper($WP_KOTAKAB);
    $contentWP['CPM_WP_KECAMATAN'] = strtoupper($WP_KECAMATAN);
    $contentWP['CPM_WP_KODEPOS'] = strtoupper($WP_KODEPOS);
    $contentWP['CPM_WP_NO_HP'] = strtoupper($WP_NO_HP);


    $res = array('msg' => '', 'res' => 0);
    if ($bOK = $dbWajibPajak->save($WP_ID, $contentWP)) {
        //Edited By ZNK 20171013
        //Jika dari loket tabel Final, Susulan, Pendataan tidak diupdate
        if ($case != 'from_loket') {
            $bOK = $dbWajibPajak->saveToSPPT($WP_ID, $contentWP);
        } else {
            $bOK = true;
        }
        // END
        if ($bOK) {
            $res['msg'] = "Input data berhasil";
            $res['res'] = 1;
        } else {
            $res['msg'] = "Update data SPPT gagal";
        }
    } else {
        $res['msg'] = "Input data WP gagal";
    }
    exit($json->encode($res));
}
$WP_ID = strip_tags($_REQUEST['id']);
$dataWP = $dbWajibPajak->get(array('CPM_WP_ID' => $WP_ID), 1, 1);

foreach ($dataWP[0] as $key => $val) {
    $key = str_replace("CPM_", "", $key);
    $$key = mysqli_escape_string($DBLink, $val);
}

?>
<script type="text/javascript">
    function iniAngka(evt, x) {
        var charCode = (evt.which) ? evt.which : event.keyCode;

        if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
            return true;
        } else {
            alert("Input hanya boleh angka!");
            return false;
        }
    }

    function iniAngkaDenganKoma(evt, x) {
        var charCode = (evt.which) ? evt.which : event.keyCode;

        if ((charCode >= 48 && charCode <= 57) || charCode == 46 || charCode == 8 || charCode == 13) {
            return true;
        } else {
            alert("Input hanya boleh angka dan titik!");
            return false;
        }
    }

    function checkVal(el, dest) {
        if (el.value != "") {
            el.value = document.getElementById(dest).value;
        }
    }

    $(document).ready(function() {
        $("input:submit, input:button").button();
        $("#formWpDialog").validate({
            rules: {
                WP_NAMA: "required",
                WP_ALAMAT: "required",
                WP_RT: "required",
                WP_RW: "required",
                WP_PROV: "required",
                WP_KOTAKAB: "required",
                WP_KECAMATAN: "required",
                WP_KELURAHAN: "required",
                WP_KODEPOS: "required",
                WP_NO_KTP: "required",
                WP_NO_HP: "required"
            },
            messages: {
                WP_NAMA: "Wajib diisi",
                WP_ALAMAT: "Wajib diisi",
                WP_RT: "Wajib diisi",
                WP_RW: "Wajib diisi",
                WP_PROV: "Wajib diisi",
                WP_KOTAKAB: "Wajib diisi",
                WP_KECAMATAN: "Wajib diisi",
                WP_KELURAHAN: "Wajib diisi",
                WP_KODEPOS: "Wajib diisi",
                WP_NO_KTP: "Wajib diisi",
                WP_NO_HP: "Wajib diisi"
            }
        });
    });

    function save(btn) {

        if (confirm('Apakah anda yakin data WP yang diisi sudah benar?') === false) return false;
        var postData = $('#formWpDialog').serialize();

        $('#div-loadwp-dialog-save-wait').html("<img src=\"image/icon/loadinfo.net.gif\"/>");
        $(btn).attr('disabled', 'disabled');
        $.ajax({
            type: 'post',
            url: 'function/PBB/nop/wp/form-edit-dialog.php',
            data: 'a=<?php echo $_REQUEST['a'] ?>&id=<?php echo $_REQUEST['id'] ?>&' + postData,
            dataType: 'json',
            success: function(r) {
                alert(r.msg)
                if (r.res == 1) {
                    if ($('#noktp').size() > 0) $('#noktp').blur();
                    if ($('#noKtpWp').size() > 0) $('#noKtpWp').blur();
                    if ($('#WP_NO_KTP').size() > 0) $('#WP_NO_KTP').blur();
                    $('#modalDialog').dialog('close');
                }
                $('#div-loadwp-dialog-save-wait').html('');
                $(btn).removeAttr('disabled');
            }
        });
    }
</script>
<style>
    #formWpDialog input.error {
        border-color: #ff0000;
    }

    #formWpDialog textarea.error {
        border-color: #ff0000;
    }
</style>
<link type="text/css" href="function/PBB/consol/newspop.css" rel="stylesheet">
<div id="main-content">
    <form method="post" name="formWpDialog" id="formWpDialog">
        <input type="hidden" id="uname" value="<?php echo $uname; ?>">
        <input type="hidden" id="WP_ID" name="WP_ID" value="<?php echo $WP_ID; ?>">
        <?php $param = base64_encode("{'id':'$idd', 'v':'$v'}"); ?>
        <h2>Edit Data Wajib Pajak</h2><br />
        <span id="spacer">Nomor KTP</span><input type="text" name="WP_ID" maxlength="30" disabled value="<?php echo  isset($WP_ID) ? $WP_ID : "" ?>" size=30> <span id="div-loadwp-dialog-save-wait"></span>
        <div id="newl"></div>
        <span id="spacer">Pekerjaan</span><label><input type="radio" name="WP_PEKERJAAN" value="PNS" <?php echo ($WP_PEKERJAAN == "PNS") ? "checked" : "" ?> checked> PNS *)</label>
        <div id="newl"></div>
        <span id="spacer">&nbsp;</span><label><input type="radio" name="WP_PEKERJAAN" value="TNI" <?php echo ($WP_PEKERJAAN == "TNI") ? "checked" : "" ?>> TNI *)</label>
        <div id="newl"></div>
        <span id="spacer">&nbsp;</span><label><input type="radio" name="WP_PEKERJAAN" value="Pensiunan" <?php echo ($WP_PEKERJAAN == "Pensiunan") ? "checked" : "" ?>> Pensiunan *)</label>
        <div id="newl"></div>
        <span id="spacer">&nbsp;</span><label><input type="radio" name="WP_PEKERJAAN" value="Badan" <?php echo ($WP_PEKERJAAN == "Badan") ? "checked" : "" ?>> Badan</label>
        <div id="newl"></div>
        <span id="spacer">&nbsp;</span><label><input type="radio" name="WP_PEKERJAAN" value="Lainnya" <?php echo ($WP_PEKERJAAN == "Lainnya") ? "checked" : "" ?>> Lainnya</label>
        <div id="newl"></div>
        <span id="spacer">&nbsp;</span><label>*)Yang penghasilannya semata-mata berasal dari gaji atau uang pensiunan</label>
        <div id="newl"></div>

        <!-- <span id="spacer">Nama Wajib Pajak</span><input type="text" name="WP_NAMA" id="WP_NAMA" maxlength="50" value="<?php echo  isset($WP_NAMA) ? str_replace($bSlash, $ktip, $WP_NAMA) : "" ?>" size=27><div id="newl"></div> -->
        <span id="spacer">Nama Wajib Pajak</span><input type="text" name="WP_NAMA" id="WP_NAMA" maxlength="50" value="<?php echo  isset($WP_NAMA) ? preg_replace('/[^a-zA-Z0-9_ %\,\/\'\[\]\.\(\)%&-]/s', '', $WP_NAMA) : "" ?>" size=27>
        <div id="newl"></div>

        <span id="spacer">Nama Jalan</span><input type="text" name="WP_ALAMAT" id="WP_ALAMAT" maxlength="70" id="WP_ALAMAT" value="<?php echo  isset($WP_ALAMAT) ? str_replace($bSlash, $ktip, $WP_ALAMAT) : "" ?>" size=27>
        <div id="newl"></div>
        <span id="spacer">RT</span><input type="text" name="WP_RT" id="WP_RT" maxlength="3" value="<?php echo  isset($WP_RT) ? $WP_RT : "" ?>" size=6 onkeypress="return iniAngka(event, this)">
        <div id="newl"></div>
        <span id="spacer">RW</span><input type="text" name="WP_RW" id="WP_RW" maxlength="3" value="<?php echo  isset($WP_RW) ? $WP_RW : "" ?>" size=6 onkeypress="return iniAngka(event, this)">

        <div id="newl"></div>
        <span id="spacer">Provinsi</span>
        <input type="text" name="WP_PROPINSI" size="27" maxlength="25" id="WP_PROPINSI" value="<?php echo  isset($WP_PROPINSI) ? $WP_PROPINSI : "" ?>">
        <div id="newl"></div>
        <span id="spacer">Kab/kodya</span>
        <div id="sKota">
            <input type="text" name="WP_KOTAKAB" id="WP_KOTAKAB" size="27" maxlength="25" id="WP_KOTAKAB" value="<?php echo  isset($WP_KOTAKAB) ? $WP_KOTAKAB : "" ?>">
        </div><span id="div-sKota-wait"></span>

        <div id="newl"></div>
        <span id="spacer">Kecamatan</span>
        <div id="sKec">
            <input type="text" name="WP_KECAMATAN" id="WP_KECAMATAN" size="27" maxlength="25" id="WP_KECAMATAN" value="<?php echo  isset($WP_KECAMATAN) ? $WP_KECAMATAN : "" ?>">
        </div><span id="div-sKec-wait"></span>

        <div id="newl"></div>
        <span id="spacer"><?php echo $appConfig['LABEL_KELURAHAN']; ?></span>
        <div id="sKel2">
            <input type="text" name="WP_KELURAHAN" id="WP_KELURAHAN" size="27" maxlength="25" id="WP_KELURAHAN" value="<?php echo  isset($WP_KELURAHAN) ? $WP_KELURAHAN : "" ?>">
        </div><span id="div-sKel2-wait"></span>

        <div id="newl"></div>
        <span id="spacer">Kode Pos</span><input type="text" name="WP_KODEPOS" id="WP_KODEPOS" value="<?php echo  isset($WP_KODEPOS) ? $WP_KODEPOS : "" ?>" maxlength="5" size=27 onkeypress="return iniAngka(event, this)">
        <div id="newl"></div>
        <span id="spacer">Nomor HP</span><input type="text" name="WP_NO_HP" id="WP_NO_HP" value="<?php echo  isset($WP_NO_HP) ? $WP_NO_HP : "" ?>" maxlength="15" size=27 onkeypress="return iniAngka(event, this)">
        <div id="newl"></div>
        <br>
        <input type="button" onclick="javascript:save(this)" name="newformWpDialog" value="Simpan">
        <input type="button" value="Batal" onClick="if (confirm('PERINGATAN! Perubahan pada halaman ini belum disimpan! \nBatalkan?'))
            javascript:window.location = 'main.php?param=<?php echo  base64_encode("a=" . $a . "&m=" . $m) ?>';">
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function() {});
</script>