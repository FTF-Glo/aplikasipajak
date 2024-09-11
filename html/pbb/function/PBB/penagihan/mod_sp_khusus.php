<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
//require_once($sRootPath."function/PBB/penagihan/svc-count-notif.php");


$appConfig = $User->GetAppConfig($application);

$dbhost = $appConfig['GW_DBHOST'];
$dbuser = $appConfig['GW_DBUSER'];
$dbpwd = $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME'];
$perpage = $appConfig['ITEM_PER_PAGE'];
$kota = $appConfig['NAMA_KOTA'];
$kodekota = $appConfig['KODE_KOTA'];
$lblkel = $appConfig['LABEL_KELURAHAN'];
$tahun_tagihan = $appConfig['tahun_tagihan'];

$kabid_nama = $appConfig['KABID_NAMA'];
$kabid_nip = $appConfig['KABID_NIP'];
$kabid_jabatan = $appConfig['KABID_JABATAN'];

$kadis_nama = $appConfig['NAMA_PEJABAT_SK2'];
$kadis_nip = $appConfig['NAMA_PEJABAT_SK2_NIP'];
$kadis_jabatan = $appConfig['NAMA_PEJABAT_SK2_JABATAN'];

$modConfig = $User->GetModuleConfig($module);

$sp1 = $modConfig['SP1'];
$sp2 = $modConfig['SP2'];
$sp3 = $modConfig['SP3'];
$bank = $modConfig['bank'];
$denda = $modConfig['persentasi_denda'];
$totalBulanPajak = $modConfig['total_bulan_pajak'];
$tipeKalkulasiPajak = $modConfig['tipe_kalkulasi_pajak'];
$kd_dispenda = $modConfig['KODE_DISPENDA'];
$kd_bidang = $modConfig['KODE_BIDANG'];
$kd_seksi = $modConfig['KODE_SEKSI'];
$limit_thnpajak = $modConfig['LIMIT_TAHUN_PAJAK'];

$userRole = $User->GetUserRole($uid, $a);
$arrUserRole = explode(',', $modConfig['role_id_kasi_pengurangan']);
$isAdminPenagihan = 0;
if (in_array($userRole, $arrUserRole)) {
    $isAdminPenagihan = 1;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

function displayMenuPendata()
{  // srch
    global $a, $m, $data, $countSP1, $countSP2, $countSP3, $dbhost, $dbuser, $dbpwd, $dbname, $kabid_nama, $kabid_nip, $kabid_jabatan, $kadis_nama, $kadis_nip, $kadis_jabatan, $kota, $sp1, $sp2, $sp3, $lblkel, $bank, $denda, $perpage, $kodekota, $kd_dispenda, $kd_bidang, $kd_seksi, $isAdminPenagihan, $tahun_tagihan;

    $clrSP1 = "#ff0000";
    $clrSP2 = "#ff0000";
    $clrSP3 = "#ff0000";
    $clrAll = "#ff0000";
    if ($countSP1['TOTALROWS'] == 0) {
        $clrSP1 = "";
    }
    if ($countSP2['TOTALROWS'] == 0) {
        $clrSP2 = "";
    }
    if ($countSP3['TOTALROWS'] == 0) {
        $clrSP3 = "";
    }
    if (($countSP1['TOTALROWS'] + $countSP2['TOTALROWS'] + $countSP3['TOTALROWS']) == 0) {
        $clrAll = "";
    }

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"function/PBB/penagihan/svc-list-sp_khusus.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'1', 'n':'1', 'u':'$data->uid', 'adm':'$isAdminPenagihan', 'dbhost':'$dbhost', 'dbuser':'$dbuser', 'dbpwd':'$dbpwd', 'dbname':'$dbname', 'kepala':'$kadis_nama','nip':'$kadis_nip','jabatan':'$kadis_jabatan','kota':'$kota','sp1':'$sp1','sp2':'$sp2','sp3':'$sp3', 'perpage':'$perpage', 'kodekota':'$kodekota', 'lblkel':'$lblkel', 'thn':'$tahun_tagihan'}") . "\"><div id=\"sp1\" class=\"notif\">SP Tahun 2015<font color=\"" . $clrSP1 . "\"> </font></div></a></li>\n";
    echo "\t</ul>\n";
}

