<?php

/**
 * adalah Function untuk menangani Proses monitoring sukabumi
 * dengan cara input data kecamata dan kelurahan. Dilengkapi juga 
 * dengan fungsi pencarian agar memudahkan Proses monitoring
 * @package Function
 * @subpackage monitoring
 * @author Bayu kusumah Rahman <bayu.kusumah@vsi.co.id>
 * @copyright (c) 2013, PT VallueStream International
 * @link http://www.vsi.co.id
 * 
 */
if (!isset($data)) {
    return;
}

$thn = '';
$awal = '';
/* $nop='';
  $ctnt3='';
  $first='';
  $dte='';
  $ctnt='';
  $ctnt1='';
  $kec2='';
  $ctnt11='';
  $dte2='';
  $hasil2= '';
  $ctnt21='';
  $tmpData=array();
  $kec = '';
  $maxData='';
  $hsl= array();
  $id_kel='';
  $tmp1='';
  $tmp2='';
  $jumPage='';
  $id_kec='';
  $idKel='';
  $tabs=''; */
session_start();
$_SESSION['kec'] = '0';
$_SESSION['kel'] = '0';
$_SESSION['awal'] = '';
$_SESSION['kec2'] = '0';
$_SESSION['kel2'] = '0';
$_SESSION['thnawal2'] = '';
$_SESSION['tanggal1'] = '';
$_SESSION['tanggal2'] = '';
$_SESSION['tanggal3'] = '';
$_SESSION['tanggal4'] = '';
$_SESSION['cari'] = '';
$_SESSION['Nama_kec1'] = '';
$_SESSION['Nam_kel1'] = '';
$_SESSION['Nama_kec2'] = '';
$_SESSION['Nam_kel2'] = '';
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Monitoring', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
$link = 'main.php?param=' . base64_encode("a=$a&m=$m&f=$f&tabs=1");
$link2 = 'main.php?param=' . base64_encode("a=$a&m=$m&f=$f&tabs=0");
$link4 = 'main.php?param=' . base64_encode("a=$a&m=$m&f=$f");
$link3 = $link2;
$link2 .= '#tabs-2';
if (isset($_POST['kirim'])) {
    $_SESSION['ip'] = $_POST['ip'];
    $_SESSION['port'] = $_POST['port'];
}
define('HOST', $_SESSION['ip']);
define('PORT', $_SESSION['port']);
define('URL', 'function/Monitoring/download.php');
define('URL2', 'function/Monitoring/download2.php');
define('JUM_DATA', 15);
define('INDEX', 0);
define('TIME', 120);

$kec1 = "0";
$kel1 = "0";
$id = "";
$cek = "";
$thnawal = "";
$src = "2";
$maxData = "0";
$bayar = "";

if (isset($_GET['i'])) {
    $nop = $_GET['i'];
    $sementara = base64_decode($nop);
    $kec1 = substr($sementara, 0, 3);
    $kel1 = substr($sementara, -3, 3);
}

/** #@+
 * Pengambilan data variabel saat form submit
 * data yang diambil kecamatan, kelurahan dan pencarian jika ada
 *  @var string 
 */
