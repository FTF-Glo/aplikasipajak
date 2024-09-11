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

require_once("inc/PBB/dbFinalSppt.php");
require_once("inc/PBB/dbSpptTran.php");
require_once("inc/PBB/dbSppt.php");
require_once("inc/PBB/dbSpptExt.php");
require_once("inc/payment/uuid.php");

$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbSppt = new DbSppt($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);

//Processing POST Action
if (isset($_REQUEST['doAct'])) {
    if ($_REQUEST['newAct'] == "doNew") {
        header("Location: main.php?param=" . base64_encode("a=$application&m=$module&f=" . $arConfig['id_new_spop']));
    } else if ($_REQUEST['newAct'] == "doMerge") {
        if (spopIsCheck($_REQUEST['NOP2'], $errMsg) && spopIsCheck($_REQUEST['NOP1'], $errMsg)) {
            $bOK = spopDelete($_REQUEST['NOP2'], $errMsg);
            $bOK &= spopEdit($_REQUEST['NOP1'], $idt, $errMsg);
            if ($bOK)
                $bOK = spopDelete($_REQUEST['NOP1'], $errMsg);

            //buka halaman ke data proses
            if ($bOK) {
                $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_spop'] . "&idt=$idt";
                header("Location: main.php?param=" . base64_encode($params));
            }
        }
    } else if ($_REQUEST['newAct'] == "doSplit") {
        if (spopIsCheck($_REQUEST['NOP1'], $errMsg)) {
            /* Split versi lama : NOP induk di edit masuk ke pendataan SPOP*/
            /*$bOK = spopEdit($_REQUEST['NOP1'], $idt, $errMsg);
            if ($bOK)
                $bOK = spopDelete($_REQUEST['NOP1'], $errMsg);

            //buka halaman ke data proses
            if ($bOK) {
                $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_spop'] . "&idt=$idt";
                header("Location: main.php?param=" . base64_encode($params));
            }*/

            /* Split versi baru : membuat pendataan SPOP untuk NOP baru, jika luas tanah NOP induk habis dipecah maka NOP induk otomatis dihapus, jika tidak maka luas bumi NOP induk otomatis berkurang*/
            $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_spop'] . "&NOP_INDUK=" . $_REQUEST['NOP1'];
            header("Location: main.php?param=" . base64_encode($params));
        }
    } else if ($_REQUEST['newAct'] == "doEdit") {
        if (spopIsCheck($_REQUEST['NOP1'], $errMsg)) {
            $bOK = spopEdit($_REQUEST['NOP1'], $idt, $errMsg);

            if ($bOK)
                spopDelete($_REQUEST['NOP1'], $errMsg);
            //buka halaman ke data proses
            if ($bOK) {
                $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_spop'] . "&idt=" . $idt;
                // echo "ok";
                // echo "main.php?param=" . base64_encode($params) ;exit;
                try {
                    header("Location: main.php?param=" . base64_encode($params));
                } catch (Exception $err) {
                    echo $err;
                }
            }
        }
    } else if ($_REQUEST['newAct'] == "doDelete") {
        if (spopIsCheck($_REQUEST['NOP1'], $errMsg)) {
            if (moveFinalToHistory($_REQUEST['NOP1'], $errMsg)) {
                if (spopDelete($_REQUEST['NOP1'], $errMsg)) {
                    $cmdMsg = "SPPT dengan NOP " . $_REQUEST['NOP1'] . " telah dihapus";
                }
            }
        }
    }
}

function moveFinalToHistory($nop, &$errMsg)
{
    global $dbFinalSppt;

    //cari tahu iddoc dan vers
    $dokumen = $dbFinalSppt->get_where(array("CPM_NOP" => $nop));
    $iddoc   = $dokumen[0]['CPM_SPPT_DOC_ID'];
    $vers    = $dokumen[0]['CPM_SPPT_DOC_VERSION'];

    if ($dbFinalSppt->moveFinalToHistory($iddoc, $vers)) {
        return true;
    } else {
        $errMsg = "Maaf, Proses pemindahan data gagal";
        return false;
    }
}