function displayMenuAdmin()
{  // srch
    global $a, $m, $data, $countSP1, $countSP2, $countSP3, $dbhost, $dbuser, $dbpwd, $dbname, $kabid_nama, $kabid_nip, $kabid_jabatan, $kadis_nama, $kadis_nip, $kadis_jabatan, $kota, $sp1, $sp2, $sp3, $lblkel, $bank, $denda, $perpage, $kodekota, $kd_dispenda, $kd_bidang, $kd_seksi, $isAdminPenagihan, $tahun_tagihan;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"function/PBB/penagihan/svc-list-sp_khusus.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'9', 'n':'9', 'u':'$data->uid', 'adm':'$isAdminPenagihan', 'dbhost':'$dbhost', 'dbuser':'$dbuser', 'dbpwd':'$dbpwd', 'dbname':'$dbname', 'kepala':'$kadis_nama','nip':'$kadis_nip','jabatan':'$kadis_jabatan','kota':'$kota','sp1':'$sp1','sp2':'$sp2','sp3':'$sp3', 'perpage':'$perpage', 'kodekota':'$kodekota', 'lblkel':'$lblkel', 'thn':'$tahun_tagihan'}") . "\"><div id=\"psp2\" class=\"notif\">Persetujuan SP</div></a></li>\n";
    echo "\t</ul>\n";
}

?>
<link rel="stylesheet" href="view/PBB/penagihan/sppgh.css" type="text/css">
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />

