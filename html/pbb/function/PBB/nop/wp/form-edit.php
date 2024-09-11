<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>

<script src="function/PBB/consol/jquery.validate.min.js"></script>

<!--
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>
-->
<?php
if (!isset($data)) {
    die("Forbidden direct access");
}

if (!$User) {
    die("Access not permitted");
}

$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);

if (!$bOK) {
    die("Function access not permitted");
}

require_once("inc/PBB/dbWajibPajak.php");
require_once("inc/payment/comm-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$appConfig = $User->GetAppConfig($application);
$dbWajibPajak = new DbWajibPajak($dbSpec);


if (isset($newForm1)) {

    $content = array();

    $contentWP['CPM_WP_PEKERJAAN'] = $WP_PEKERJAAN;
    $contentWP['CPM_WP_NAMA'] = strtoupper($WP_NAMA);
    $contentWP['CPM_WP_ALAMAT'] = strtoupper($WP_ALAMAT);
    $contentWP['CPM_WP_KELURAHAN'] = strtoupper($WP_KELURAHAN);
    $contentWP['CPM_WP_RT'] = strtoupper($WP_RT);
    $contentWP['CPM_WP_RW'] = strtoupper($WP_RW);
    $contentWP['CPM_WP_PROPINSI'] = strtoupper($WP_PROPINSI);
    $contentWP['CPM_WP_KOTAKAB'] = strtoupper($WP_KOTAKAB);
    $contentWP['CPM_WP_KECAMATAN'] = strtoupper($WP_KECAMATAN);
    $contentWP['CPM_WP_KODEPOS'] = strtoupper($WP_KODEPOS);
    $contentWP['CPM_WP_NO_HP'] = strtoupper($WP_NO_HP);

    $bOK = $dbWajibPajak->save($WP_ID, $contentWP);
    if (!$bOK) {
        echo "input data gagal";
        exit();
    }

    $bOK = $dbWajibPajak->saveToSPPT($WP_ID, $contentWP);

    if ($bOK) {
        header("Location: main.php?param=" . base64_encode("a=$a&m=$m&f=fWp"));
        exit();
    } else {
        echo "input data gagal";
        exit();
    }
}


$rows = $dbWajibPajak->get(array('CPM_WP_ID' => $_REQUEST['id']));
foreach ($rows[0] as $key => $value) {
    $tKey = substr($key, 4);
    $$tKey = $value;
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
        $("#form1").validate({
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
</script>
<style>
    #form1 input.error {
        border-color: #ff0000;
    }

    #form1 textarea.error {
        border-color: #ff0000;
    }
</style>
<link type="text/css" href="function/PBB/consol/newspop.css" rel="stylesheet">
<div id="main-content">
    <form method="post" name="form1" id="form1">
        <input type="hidden" id="uname" value="<?php echo $uname; ?>">
        <input type="hidden" id="WP_ID" name="WP_ID" value="<?php echo $WP_ID; ?>">
        <?php $param = base64_encode("{'id':'$idd', 'v':'$v'}"); ?>
        <h2>Edit Data Wajib Pajak</h2><br />
        <span id="spacer">Nomor KTP</span><input type="text" name="WP_ID" maxlength="30" disabled value="<?php echo  isset($WP_ID) ? $WP_ID : "" ?>" size=30>
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

        <span id="spacer">Nama Wajib Pajak</span><input type="text" name="WP_NAMA" id="WP_NAMA" maxlength="50" value="<?php echo  isset($WP_NAMA) ? str_replace($bSlash, $ktip, $WP_NAMA) : "" ?>" size=27>
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
        <input type="submit" name="newForm1" value="Simpan">
        <input type="button" value="Batal" onClick="if (confirm('PERINGATAN! Perubahan pada halaman ini belum disimpan! \nBatalkan?'))
            javascript:window.location = 'main.php?param=<?php echo  base64_encode("a=" . $a . "&m=" . $m) ?>';">
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function() {});
</script>