if (isset($_POST['Show']) or isset($_POST['Show2'])) {
    if (isset($_POST['kec2'])) {
        $kec1 = $_POST['kec2'];
        $_SESSION['kec2'] = $kec1;
    }
    if (isset($_POST['kel2'])) {
        $kel1 = $_POST['kel2'];
        $_SESSION['kel2'] = $kel1;
    }
    if (isset($_POST['kec'])) {
        $kec1 = $_POST['kec'];
        $_SESSION['kec'] = $kec1;
    }
    if (isset($_POST['thnawal2'])) {
        $thnawal = $_POST['thnawal2'];
        $_SESSION['thnawal2'] = $thnawal;
    } else {
        $_SESSION['awal'] = date('Y');
    }
    if (isset($_POST['kel'])) {
        $kel1 = $_POST['kel'];
        $_SESSION['kel'] = $kel1;
    }
    if (isset($_POST['thnawal'])) {
        $thnawal = $_POST['thnawal'];
        $_SESSION['awal'] = $thnawal;
    }
    if (isset($_POST['tanggal1'])) {
        $tanggal1 = $_POST['tanggal1'];
        $_SESSION['tanggal1'] = $tanggal1;
    }
    if (isset($_POST['tanggal2'])) {
        $tanggal2 = $_POST['tanggal2'];
        $_SESSION['tanggal2'] = $tanggal2;
    }
    if (isset($_POST['tanggal3'])) {
        $tanggal3 = $_POST['tanggal3'];
        $_SESSION['tanggal3'] = $tanggal3;
    }
    if (isset($_POST['tanggal4'])) {
        $tanggal4 = $_POST['tanggal4'];
        $_SESSION['tanggal4'] = $tanggal4;
    }
    if (isset($_POST['src'])) {
        $src = $_POST['src'];
        $_SESSION['cari'] = $src;
    }

    if ($kec1 != 0 or $kel1 != 0) {
        if ($kel1 == 0) {
            $kel1 = "0";
            $_SESSION['kel'] = "0";
        }
        $id = base64_encode($kec1 . $kel1);
    }
    $cek = $kec1 . $kel1;

    if ($tabs) {
        if ($src == "") {
            $src = 1;
        }
    } else {
        if ($src == "") {
            $src = 0;
        }
    }
}

/** #@+
 * Query untuk menampilkan data kecamatan dan kelurahan
 *  @var string 
 */
$kosong['empty'] = '';
$hasil = "";

$host = HOST;
$port = PORT;
$timeOut = TIME;
$tmp['f'] = 'pbbv21.selectkecamatan';
$tmp['i'] = $kosong;
$tmp['PAN'] = '11000';


$sRequestStream = $json->encode($tmp);
$bOK = GetRemoteResponse($host, $port, $timeOut, $sRequestStream, $sResp);

$ts = $json->decode($sResp);
$Kecamatan = $json->decode($ts->o);
foreach ($Kecamatan as $key => $value) {

    $Idkec = $value->KD_KECAMATAN;
    $IdKec[$Idkec] = $value->NM_KECAMATAN;
    if ($tabs) {
        if ($kec1 == $Idkec) {
            $_SESSION['Nama_kec1'] = $value->NM_KECAMATAN;
            $hasil .= "<option selected='selected' value=" . $Idkec . ">" . $value->NM_KECAMATAN . "</option>";
        } else {
            $hasil .= "<option value=" . $Idkec . ">" . $value->NM_KECAMATAN . "</option>";
        }
    } else {
        $hasil .= "<option value=" . $Idkec . ">" . $value->NM_KECAMATAN . "</option>";
    }
    if (!$tabs) {

        if ($kec1 == $Idkec) {
            $_SESSION['Nama_kec2'] = $value->NM_KECAMATAN;
            $hasil2 .= "<option selected='selected' value=" . $Idkec . ">" . $value->NM_KECAMATAN . "</option>";
        } else {
            $hasil2 .= "<option value=" . $Idkec . ">" . $value->NM_KECAMATAN . "</option>";
        }
    } else {
        $hasil2 .= "<option value=" . $Idkec . ">" . $value->NM_KECAMATAN . "</option>";
    }
}
$tmp['f'] = 'pbbv21.selectkelurahan';
$sRequestStream = $json->encode($tmp);
$bOK = GetRemoteResponse($host, $port, $timeOut, $sRequestStream, $sResp);
$ts = $json->decode($sResp);
$Kelurahan = $json->decode($ts->o);
foreach ($Kelurahan as $key => $value) {
    $Idkel = $value->KD_KECAMATAN;
    $tmp1[$Idkel][] = $value->NM_KELURAHAN;
    $IdKel[$Idkel][$value->KD_KELURAHAN] = $value->NM_KELURAHAN;
    $tmp2[$Idkel][] = $value->KD_KELURAHAN;
}