<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript">
    var page = 1;
    var axx = '<?php echo base64_encode($a) ?>';
    var dbhost = "<?php echo $dbhost; ?>";
    var dbuser = "<?php echo $dbuser; ?>";
    var dbpwd = "<?php echo $dbpwd; ?>";
    var dbname = "<?php echo $dbname; ?>";
    var sp1 = "<?php echo $sp1; ?>";
    var sp2 = "<?php echo $sp2; ?>";
    var sp3 = "<?php echo $sp3; ?>";
    var bank = "<?php echo $bank; ?>";
    var denda = "<?php echo $denda; ?>";
    var totalBulanPajak = "<?php echo $totalBulanPajak; ?>";
    var tipeKalkulasiPajak = "<?php echo $tipeKalkulasiPajak; ?>";
    var kd_dispenda = "<?php echo $kd_dispenda; ?>";
    var kd_bidang = "<?php echo $kd_bidang; ?>";
    var kd_seksi = "<?php echo $kd_seksi; ?>";
    var limitTahunPajak = "<?php echo $limit_thnpajak; ?>";

    function setTabs(sts, clearStatus) {
        //setter
        var sel = 0;
        var find = "";
        if (sts == 1) sel = 0;
        if (sts == 2) sel = 1;
        if (sts == 3) sel = 2;
        if (sts == 4) sel = 3;
        if (sts == 5) sel = 4;
        if (sts == 6) sel = 5;
        if (sts == 7) sel = 6;
        if (sts == 8) sel = 7;
        if (!clearStatus) {
            find = $("#src-approved-" + sts).val() + "&" + $("#src-tagihan-" + sts).val() + "&" + $("#src-kecamatan-" + sts).val() + "&" + $("#src-kelurahan-" + sts).val() + "&" + $("#src-tahun-" + sts).val() + "&" + $("#src-nop-" + sts).val();
        }
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                find: find,
                isViewData: 1
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function setPage(sts, np) {
        if (np == 1) page++;
        else page--;
        var sel = 0;
        var find = "";

        if (sts == 1) sel = 0;
        if (sts == 2) sel = 1;
        if (sts == 3) sel = 2;
        if (sts == 4) sel = 3;
        if (sts == 5) sel = 4;
        if (sts == 6) sel = 5;
        if (sts == 7) sel = 6;
        if (sts == 8) sel = 7;
        find = $("#src-approved-" + sts).val() + "&" + $("#src-tagihan-" + sts).val() + "&" + $("#src-kecamatan-" + sts).val() + "&" + $("#src-kelurahan-" + sts).val() + "&" + $("#src-tahun-" + sts).val() + "&" + $("#src-nop-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                page: page,
                np: np,
                find: find,
                isViewData: 1
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function refreshNotif() {

        $.ajax({
            type: "POST",
            url: "./function/PBB/penagihan/svc-count-notif-jv.php",
            data: "dbhost=" + dbhost + "&dbuser=" + dbuser + "&dbpwd=" + dbpwd + "&dbname=" + dbname + "&sp1=" + sp1 + "&sp2=" + sp2 + "&sp3=" + sp3,
            success: function(msg) {
                arrNotif = msg.split("+");
                clrSP1 = "#ff0000";
                clrSP2 = "#ff0000";
                clrSP3 = "#ff0000";
                clrAll = "#ff0000";
                if (arrNotif[0] == 0) {
                    clrSP1 = "";
                }
                if (arrNotif[1] == 0) {
                    clrSP2 = "";
                }
                if (arrNotif[2] == 0) {
                    clrSP3 = "";
                }
                if (arrNotif[3] == 0) {
                    clrAll = "";
                }
                $("#sp1").html("SP1<font color='" + clrSP1 + "'> (" + arrNotif[0] + ")</font>");
                $("#sp2").html("SP2<font color='" + clrSP2 + "'> (" + arrNotif[1] + ")</font>");
                $("#sp3").html("SP3<font color='" + clrSP3 + "'> (" + arrNotif[2] + ")</font>");
                $("#all").html("ALL SP<font color='" + clrAll + "'> (" + arrNotif[3] + ")</font>");
            }
        });
    }

    $(document).ready(function() {
        //        setInterval(function(){refreshNotif()}, 10000 );

        $("#all-check-button").click(function() {
            $('.check-all').each(function() {
                this.checked = $("#all-check-button").is(':checked');
            });
        });

        $("#tabsContent").tabs({
            load: function(e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },

            select: function(e, ui) {
                var $panel = $(ui.panel);

                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });
        <?php
        if (isset($n)) {
            $arrN = array(1 => 2, 2 => 3, 3 => 4, 4 => 4, 5 => 5);
        ?>
            setTabs(<?php echo $arrN[$n] ?>);
        <?php
        }
        ?>
    });
</script>
<style type="text/css">
    .notif {
        font-weight: normal;
    }
</style>
<div class="col-md-12">
    <div id="tabsContent">
        <?php
        if (isset($n)) {
            $arrN = array(1 => 2, 2 => 3, 3 => 4, 4 => 5);
            echo "<script language='javascript'>setTabs(" . $arrN[$n] . ")</script>";
        }

        if ($isAdminPenagihan == 1)
            echo displayMenuAdmin();
        else
            echo displayMenuPendata();
        ?>
    </div>
</div>

<style type="text/css">
    #btnClose {
        cursor: pointer;
    }

    .linkapprove:hover,
    .linkcetakup3:hover,
    .linkcetakup2:hover,
    .linkcetakpsp9:hover,
    .linkcetakpsp8:hover,
    .linkcetakpsp7:hover,
    .linkcetakpsp6:hover,
    .linkketpsp1:hover,
    .linkto:hover,
    .linkstpd:hover,
    .linkdate:hover,
    .linkketerangan:hover {
        color: #ce7b00;
    }

    .linkapprove,
    .linkcetakup3,
    .linkcetakup2,
    .linkcetakpsp9,
    .linkcetakpsp8,
    .linkcetakpsp7,
    .linkcetakpsp6,
    .linkketpsp1,
    .linkto,
    .linkstpd,
    .linkdate,
    .linkketerangan {
        text-decoration: underline;
        cursor: pointer;
    }

    #setketSP1-1,
    #setketSP1-2,
    #setyear1,
    #setyear2,
    #contsetdate1,
    #contsetdate2,
    #contsetdate3,
    #contsetdate4 {
        display: none;
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    #setketSP1-1,
    #setyear1,
    #contsetdate1,
    #contsetdate3 {
        background-color: #000000;
        filter: alpha(opacity=70);
        opacity: 0.7;
        z-index: 1;
    }

    #setketSP1-2,
    #setyear2,
    #contsetdate2,
    #contsetdate4 {
        z-index: 2;
    }

    #closesetSP1,
    #closedyear,
    #closeddate,
    #closedketerangan {
        cursor: pointer;
    }