function spopIsCheck($nop, &$errMsg)
{
    global $dbFinalSppt;

    if ($dbFinalSppt->isNopExist($nop)) {
        return true;
    } else {
        $errMsg = "Maaf, NOP $nop tidak ditemukan. Pastikan kembali NOP yang anda masukkan";
        return false;
    }
}

function spopEdit($nop, &$idt, &$errMsg)
{
    global $dbFinalSppt, $dbSpptTran, $dbSppt, $dbSpptExt, $appConfig, $uname;
    try {


        //cari tahu iddoc dan vers
        $dokumen = $dbFinalSppt->get_where(array("CPM_NOP" => $nop));
        $iddoc = $dokumen[0]['CPM_SPPT_DOC_ID'];
        $vers = $dokumen[0]['CPM_SPPT_DOC_VERSION'];
        $thn_penetapan = $dokumen[0]['CPM_SPPT_THN_PENETAPAN'];
        $bOK = false;

        //edit nu 35UTECH 03 01 2019
        $checkPenetapan = $appConfig['CHECK_SPPT_EDIT'];
        if ($checkPenetapan == "1") {
            $c1 = $thn_penetapan < $appConfig['tahun_tagihan'];
        } else {
            $c1 = true;
        }


        //hanya lakukan proses copy apabila data belum ditetapkan untuk tahun ini
        if ($c1) {
            //copy dari final ke proses
            $bOK = $dbFinalSppt->doResurect($iddoc, $vers);
            $idt = c_uuid();
            $tranValue['CPM_TRAN_REFNUM'] = c_uuid();
            $tranValue['CPM_TRAN_STATUS'] = "0";
            $tranValue['CPM_TRAN_SPPT_DOC_ID'] = $iddoc;
            $tranValue['CPM_SPPT_DOC_VERSION'] = $vers;
            $tranValue['CPM_TRAN_DATE'] = date("Y-m-d H:i:s");
            $tranValue['CPM_TRAN_OPR_KONSOL'] = $uname;
            // echo "masuk sini 1";
            $bOK = $dbSpptTran->add($idt, $tranValue);
            if (!$bOK) {
                //failed add transaction. Maybe ID transaction already exist. Second try
                $idt = c_uuid();
                $bOK = $dbSpptTran->add($idt, $tranValue);
                if (!$bOK) {
                    //something failed. Delete Sppt document
                    $dbSppt->del($iddoc);
                    // echo "masuk sini 1";
                    $dbSpptExt->del($iddoc);
                    $errMsg = "Data SPOP gagal dipersiapkan. Mohon ulangi.";
                }
            } else {
                $errMsg = "Data SPOP Gagal disimpan, silahkan ulangi kembali.";
            }
        } else {
            $errMsg = "Data SPPT telah ditetapkan untuk tahun tagihan " . $appConfig['tahun_tagihan'] . ". Dilarang melakukan pemrosesan SPPT untuk NOP tersebut.";
        }
        return $bOK;
    } catch (Exception $err) {
        echo $err;
    }
}

function spopDelete($nop, &$errMsg)
{
    global $dbFinalSppt, $dbSpptTran, $dbSppt, $dbSpptExt;

    //cari tahu iddoc dan vers
    $dokumen = $dbFinalSppt->get_where(array("CPM_NOP" => $nop));
    $iddoc = $dokumen[0]['CPM_SPPT_DOC_ID'];
    $vers = $dokumen[0]['CPM_SPPT_DOC_VERSION'];

    //hapus dari final
    $bOK = $dbFinalSppt->doPurge($iddoc, $vers);
    return $bOK;
}

// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
?>
<script type="text/javascript" src="inc/payment/base64.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>