$year = date('Y');
$i = 1;
$ThnAwal = '';
$ThnAkhir = '';
$ThnAwal2 = '';
$ThnAkhir2 = '';
while ($i <= 17) {
    $tmp3 = (date('Y') + 1) - $i;
    if (isset($_POST['thnawal'])) {
        if ($_POST['thnawal'] == $tmp3) {
            $ThnAwal .= '<option selected="selected" value="' . $tmp3 . '"> ' . $tmp3 . ' </option>';
        } else {
            $ThnAwal .= '<option value="' . $tmp3 . '"> ' . $tmp3 . ' </option>';
        }
    } else {

        if ($awal == $tmp3) {
            $ThnAwal .= '<option selected="selected" value="' . $tmp3 . '"> ' . $tmp3 . ' </option>';
        } else {
            $ThnAwal .= '<option value="' . $tmp3 . '"> ' . $tmp3 . ' </option>';
        }
    }

    if (isset($_POST['thnawal2'])) {
        if ($_POST['thnawal2'] == $tmp3) {
            $ThnAwal2 .= '<option selected="selected" value="' . $tmp3 . '"> ' . $tmp3 . ' </option>';
        } else {
            $ThnAwal2 .= '<option value="' . $tmp3 . '"> ' . $tmp3 . ' </option>';
        }
    } else {
        if ($awal == $tmp3) {
            $ThnAwal2 .= '<option selected="selected" value="' . $tmp3 . '"> ' . $tmp3 . ' </option>';
        } else {
            $ThnAwal2 .= '<option value="' . $tmp3 . '"> ' . $tmp3 . ' </option>';
        }
    }

    $i++;
}


if (isset($_GET['s'])) {
    $src = trim($_GET['s']);
}

if ($nop != NULL) {
    $id = $nop;
}
if ($awal != NULL) {
    $thnawal = $awal;
}
if ($kel1 == 0 and $id_kec != NULL) {
    $kec1 = $id_kec;
    $kel1 = $id_kel;
}

foreach ($IdKel[$kec1] as $key => $value) {
    if ($tabs) {
        if ($kel1 == $key) {
            $_SESSION['Nam_kel1'] = $value;

            $kec .= "<option selected='selected' value='" . $key . "'>" . $value . "</option>";
        } else {
            $kec .= "<option value='" . $key . "'>" . $value . "</option>";
        }
    }
    if (!$tabs) {
        if ($kel1 == $key) {
            $_SESSION['Nam_kel2'] = $value;
            $kec2 .= "<option selected='selected' value='" . $key . "'>" . $value . "</option>";
        } else {
            $kec2 .= "<option value='" . $key . "'>" . $value . "</option>";
        }
    }
}
?>
<link href="view/Monitoring/monitoring.css?v0002" rel="stylesheet" type="text/css" />

<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jtable/themes/lightcolor/gray/jtable.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min2.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min2.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button2.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jtable/jquery.jtable.js"></script>
<!--<script src="view/Monitoring/simple-excel.js"></script>-->
<script type="text/javascript">
    $(document).ready(function() {

        $("#tanggal1").datepicker();
        $("#tanggal2").datepicker();
        $("#tanggal1").datepicker("option", "dateFormat", 'yy/mm/dd');
        $("#tanggal2").datepicker("option", "dateFormat", 'yy/mm/dd');
        $("#tanggal3").datepicker();
        $("#tanggal4").datepicker();
        $("#tanggal3").datepicker("option", "dateFormat", 'yy/mm/dd');
        $("#tanggal4").datepicker("option", "dateFormat", 'yy/$mm/dd');
        $("#src").focus(function() {
            $("#src").val('');
        });
        $("#tabs").tabs();
        $('#tabs').tabs({
            select: function(event, ui) {
                $(ui.tab);
            }
        });
    });
    /** #@+
     * function ini berfungsi mengambil data kelurahan setalah 
     * data kecamatan dipilih
     *  @var string 
     */
    function kecamatan() {

        var kel = document.getElementById('kec').value;
        var tmp1 = <?php echo $json->encode($tmp1); ?>;
        var tmp2 = <?php echo $json->encode($tmp2); ?>;
        cityList1 = tmp1[kel];
        cityList2 = tmp2[kel];
        changeSelecta('kel', cityList1, cityList2);

    }

    function kecamatan2() {

        var kel2 = document.getElementById('kec2').value;
        var tmp12 = <?php echo $json->encode($tmp1); ?>;
        var tmp22 = <?php echo $json->encode($tmp2); ?>;
        cityList12 = tmp12[kel2];
        cityList22 = tmp22[kel2];

        changeSelecta('kel2', cityList12, cityList22);

    }

    function changeSelecta(fieldID, newOptions, newValues) {

        selectField = document.getElementById(fieldID);
        selectField.options.length = 0;
        selectField.options[selectField.length] = new Option("Pilih", "");
        for (i = 0; i < newOptions.length; i++) {
            selectField.options[selectField.length] = new Option(newOptions[i], newValues[i]);
        }
    }