</style>

<div id="setketSP1-2">
    <div id="setyear" style="width: 395px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 395; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;">
            <div style="float: left; margin: 5px;"><b>Pilih Status dan Isi Keterangan</b></div>
            <div id="closesetSP1" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div id="setket" style="margin: 10px;margin-left: 10px;">
            <input type="radio" name="ketsp1" id="ketsp1" value="1"> SP1 yang sudah diterima Wajib Pajak<br>
            <input type="radio" name="ketsp1" id="ketsp2" value="2"> Wajib Pajak yang sudah membayar PBB setelah penerbitaan SP 1<br>
            <input type="radio" name="ketsp1" id="ketsp3" value="3"> Data Wajib Pajak yang dibatalkan<br>
            <input type="radio" name="ketsp1" id="ketsp4" value="4"> Alamat tidak ditemukan<br>
            <input type="radio" name="ketsp1" id="ketsp5" value="5"> Tanah sengketa<br>
            <input type="radio" name="ketsp1" id="ketsp6" value="6"> Wajib Pajak sudah melakukan perubahan data<br>
            <div id="error1"></div>
            <br>Keterangan:
            <textarea rows="4" cols="44" id="keterangan"></textarea>
            <input type="hidden" id="nop_fu" />
            <div id="error2"></div>
            <br>
            <div align="right"><button id="simpanketSP1">Simpan</button></div>
        </div>
    </div>
</div>
<div id="setketSP1-1"></div>

<div id="setyear2">
    <div id="setyear" style="width: 192px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 190; height: 30px; border-bottom: 1px solid #eaeaea;">
            <div style="float: left; margin: 5px;"><b>Pilih Tahun Surat Penagihan</b></div>
            <div id="closedyear" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div id="setyear_form" style="margin: 10px;margin-left: 10px;">
            <input type='checkbox' name='select-all' id='select-all' /> Pilih Semua<br>
            <div id="year"></div>
            <input type="hidden" id="nop_sp1" />
            <div align='right' style="margin: 2 px;">
                <button id='cetakSP1'>Cetak</button>
            </div>
        </div>
    </div>
</div>
<div id="setyear1"></div>

<div id="contsetdate2">
    <div id="setdate" style="width: 250px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 248; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;">
            <div style="float: left; margin: 5px;"><b>Atur tanggal SP</b></div>
            <div id="closeddate" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div style="margin: 10px;margin-left: 10px;">
            <input type="text" name="tanggal" id="tanggal" readonly="readonly" />
            <input type="hidden" id="nop_fu" />
            <input type="hidden" id="thnpajak_fu" />
            <input type="hidden" id="sp_fu" />
            <button id="simpantanggal">Simpan</button>
        </div>
    </div>
</div>
<div id="contsetdate1"></div>

<div id="contsetdate4">
    <div id="setdate" style="width: 350px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 340px; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;">
            <div style="float: left; margin: 5px;"><b>Keterangan</b></div>
            <div id="closedketerangan" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div style="margin: 10px;margin-left: 10px;">
            <textarea name="keterangan" id="keterangan" cols="47" rows="2"></textarea>
            <input type="hidden" id="nop_fuket" />
            <input type="hidden" id="thnpajak_fuket" />
            <input type="hidden" id="sp_fuket" />
            <button id="simpanketerangan">Simpan</button>
        </div>
    </div>
</div>
<div id="contsetdate3"></div>