<script type="text/javascript" src="inc/PBB/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="inc/PBB/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript">
    $(document).ready(function() {
        $("#btnDoAct").click(
            function() {
                if ($('input[name="newAct"]:checked').val() != 'doDelete') {
                    $("#formAct").submit();
                } else {
                    var conf = confirm("Anda yakin akan menghapus data ini?");
                    if (conf == true) {
                        $("#formAct").submit();
                    }
                }
            }
        );

        $("input:submit, input:button").button();
        /*               
         $("#inlinePopup").dialog({
            autoOpen: false,
            show: 'slide',
            resizable: false,
            position: 'center',
            stack: true,
            height: 'auto',
            width: 'auto',
            modal: true
        });
        */
        $("#srch-button").click(
            function() {
                var $dialog = $('<div></div>')
                    .load("function/PBB/search.php?q=" + encodeBase64("{'a':'<?php echo $a ?>', 'm':'<?php echo $m ?>', 'srch':'" + $("#search-sppt").val() + "'}"))
                    .dialog({
                        title: "Pencarian NOP/Nama",
                        autoOpen: false,
                        show: 'fade',
                        resizable: false,
                        position: 'center',
                        stack: true,
                        height: '500',
                        width: 'auto'
                    });
                $dialog.dialog('open');
            }
        );
        $("#content-blok").show().delay(7000).fadeOut(1000);
        $("input[name=newAct]").change(function() {
            var act = $(this).val();
            if (act == 'doNew') {
                $("#step2").html("<li>Silahkan melanjutkan dengan menekan tombol</li>");
            } else if (act == 'doMerge') {
                $("#step2").html("<li>Silahkan masukkan 2 NOP yang akan digabungkan</li>");
                $("#step2").append("NOP Induk <input type='text' name='NOP1'> NOP Anak <input type='text' name='NOP2'>");
            } else if (act == 'doSplit') {
                $("#step2").html("<li>Silahkan masukkan NOP induk yang akan dipisahkan</li>");
                $("#step2").append("NOP <input type='text' name='NOP1'>");
            } else if (act == 'doEdit') {
                $("#step2").html("<li>Silahkan masukkan NOP yang akan diubah</li>");
                $("#step2").append("NOP <input type='text' name='NOP1'>");
            } else if (act == 'doDelete') {
                $("#step2").html("<li>Silahkan masukkan NOP yang akan dihapus</li>");
                $("#step2").append("NOP <input type='text' name='NOP1'>");
            }
            $("#step3").show();
        });
    });
</script>

<?php
if (isset($cmdMsg))
    echo "<div id=\"content-blok\" style=\"display:none;padding: 5 .7em;\" class=\"ui-state-highlight ui-corner-all\">\n
		<span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>\n
		<strong>Info: </strong>\n
		$cmdMsg </div>";
?>

<?php
if (isset($errMsg))
    echo "<div id=\"content-blok\" style=\"display:none;padding: 5 .7em;\" class=\"ui-state-error ui-corner-all\">\n
		<span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span>\n
		<strong>Kesalahan: </strong>\n
		$errMsg </div>";
?>
<div class="col-md-12">

    <h4>Manajemen SPOP</h4>
    <input type="text" id="search-sppt"><input type="button" id="srch-button" value="Cari">
    <form method="post" id="formAct">
        <ol>
            <li>Pilih jenis manajemen yang ingin anda lakukan</li>
            <ul>
                <li>Buat Baru</li>
                <ul>
                    <li><label><input type="radio" name="newAct" value="doNew"> SPOP baru</label></li>
                    <li><label><input type="radio" name="newAct" value="doMerge"> Penggabungan SPOP (Merge)</label></li>
                    <li><label><input type="radio" name="newAct" value="doSplit"> Pemisahan SPOP (Split)</label></li>
                </ul>
                <li><label><input type="radio" name="newAct" value="doEdit"> Ubah</label></li>
                <li><label><input type="radio" name="newAct" value="doDelete"> Hapus</label></li>
            </ul>
            <br>
            <div id="step2"></div>
            <br>
            <li id="step3" style="display:none;">
                <input type="hidden" name="doAct">
                <input type="button" name="btnDoAct" id="btnDoAct" value="Lanjutkan"></li>
        </ol>
    </form>
</div>