</script>

<?php
$action = $link;
$action2 = $link2;
echo "<br/>";
/** #@+
 *  untuk menampilkan paging serta pengiriman request data melalui json
 *  @var string 
 */
$tmp = array();
if (isset($_GET['hal'])) {
    $noPage = $_GET['hal'];
} else {
    $noPage = 1;
}


$prev = "";
$next = "";
if ($jumPage > 1) {
    if ($src != "2") {
        if ($tabs) {
            $last = "<a href='" . $link . "&hal=$jumPage&i=$id&s=$src&thnawal=$thnawal&thn=$thn#tabs-1'>Last</a>";
        } else {
            $last2 = "<a href='" . $link3 . "&hal=$jumPage&i=$id&s=$src&thnawal=$thnawal&thn=$thn#tabs-2'>Last</a>";
        }
    } else {
        if ($tabs) {
            $last = "<a href='" . $link . "&hal=$jumPage&i=$id&thnawal=$thnawal&thn=$thn#tabs-1'>Last</a>";
        } else {
            $last2 = "<a href='" . $link3 . "&hal=$jumPage&i=$id&thnawal=$thnawal&thn=$thn#tabs-2'>Last</a>";
        }
    }
}
if ($noPage > 1) {
    $page = $noPage - 1;

    if ($src != "2") {
        if ($tabs) {
            $first = "<a href='" . $link . "&hal=1&i=$id&s=$src&thnawal=$thnawal&thn=$thn#tabs-1'>First</a>";
        } else {
            $first2 = "<a href='" . $link3 . "&hal=1&i=$id&s=$src&thnawal=$thnawal&thn=$thn#tabs-2'>First</a>";
        }
    } else {
        if ($tabs) {
            $first = "<a href='" . $link . "&hal=1&i=$id&thnawal=$thnawal&thn=$thn#tabs-1'>First</a>";
        } else {
            $first2 = "<a href='" . $link3 . "&hal=1&i=$id&thnawal=$thnawal&thn=$thn#tabs-2'>First</a>";
        }
    }

    if ($src != "2") {
        if ($tabs) {
            $prev = "<a href='" . $link . "&hal=$page&i=$id&s=$src&thnawal=$thnawal&thn=$thn#tabs-1'>Previous</a>";
        } else {
            $prev2 = "<a href='" . $link3 . "&hal=$page&i=$id&s=$src&thnawal=$thnawal&thn=$thn#tabs-2'>Previous</a>";
        }
    } else {
        if ($tabs) {
            $prev = "<a href='" . $link . "&hal=$page&i=$id&thnawal=$thnawal&thn=$thn#tabs-1'>Previous</a>";
        } else {
            $prev2 = "<a href='" . $link3 . "&hal=$page&i=$id&thnawal=$thnawal&thn=$thn#tabs-2'>Previous</a>";
        }
    }
}

if ($noPage < $jumPage) {
    $page = $noPage + 1;
    if ($src != "2") {
        if ($tabs) {
            $next = "<a href='" . $link . "&hal=$page&i=$id&s=$src&thnawal=$thnawal&thn=$thn#tabs-1'>Next</a>";
        } else {
            $next2 = "<a href='" . $link3 . "&hal=$page&i=$id&s=$src&thnawal=$thnawal&thn=$thn#tabs-2'>Next</a>";
        }
    } else {
        if ($tabs) {
            $next = "<a href='" . $link . "&hal=$page&i=$id&thnawal=$thnawal&thn=$thn#tabs-1'>Next</a>";
        } else {
            $next2 = "<a href='" . $link3 . "&hal=$page&i=$id&thnawal=$thnawal&thn=$thn#tabs-2'>Next</a>";
        }
    }
}

for ($page = 1; $page <= $jumPage; $page++) {
    if ((($page >= $noPage - 3) && ($page <= $noPage + 3)) || ($page == 1) || ($page == $jumPage)) {
        if (($noPage == 1) && ($page != 2))
            $angka = "...";
        if (($noPage != ($jumPage - 1)) && ($page == $jumPage))
            $angka = "...";
        if ($page == $noPage)
            $angka = " <b>" . $page . "</b> ";
        else
            $angka = " <a href='" . $_SERVER['PHP_SELF'] . "?page=" . $page . "'>&nbsp;" . $page . "&nbsp;</a>";
        $noPage = $page;
    }
}


if ($src == "2" or $src == "1" or $src == "0") {
    $ctnt3 .= "<br/><table width='90%' border='0' style='font-size:12pt;' class='transparent'>";
    $ctnt3 .= "<tr class='transparent'>";
    if ($tabs) {
        $ctnt3 .= "<td colspan='5' align='right'>" . $first . " &nbsp;&nbsp; " . $prev . " &nbsp;&nbsp; " . $next . "&nbsp;&nbsp; " . $last . "</td>";
    } else {
        $ctnt3 .= "<td colspan='5' align='right'>" . $first2 . " &nbsp;&nbsp; " . $prev2 . " &nbsp;&nbsp; " . $next2 . "&nbsp;&nbsp; " . $last2 . "</td>";
    }
    $ctnt3 .= "</tr>";
    $ctnt3 .= "</table>";
}

if ($hsl->RC != "0005") {

    foreach ($tmpData as $key => $value) {
        $nop = $value->NOP;
        $nama = $value->NM_WP_SPPT;
        $jumlah = number_format($value->PBB_YG_HARUS_DIBAYAR_SPPT, 2, ',', '.');
        $tgl = explode("-", $value->TGL_TERBIT_SPPT);
        $date = substr($tgl[2], 0, 2);
        $tglTerbit = $date . "-" . $tgl[1] . "-" . $tgl[0];
        $tgl = explode("-", $value->TGL_JATUH_TEMPO_SPPT);
        $date = substr($tgl[2], 0, 2);
        $tglTempo = $date . "-" . $tgl[1] . "-" . $tgl[0];
        $ttmp = substr($value->NOP, 4, 3);
        $kecamatan = substr($value->NOP, 4, 3);
        $kelurahan = substr($value->NOP, 7, 3);
        $status = $value->STATUS_PEMBAYARAN_SPPT;
        foreach ($IdKec as $key => $value) {
            if ($key == $kecamatan) {
                $kecamatan = $value;
                break;
            }
        }

        foreach ($IdKel[$ttmp] as $key => $value) {

            if ($key == $kelurahan) {
                $kelurahan = $value;
                break;
            }
        }

        if ($status) {
            $dte .= "<tr>";

            $dte .= " <td style='border:1px solid black;'>&nbsp;" . $nop . "</td>";
            $dte .= " <td style='border:1px solid black;'>&nbsp;" . $nama . "</td>";
            $dte .= " <td align='center' style='border:1px solid black;'>&nbsp;" . $kecamatan . "</td>";
            $dte .= " <td align='center' style='border:1px solid black;'>&nbsp;" . $kelurahan . "</td>";
            $dte .= " <td align='center' style='border:1px solid black;'>&nbsp;" . $tglTerbit . "</td>";
            $dte .= " <td align='center' style='border:1px solid black;'>&nbsp;" . $tglTempo . "</td>";
            $dte .= " <td align='right' style='border:1px solid black;'>" . $jumlah . "&nbsp;</td>";
            if ($status) {
                $dte .= " <td align='center' style='border:1px solid black;'>&nbsp;Terbayar</td>";
            } else {
                $dte .= " <td align='center' style='border:1px solid black;'>&nbsp;Belum</td>";
            }
            $dte .= "</tr>";
        } else {
            $dte2 .= "<tr>";

            $dte2 .= " <td style='border:1px solid black;'>&nbsp;" . $nop . "</td>";
            $dte2 .= " <td style='border:1px solid black;'>&nbsp;" . $nama . "</td>";
            $dte2 .= " <td align='center' style='border:1px solid black;'>&nbsp;" . $kecamatan . "</td>";
            $dte2 .= " <td align='center' style='border:1px solid black;'>&nbsp;" . $kelurahan . "</td>";
            $dte2 .= " <td align='center' style='border:1px solid black;'>&nbsp;" . $tglTerbit . "</td>";
            $dte2 .= " <td align='center' style='border:1px solid black;'>&nbsp;" . $tglTempo . "</td>";
            $dte2 .= " <td align='right' style='border:1px solid black;'>" . $jumlah . "&nbsp;</td>";
            if ($status) {
                $dte2 .= " <td align='center' style='border:1px solid black;'>&nbsp;Terbayar</td>";
            } else {
                $dte2 .= " <td align='center' style='border:1px solid black;'>&nbsp;Belum</td>";
            }
            $dte2 .= "</tr>";
        }
    }
} else {
    if ($status) {
        $dte .= "<tr><td colspan='8' style='color:#FF0000' align='center' style='font-size:13px'>Maaf data kosong</td></tr>";
    } else {
        $dte2 .= "<tr><td colspan='8' style='color:#FF0000' align='center' style='font-size:13px'>Maaf data kosong</td></tr>";
    }
}
?>

<body>
    <?php
    if ($_SESSION['if'] == '' and $_SESSION['port'] == '') {
    ?>

        <form id="form1" name="form1" method="post" action="" class="transparent">
            <table width="250" border="0" align="center" cellpadding="0" cellspacing="1">
                <tr>
                    <td width="75">IP</td>
                    <td width="12">:</td>
                    <td width="162"><label>
                            <input type="text" name="ip" id="ip" />
                        </label></td>
                </tr>
                <tr>
                    <td>Port</td>
                    <td>:</td>
                    <td><label>
                            <input name="port" type="text" id="port" size="10" />
                        </label></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><label>
                            <input type="submit" name="kirim" id="kirim" value="OK" />
                        </label></td>
                </tr>
            </table>
        </form>

    <?php } else { ?>
        <div id="div-search">
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1">Sudah Bayar</a></li>
                    <li><a href="#tabs-2">Belum Bayar</a></li>
                </ul>
                <div id="tabs-1">
                    <fieldset>
                        <form id="TheForm-2" name="form2" method="post" action="<?php echo $action; ?>">
                            <table border="0" cellpadding="0" cellspacing="0" class='transparent'>
                                <tr>
                                    <td width="88" style="font-size:medium">Kecamatan</td>
                                    <td width="9">&nbsp;:&nbsp;</td>
                                    <td><select name="kec" id="kec" onChange="kecamatan()">
                                            <option selected="selected" value="0">Pilih</option>
                                            <?php echo $hasil; ?>
                                        </select></td>

                                    <td style="font-size:medium">&nbsp;Kelurahan</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td>
                                        <select name="kel" id="kel" style="width:150px">
                                            <option selected='selected' value="0">Pilih</option>
                                            <?php echo $kec; ?>
                                        </select>
                                    </td>
                                    <td style="font-size:medium">&nbsp;Tanggal</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td><input id="tanggal1" name="tanggal1" type="text" size="10" />

                                    <td style="font-size:medium">&nbsp;s/d</td>
                                    <td>&nbsp;&nbsp;</td>
                                    <td><input id="tanggal2" name="tanggal2" type="text" size="10" /></td>

                                    <td style="font-size:medium">&nbsp;Tahun</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td><select name="thnawal" id="thnawal" style="width:70px">
                                            <option selected="selected" value="<?php echo date('Y'); ?>"> Pilih </option>
                                            <?php echo $ThnAwal; ?>
                                        </select>


                                    <td style="font-size:medium">&nbsp;Pencarian</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td><input name="src" id="src" type="text" style="width:200px;height:25px; " /></td>
                                    <td colspan="15">
                                        <input type="hidden" name="host" value="<?php echo HOST; ?>" />
                                        <input type="hidden" name="port" value="<?php echo PORT; ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="25"><input type="submit" name="Show" id="Show" value="Tampilkan" />
                                        <input type="button" name="Download" id="Download" value="Download" /></td>
                                </tr>
                            </table>
                        </form>
                    </fieldset>
                    <br />
                    <?php
                    //echo $ctnt;
                    //echo $ctnt1; 
                    //echo $ctnt3; 
                    ?>
                    <div id="keseluruhan">
                        Jumlah Bayar : <span id="bayar"></span>
                    </div>
                    <div id="TableConent1" style="width: 100%;"></div>

                </div>
                <div id="tabs-2">
                    <fieldset>
                        <form id="TheForm-3" name="form3" method="post" action="<?php echo $action2; ?>">
                            <table border="0" cellpadding="0" cellspacing="0" class='transparent'>
                                <tr>
                                    <td width="88" style="font-size:medium">Kecamatan</td>
                                    <td width="9">&nbsp;:&nbsp;</td>
                                    <td><select name="kec2" id="kec2" onChange="kecamatan2()">
                                            <option selected="selected" value="0">Pilih</option>
                                            <?php echo $hasil2; ?>
                                        </select></td>
                                    <td style="font-size:medium">&nbsp;Kelurahan</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td>
                                        <select name="kel2" id="kel2" style="width:150px">
                                            <option selected='selected' value="0">Pilih</option>
                                            <?php echo $kec2; ?>
                                        </select> </td>
                                    <!--<td  style="font-size:medium">&nbsp;Tanggal</td>
                                    <td >&nbsp;:&nbsp;</td>
                                    <td ><input id="tanggal3" name="tanggal3" type="text"  size="10" />

                                    <td  style="font-size:medium">&nbsp;s/d</td>
                                    <td >&nbsp;&nbsp;</td>
                                    <td ><input id="tanggal4" name="tanggal4" type="text"  size="10" /></td>-->
                                    <td style="font-size:medium">Tahun</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td><select name="thnawal2" id="thnawal2" style="width:70px">
                                            <option selected="selected" value="<?php echo date('Y'); ?>"> Pilih </option>
                                            <?php echo $ThnAwal2; ?>
                                        </select>
                                    </td>
                                    <td width="16">&nbsp;</td>
                                    <td style="font-size:medium">Pencarian</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td><input name="src" type="text" style="width:200px;height:25px; " /></td>
                                    <td colspan="15"></td>
                                </tr>
                                <tr>
                                    <td colspan="25"><input type="submit" name="Show2" id="Show2" value="Tampilkan" />
                                        <input type="button" name="Download2" id="Download2" value="Download" /></td>
                                </tr>
                            </table>
                        </form>
                    </fieldset>
                    <br />
                    <?php
                    //echo $ctnt11;
                    //echo $ctnt21; 
                    //echo $ctnt3; 
                    ?>
                    <div id="keseluruhan2">
                        Jumlah Bayar : <span id="bayar2"></span>
                    </div>
                    <div id="TableConent2" style="width: 100%;"></div>

                </div>
            </div>
        </div>
        <div id="dialog-modal" style="display:none">Proses download data... </div>
        <?php
        $awal = $_SESSION['awal'];
        $akhir = $_SESSION['akhir'];
        $kec = $_SESSION['kec'];
        $kel = $_SESSION['kel'];
        ?>

    <?php } ?>
    <script>
        $(document).ready(function() {

            $('#TableConent1').jtable({
                paging: true,
                pageSize: 20,
                actions: {
                    listAction: 'view/Monitoring/dataList.php?action=list'
                },
                fields: {
                    Npwp: {
                        key: true,
                        list: true,
                        title: 'NOP',
                        width: '10%'

                    },
                    Nama: {
                        title: 'Nama WP',
                        width: '25%'
                    },
                    Kec: {
                        title: 'Kecamatan',
                        width: '8%'
                    },
                    Kel: {
                        title: 'Kelurahan',
                        width: '8%'
                    },
                    Bayar: {
                        title: 'Tanggal Pembayaran',
                        width: '15%'
                    },
                    Jumlah: {
                        title: 'Dibayar',
                        width: '8%'
                    },
                    Nip: {
                        title: 'NIP Perekam',
                        width: '8%'
                    },
                    Rekam: {
                        title: 'Tanggal Rekam',
                        width: '15%'
                    }

                },
                recordsLoaded: function(event, data) {
                    $("#bayar").html(data.serverResponse.TotalSum1);
                }

            });
            $("#keseluruhan").appendTo("#tabs-1 .jtable-bottom-panel").addClass('filter_class');
            $('#TableConent1').jtable('load');

            $('#TableConent2').jtable({
                paging: true,
                pageSize: 20,
                actions: {
                    listAction: 'view/Monitoring/dataBelumBayar.php?action=list'
                },
                fields: {
                    Npwp: {
                        key: true,
                        list: true,
                        title: 'NPWP',
                        width: '10%'

                    },
                    Nama: {
                        title: 'Nama WP',
                        width: '25%'
                    },
                    Kec: {
                        title: 'Kecamatan',
                        width: '8%'
                    },
                    Kel: {
                        title: 'Kelurahan',
                        width: '8%'
                    },
                    Terbit: {
                        title: "Tanggal Terbit",
                        width: '15%'
                    },
                    Bayar: {
                        title: 'Tanggal Jatuh Tempo',
                        width: '15%'
                    },
                    Jumlah: {
                        title: 'Jumlah',
                        width: '8%'
                    }
                },
                recordsLoaded: function(event, data) {
                    $("#bayar2").html(data.serverResponse.TotalSum2);
                }
            });
            $("#keseluruhan2").appendTo("#tabs-2 .jtable-bottom-panel").addClass('filter_class');
            $('#TableConent2').jtable('load');
            $("input:submit, input:button").button();
            $("#Download").click(function(e) {
                $("#dialog-modal").dialog({
                    title: "Download",
                    closeOnEscape: false,
                    height: 70,
                    modal: true
                });

                $.post('<?php echo URL; ?>', {
                        awal: '<?php echo $thnawal; ?>',
                        tgl1: '<?php echo $tanggal1; ?>',
                        tgl2: '<?php echo $tanggal2; ?>',
                        kc: '<?php echo $kec1; ?>',
                        kl: '<?php echo $kel1; ?>',
                        namkec: '<?php echo $_SESSION['Nama_kec1']; ?>',
                        namkel: '<?php echo $_SESSION['Nam_kel1']; ?>',
                        src: '<?php echo $src; ?>',
                        host: "<?php echo HOST; ?>",
                        port: "<?php echo PORT; ?>",
                        time: <?php echo TIME; ?>,
                        download: 1
                    },
                    function(hasil) {
                        if (hasil) {
                            $("#dialog-modal").dialog("close");
                        }

                        //ExcellentExport.excel("PBB-Sudah bayar.xls", 'jtable', 'Sudah Bayar');
                        //"data:text/csv;charset=utf-8,"
                        window.open("data:application/vnd.ms-excel," + encodeURIComponent(hasil));
                        //window.open('data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent($('#TableConent1').html())); 

                    });
                //				  <?php //echo 'var host="http://'.HOST.'";';  
                                    ?>
                //				  
                //			   $.fileDownload(host+hasil)
                //    		   		.done(function () { 
                //				
                //					      setTimeout(function() {   
                //					            $.post('<?php echo URL; ?>',{fl:hasil,hapus:1},function(hps){
                //								        if(hps){
                //										  $("#dialog-modal").dialog( "close" );
                //										}  
                //					            });
                //						   },700);		        
                //					    }).fail(function () { alert('File download failed!'); });
                //			});

            });
            $("#Download2").click(function(e) {
                $("#dialog-modal").dialog({
                    title: "Download",
                    closeText: "hide",
                    height: 70,
                    modal: true
                });
                //document.cookie = 'Set-Cookie:fileDownload=true; path=/';
                $.post('<?php echo URL2; ?>', {
                        awal: '<?php echo $thnawal; ?>',
                        tgl1: '<?php echo $tanggal1; ?>',
                        tgl2: '<?php echo $tanggal2; ?>',
                        kc: '<?php echo $kec1; ?>',
                        kl: '<?php echo $kel1; ?>',
                        namkec: '<?php echo $_SESSION['Nama_kec2']; ?>',
                        namkel: '<?php echo $_SESSION['Nam_kel2']; ?>',
                        src: '<?php echo $src; ?>',
                        host: "<?php echo HOST; ?>",
                        port: "<?php echo PORT; ?>",
                        time: <?php echo TIME; ?>,
                        download: 2
                    },
                    function(hasil) {
                        if (hasil) {
                            $("#dialog-modal").dialog("close");
                        }
                        //window.open('data:application/vnd.ms-excel,' + hasil); 
                        window.open("data:application/vnd.ms-excel," + encodeURIComponent(hasil));
                    });
            });
        });
    </script>

</body>