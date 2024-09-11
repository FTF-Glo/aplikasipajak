<?php
// print_r($_REQUEST);
// ini_set('memory_limit','500M');
// ini_set ("max_execution_time", "100000");
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/PBB/dbServices.php");

// echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js\"></script>";
//echo "<script language=\"javascript\">$(\"input:submit, input:button\").button();</script>";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
function showKec()
{
    global $aKecamatan, $kec;
    foreach ($aKecamatan as $row)
        echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
}

function displayContent($selected)
{
    global $isSusulan, $kec, $kel, $jumlah, $srch, $PenilaianParam, $appConfig, $module, $m, $aKecamatanPendataan, $conf_kdkec, $aKecamatan, $aKelurahan, $aKelurahanPendataan, $a, $dbUtils, $dbGwCurrent, $tahun, $uid, $uname, $buku, $displayDat;

    echo "<form name=\"mainform\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"kecamatan\" value=\"" . $kel . "\">";
    echo "<div class=\"row\">\n";
    echo "<div class=\"col-md-12\">";
    $verif_massal = isset($appConfig['PENDATAAN_VERIFIKASI_MASSAL']) ? $appConfig['PENDATAAN_VERIFIKASI_MASSAL'] : '';
    if ($selected == 50) {
        // echo "<input type=\"button\" value=\"Kirim Notifikasi\" name=\"btn-kirim-notifikasi\"/ onClick=\"return actionSendNotification('".$PenilaianParam."', '1', '".$a."');\">\n";
    } else if ($selected == 10) {

        echo "<button class=\"btn btn-primary bg-maka\" type=\"submit\" value=\"Finalkan\" name=\"btn-finalize\"/ onClick=\"return confirm('Anda yakin akan memfinalisasi data ini? Data akan langsung terkirim ke verifikasi')\">Finalkan</button>&nbsp;<input hidden type=\"submit\" value=\"Hapus\" name=\"btn-delete\"/ onClick=\"return confirm('Anda yakin akan menghapus seluruh dokumen ini?')\">&nbsp;&nbsp;&nbsp;\n";
    } else if ($selected == 21 && $verif_massal == 1) {
        echo "<button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Verifikasi Massal\" name=\"btn-verifikasi-massal\"/ onClick=\"return actionVerifikasiMassal();\">Verifikasi Massal</button>&nbsp;";
    } else if ($selected == 24) { //Tab Tertunda Modul Penetapan
        echo '<div class="row" style="margin-top: 15px; margin-bottom: 15px;">';
        //if ($m == $appConfig['id_mdl_susulan']) {
        echo "<div class=\"col-md-1\"><button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Proses Penetapan\" name=\"btn-penetapan\"/ onClick=\"return actionPenetapan('" . $PenilaianParam . "', '" . $appConfig['tahun_tagihan'] . "', '1','" . $uname . "');\">Proses Penetapan</button></div>\n";
        /*} else if ($m == $appConfig['id_mdl_penetapan']) {
            if (!$srch) {
                if ($kel != '') // hanya tampilkan kalau sudah memilih kelurahan
                    echo "<div class=\"col-md-2\"><button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Proses Penetapan\" name=\"btn-penetapan\"/ onClick=\"return actionPenetapan('" . $PenilaianParam . "', '" . $appConfig['tahun_tagihan'] . "', '0');\">Proses Penetapan</button></div>\n";
                else echo "<div class=\"col-md-2\"><button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Proses Penetapan Perkelurahan\" name=\"btn-penetapan\"/ onClick=\"return actionPenetapan('" . $PenilaianParam . "', '" . $appConfig['tahun_tagihan'] . "', '0','" . $uname . "');\" disabled=\"disabled\">Proses Penetapan Perkelurahan</button></div>\n";
            }
        }*/
        echo '<div class="col-md-2 text-right" style="padding-top: 7px; margin-left:40px">Tanggal Penetapan</div>
        <div class="col-md-2"><input type="text" id="tgl_penetapan" class="form-control" readonly="true" name="tgl_penetapan" value="' . date('d-m-Y') . '" maxlength="10" ></div>
            <div class="col-md-1" style="padding-top: 7px; width:80px;">Tampilkan</div>
            <div class="col-md-1">
                <select id="tampilkan_data" style="width:80px;" class="form-control" name="tampilkan_data" onchange="displayDat(\'' . $selected . '\', this)">
                    <option value="25" ' . ((!isset($displayDat) || empty($displayDat) || $displayDat == 25) ? 'selected' : '') . '>25</option>
                    <option value="50" ' . ((isset($displayDat) && $displayDat == 50) ? 'selected' : '') . '>50</option>
                    <option value="100" ' . ((isset($displayDat) && $displayDat == 100) ? 'selected' : '') . '>100</option>
                    <option value="200" ' . ((isset($displayDat) && $displayDat == 200) ? 'selected' : '') . '>200</option>
                    <option value="300" ' . ((isset($displayDat) && $displayDat == 300) ? 'selected' : '') . '>300</option>
                    <option value="400" ' . ((isset($displayDat) && $displayDat == 400) ? 'selected' : '') . '>400</option>
                    <option value="500" ' . ((isset($displayDat) && $displayDat == 500) ? 'selected' : '') . '>500</option>
                    <option value="1000" ' . ((isset($displayDat) && $displayDat == 1000) ? 'selected' : '') . '>1000</option>
                    <option value="2500" ' . ((isset($displayDat) && $displayDat == 2500) ? 'selected' : '') . '>2500</option>
                    <option value="5000" ' . ((isset($displayDat) && $displayDat == 5000) ? 'selected' : '') . '>5000</option>
                </select>
            </div>
        </div>
        </div>';
    } else if ($selected == 26) { //Tab Tertunda Modul Penetapan

        $cityID = $appConfig['KODE_KOTA'];
        $cityName = $appConfig['NAMA_KOTA'];
        $optionCityOP = "<option valued=$cityID>$cityName</option>";

        $provID = $appConfig['KODE_PROVINSI'];
        $provName = $appConfig['NAMA_PROVINSI'];
        $optionProvOP = "<option valued=$provID>$provName</option>";

        $datKec = $aKecamatan;
        $datKel = $aKelurahan;
        echo "
                <link href=\"view/PBB/monitoring.css\" rel=\"stylesheet\" type=\"text/css\"/>";
        echo "<script type=\"text/javascript\">
                function onSearchDataSPPTMundur() {
                    var nop = $(\"#nop_penetapan\").val();
                    var tahun = $(\"#tahun-penilaian\").val();
                    $(\"#monitoring-content-5\").html(\"loading ...\");
                    
                    var svc = \"\";
                    $(\"#monitoring-content-5\").load(\"view/PBB/svc-penilaian-mundur.php?q=" . base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'26','uid':'{$uid}'}") . "\",
                    {n:nop,t:tahun}, function(response, status, xhr) {
                        if (status == \"error\") {
                        var msg = \"Sorry but there was an error: \";
                        $(\"#monitoring-content-5\").html(msg + xhr.status + \" \" + xhr.statusText);
                        }
                    });     
                }
                </script>					";
        echo "<div id=\"div-search\" >";

        echo "<form name=\"form-penerimaan\" nilai='btn-save' id=\"form-penerimaan\" method=\"post\" action=\"\">
            <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
                <tbody id=\"info_lengkap\">
                <tr>
                <td width=\"39%\"><label for=\"provinsiOP\">Provinsi</label></td>
                <td width=\"60%\">
                    <select name=\"propinsiOP\" id=\"propinsiOP\" style=\"width:150px\">
                    
                    $optionProvOP
                    </select>
                </td>
                </tr>
                <tr>
                <td width=\"39%\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
                <td width=\"60%\">
                    <select name=\"kabupatenOP\" id=\"kabupatenOP\" style=\"width:150px\">$optionCityOP</select>
                </td>
                </tr>
                <tr>
                <td width=\"39%\"><label for=\"kecamatanOP\">Kecamatan</label></td>
                <td width=\"60%\">
                    <select name=\"kecamatanOP\" id=\"kecamatanOP\" style=\"width:150px\" onchange=\"showKel(this)\">
                    <option value=\"\">Kecamatan</option>";

        foreach ($datKec as $row) {
            $digit3 = substr($row['CPC_TKC_ID'], 4, 3);
            echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . " - " . $digit3 . "</option>";
        }
        echo "</select>
                    </td>
                  </tr>
                  <tr>
                    <td width=\"39%\"><label for=\"kel\">" . $appConfig['LABEL_KELURAHAN'] . "</label></td>
                    <td width=\"60%\">
                    <div id=\"sKel26\">
                      <select name=\"kel\" id=\"kel\" style=\"width:150px\">
                        <option value=\"\">Kelurahan/Desa</option>
                      </select>
                    </td>
                  </tr>
                  </tbody>
                  <tr>
                    <td width=\"39%\">Blok</td>
                    <td width=\"60%\">
                        <input type=\"text\" name=\"blok\" id=\"blok\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"3\" placeholder=\"Blok\" />
                    </td>
                    </tr>
                    <tr>
                        <td width=\"39%\">No Urut</td>
                        <td width=\"60%\">
                            <input type=\"text\" name=\"urut-1\" id=\"urut-1\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"4\" placeholder=\"No Urut\" />
                           
                        </td>
                    </tr>
                  <tr>
                    <td width=\"39%\">Tahun Pajak</td>
                    <td width=\"60%\">
                        <input type=\"text\" name=\"tahun-penilaian-1\" id=\"tahun-penilaian-1\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"4\" value=\"" . ((isset($initData['CPM_SPPT_YEAR']) && $initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $appConfig['tahun_tagihan']) . "\" placeholder=\"Tahun\" />
                        s/d<input type=\"text\" name=\"tahun-penilaian-2\" id=\"tahun-penilaian-2\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"4\" value=\"" . ((isset($initData['CPM_SPPT_YEAR']) && $initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $appConfig['tahun_tagihan']) . "\" placeholder=\"Tahun\" />
                        <input type=\"hidden\" id=\"tgl_penetapan\" readonly=\"true\" name=\"tgl_penetapan\" value=\"" . date('d-m-Y') . "\" maxlength=\"10\" size=\"12\">&nbsp;<br/><br/>
                     </td>
                  </tr>

                  <tr>
                    <td width=\"39%\"></td>
                    <td width=\"60%\">
                      <input type=\"hidden\" name=\"nop_penetapan\" id=\"nop_penetapan\" value=\"\"/>
                    </td>
                  </tr>
              </table>
              </form>";
        //   echo "NOP : <input type=\"text\" name=\"nop_penetapan\" id=\"nop_penetapan\" size=\"25\" maxlength=\"18\">&nbsp;&nbsp;&nbsp;";
        //   echo "Tahun : <select name=\"tahun-penilaian\" id=\"tahun-penilaian\">";
        //                                 // for ($t=date('Y')-1 ; $t > 1993; $t--) {
        //                                 //     if ($t == $appC ; $t > 1993; $t--) {
        //                                 for ($t=date('Y'); $t > 1993; $t--) {
        //                                     if ($t == $appConfig[tahun_tagihan]-1) {
        //                                         echo "<option value=\"$t\" selected>$t</option>";
        //                                     } else
        //                                         echo "<option value=\"$t\">$t</option>";
        //                                 }

        //   echo                            "</select> &nbsp;&nbsp;&nbsp;";
        //   echo 'Tanggal Penetapan <input type="text" id="tgl_penetapan" readonly="true" name="tgl_penetapan" value="'.date('d-m-Y').'" maxlength="10" size="12">&nbsp;<br/><br/>';
        if ($isSusulan) {
            echo "<input type=\"button\" class=\"btn btn-primary bg-orange\" value=\"Proses Penilaian\" name=\"btn-penilaian-mundur\" onClick=\"penilaianMundur(1);\">\n";
            echo "<input type=\"button\" class=\"btn btn-primary bg-blue\" value=\"Proses Penetapan Mundur\" name=\"btn-penetapan-mundur\" id=\"btn-penetapan-mundur\" onClick=\"return actionPenetapanMundur('" . $PenilaianParam . "', '1','" . $uid . "');\"  disabled=\"disabled\" >\n";
        } else {
            echo "<input type=\"button\" class=\"btn btn-primary bg-orange\" value=\"Proses Penilaian\" name=\"btn-penilaian-mundur\" onClick=\"penilaianMundur(0)\">\n";
            echo "<input type=\"button\" class=\"btn btn-primary bg-blue\" value=\"Proses Penetapan Mundur\" name=\"btn-penetapan-mundur\" id=\"btn-penetapan-mundur\" onClick=\"return actionPenetapanMundur('" . $PenilaianParam . "', '0','" . $uid . "');\"  disabled=\"disabled\" >\n";
        }
        echo '<br><br><div id="monitoring-content-5" class="monitoring-content"></div></div>';
    } else if ($selected == 70) {
        echo "<script>
        $(document).ready(function() {
                listPrinter();
            });    </script>
        ";
        echo "<button class=\"btn btn-primary btn-orange\" type=\"button\" value=\"Pratinjau SPPT\" name=\"btn-print-preview\"/ onclick=\"javascript:printpreviewdata()\">Pratinjau SPPT</button>&nbsp;&nbsp;\n";
        echo "<button class=\"btn btn-primary btn-blue\" type=\"button\" value=\"Cetak PDF\" name=\"btn-print-pdf\"/ onclick=\"javascript:exportPDF()\">Cetak PDF</button>&nbsp;&nbsp;\n";
        // echo "<button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Cetak PDF Baru\" name=\"btn-print-pdf-new\"/ onclick=\"javascript:exportPDF(true)\">Cetak PDF Baru</button>&nbsp;&nbsp;\n";
        // echo "<button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Pratinjau SPPT Double\" name=\"btn-print-preview\"/ onclick=\"javascript:printpreviewdataDouble()\">Pratinjau SPPT Double</button>&nbsp;&nbsp;\n";
        echo "<button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Cetak\" name=\"btn-print\"/ onclick=\"javascript:printdata()\">Cetak</button>&nbsp;&nbsp;\n";
        echo "<button class=\"btn btn-primary btn-orange\" type=\"button\" value=\"STTS\" name=\"btn-print-stts\"/ onclick=\"javascript:printsttsdata()\">STTS</button>&nbsp;&nbsp;\n";
        echo "<button class=\"btn btn-primary btn-blue\" type=\"button\" value=\"DHKP\" name=\"btn-print-dhkp\"/ onclick=\"javascript:printdhkpdata()\">Cetak DHKP</button>&nbsp;&nbsp;\n";
        // echo "<button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Cetak Double\" name=\"btn-print-double\"/ onclick=\"javascript:printdataDouble()\">Cetak Double</button>&nbsp;&nbsp;\n";
        $printerList = explode(';', $appConfig['PRINTER_NAME']);

        echo "<div style=\"display:inline\"><span>Printer</span>&nbsp;";
        //$uid = $data->uid;
        echo "<select class=\"form-control\" name=\"selectedPrinter\" id=\"selectedPrinter\"  style=\"width:150px;display:inherit\" onchange=\"changePrinter($('#selectedPrinter').val(), '$uid', '$m');\">";
        /*foreach($printerList as $name){
            echo "<option value=\"".$name."\" ".(($name == $_SESSION['printerName'])? "selected=selected":"").">".$name."</option>";
        }*/
        echo "</select></div>";
        echo "&nbsp;&nbsp;&nbsp;\n";
        echo "<div style=\"display:inline\"><span>Tahun</span>&nbsp;";
        //$dbGwCurrent->getYearList('2014');
        echo "<select class=\"form-control\" name=\"tahun\" id=\"tahun\"  style=\"width:150px;display:inherit\" onchange=\"setTabs(" . $selected . "," . $selected . ")\">";
        $sql = "SELECT REPLACE(table_name,'cppmod_pbb_sppt_cetak_','') as `table` FROM information_schema.tables WHERE `table_name` LIKE 'cppmod_pbb_sppt_cetak%' ORDER BY 1 DESC";
        // echo $sql;
        $result = mysqli_query(true, $sql);
        // echo "<option value='".date('Y')."'>".date('Y')."</option>";
        echo "<option value='" . $appConfig['tahun_tagihan'] . "'>" . $appConfig['tahun_tagihan'] . "</option>";
        while ($r = mysqli_fetch_array($result)) {
            if ($r[0] == $tahun) $selected70 = 'selected';
            else $selected70 = '';

            echo "<option value='$r[0]' $selected70>$r[0]</option>";
        }
        echo "</select>";

        echo "&nbsp;&nbsp;&nbsp;\n";
    } else if ($selected == 80) { //Tab Tertunda Penilaian Massal
        echo '<div class="row" style="margin-top: 15px; margin-bottom: 15px;">';
        if ($isSusulan) {
            echo "<div class=\"col-md-2\"><input type=\"button\" class=\"btn btn-primary bg-orange\" value=\"Proses Penilaian\" id=\"btn-penilaian\" name=\"btn-penilaian\"/ onClick=\"return actionPenilaian('" . $PenilaianParam . "', '" . $appConfig['tahun_tagihan'] . "', '2', '1');\"></div>";
        } else {
            if ($kel != '') {
                $hasilKalibrasi = $dbUtils->checkKalibrasi($kel, $appConfig['tahun_tagihan']);
                $namaKel = $dbUtils->getKelurahanNama($kel);
                if ($hasilKalibrasi == 1) {
                    echo "<script type=\"text/javascript\">
                    $(document).ready(function() {
                        alert('Penilaian massal sudah dilakukan untuk kelurahan " . $namaKel . " tahun " . $appConfig['tahun_tagihan'] . " ');
                    });    
                    </script>";
                } else if ($hasilKalibrasi == -1) {
                    echo "<script type=\"text/javascript\">
                    $(document).ready(function() {
                        alert('Terjadi kegagalan pada saat mengambil data penilaian massal');
                    });    
                    </script>";
                }

                echo "<div class=\"col-md-2\"><input type=\"button\" class=\"btn btn-primary bg-orange\" value=\"Proses Penilaian\" id=\"btn-penilaian\" name=\"btn-penilaian\"/ onClick=\"return actionPenilaian('" . $PenilaianParam . "', '" . $appConfig['tahun_tagihan'] . "', '1', '0');\"></div>\n";
            } else echo "<div class=\"col-md-2\"><input type=\"button\" class=\"btn btn-primary bg-orange\" value=\"Proses Penilaian\" id=\"btn-penilaian\" name=\"btn-penilaian\"/ onClick=\"return actionPenilaian('" . $PenilaianParam . "', '" . $appConfig['tahun_tagihan'] . "', '1', '0');\" disabled=\"disabled\"></div>\n";
        }

        echo '<div class="col-md-1" style="padding-top: 7px;">Tampilkan</div>
            <div class="col-md-1">
                <select id="tampilkan_data" class="form-control" name="tampilkan_data" onchange="displayDat(\'' . $selected . '\', this)">
                    <option value="25" ' . ((!isset($displayDat) || empty($displayDat) || $displayDat == 25) ? 'selected' : '') . '>25</option>
                    <option value="50" ' . ((isset($displayDat) && $displayDat == 50) ? 'selected' : '') . '>50</option>
                    <option value="100" ' . ((isset($displayDat) && $displayDat == 100) ? 'selected' : '') . '>100</option>
                    <option value="200" ' . ((isset($displayDat) && $displayDat == 200) ? 'selected' : '') . '>200</option>
                    <option value="300" ' . ((isset($displayDat) && $displayDat == 300) ? 'selected' : '') . '>300</option>
                    <option value="400" ' . ((isset($displayDat) && $displayDat == 400) ? 'selected' : '') . '>400</option>
                    <option value="500" ' . ((isset($displayDat) && $displayDat == 500) ? 'selected' : '') . '>500</option>
                </select>
            </div>
        </div>';
    } else if ($selected == 81) { //Fungsi Cetak SK
        // echo "<input type=\"text\" name=\"noSK\" id=\"noSK\" placeholder=\"Nomor SK NJOP\">&nbsp;<input type=\"button\" value=\"Cetak\" id=\"btn-cetak\" name=\"btn-cetak\"/ onClick=\"return printSK();\">&nbsp;&nbsp;";
        echo '<div class="row"><div class="col-md-2">';
        echo "<button id=\"btn-cetak-prev\" class=\"btn btn-primary btn-orange mb5\" name=\"btn-cetak-prev\"/ onClick=\"return printSKPrev();\">Preview</button> ";
        echo "<button id=\"btn-cetak\" class=\"btn btn-primary btn-blue mb5\" name=\"btn-cetak\"/ onClick=\"return printSK();\">Cetak</button>";
        echo '</div></div>';
    } else if ($selected == 82) { //Fungsi Cetak SK
        echo '<div class="row"><div class="col-md-2">';
        echo "<button class=\"btn btn-primary btn-orange mb5\" id=\"btn-cetak-prev\" name=\"btn-cetak-prev\"/ onClick=\"return printSKPrevMasal();\">Preview</button> ";
        echo "<button class=\"btn btn-primary btn-blue mb5\" id=\"btn-cetak\" name=\"btn-cetak\"/ onClick=\"return printSKMasal();\">Cetak</button>";
        echo '</div></div>';
    } else if ($selected == 5) {
        echo "<input type=\"submit\" class=\"btn btn-primary btn-orange mb5\" value=\"Kembalikan ke Loket\" name=\"btn-backtoloket\"/ onClick=\"return confirm('Anda yakin akan mengirim data ini ke loket pelayanan?')\">\n";
    }

    if ($selected == 24 || $selected == 70 || $selected == 82) {
        if ($selected == 70) echo "<br><br>";
        echo '<div class="row" style="margin-top: 10px"><div class="col-md-4">';
        echo " &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;<input type=\"radio\" name=\"tipeFilter\" id=\"single\" value=\"single\"> Filter Kelurahan &nbsp;&nbsp;&nbsp;";
        echo "<input type=\"radio\" name=\"tipeFilter\" id=\"multi\" value=\"multi\"> Filter Multiple NOP<br>";
        echo '</div></div>';
        echo "<span id=\"multiFilter\">";
        echo '<div class="row"><div class="col-md-4">';
        echo "<div class=\"form-group\" style=\"margin-left:20px;margin-top:10px\">
				<label>Masukan NOP </label>
				<textarea class=\"form-control\" maxlength=\"1900\" rows=\"5\" id=\"daftarNOP\"></textarea>
                <br><font size=\"-4\">Gunakan koma (,) untuk pemisah</font>
                </div></div>
                <div class=\"col-md-2\">
                    <button class=\"btn btn-primary btn-orange mb5\" style=\"margin-top: 35px;\" onclick=\"searchMultiNOP(" . $selected . "," . $selected . ")\" id=\"btn-src\">Cari</button>
                </div></div>";
        echo "</span>";
    }
    if ($selected == 26) {
        echo "";
    }

    if ($selected != 26) {
        // if($selected == 82){
        //     echo "<br><br><input type=\"radio\" name=\"tipeFilter\" id=\"single\" value=\"single\"> Filter Kelurahan ";
        //     echo "<input type=\"radio\" name=\"tipeFilter\" id=\"multi\" value=\"multi\"> Filter Multiple NOP<br>";
        //     echo "<span id=\"multiFilter\">";
        //     echo "<table border=\"0\">
        //             <tr>
        //                 <td valign=\"top\">Masukan NOP </td>
        //                 <td valign=\"top\"><textarea maxlength=\"1900\" rows=\"5\" id=\"daftarNOP\"></textarea>
        //                 <br><font size=\"-4\">Gunakan koma (,) untuk pemisah</font></td>
        //                 <td valign=\"top\"><input type=\"button\" onclick=\"searchMultiNOP(".$selected.",".$selected.")\" value=\"Cari\" id=\"btn-src\"/></td>
        //             </tr>";
        //     echo "</table></span>";
        //     echo "<span id=\"singleFilter\">";
        // }

        // echo "\t<tr>\n";
        // }else{
        echo "<div class=\"row\" style=\"margin-top: 10px\">";

        if ($selected == "82") {
            echo "<span id=\"singleFilter\">";
        }
        
        echo "  
                <style>
                    .form-filtering-penetapan {
                        background-color: #fff;
                        margin:  20px;
                        padding: 20px 20px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

                    }
                </style>
                 <div class=\"col-md-12\" style=\"margin-bottom: 20px\">
                    <div class=\"row\">
                        <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\">
                            <button class=\"btn btn-primary\" type=\"button\" style=\"border-radius:8px; margin-right:30px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$selected}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-$selected\">
                                Filter Data
                            </button>
                        </div>

                        <div class=\"col-12\"> 
                            <div class=\"collapse\" id=\"collapsFilter-$selected\">
                                <div class=\"form-filtering-penetapan\">
                                    <div class=\"row \">
                                        <div class=\"form-group col-md-3\" >
                                            <label>NOP / Nama</label>
                                            <div style=\"display: flex; align-items: center;\">
                                            <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $selected . "," . $selected . ");\" id=\"srch-" . $selected . "\" name=\"srch-" . $selected . "\" placeholder=\"NOP/Nama\" value=\"" . $srch . "\"/>
                                            
                                            <button type=\"button\" class=\"btn btn-primary mb5\" style=\"margin-top: 4px; margin-left:10px\" onclick=\"setTabs(" . $selected . "," . $selected . ")\" id=\"btn-src\">Cari</button>
                                        </div>
                                        
                                        </div>
                                    
                                        <div class=\"form-group col-md-3\" >
                                            <label>Kecamatan</label>
                                            <select name=\"kec\" class=\"form-control\" id=\"kec\" onchange=\"showKel(this)\">
                                                <option value=\"\">Kecamatan</option>";
                                                if ($selected == 21) {
                                                    $datKec = $aKecamatanPendataan;
                                                    $datKel = $aKelurahanPendataan;
                                                } else {
                                                    $datKec = $aKecamatan;
                                                    $datKel = $aKelurahan;
                                                }
                                        
                                                foreach ($datKec as $row) {
                                                    $digit3 = substr($row['CPC_TKC_ID'], 4, 3);
                                                    echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . " - " . $digit3 . "</option>";
                                                }
                                                echo "  </select>
                                        </div>


                                        <div class=\"form-group col-md-3\">
                                            <label>Kelurahan</label>
                                            <div id=\"sKel" . $selected . "\" >
                                                <select name=\"kel\" id=\"kel\" class=\"form-control kel{$selected}\" onchange=\"filKel(" . $selected . ",this)\">
                                            
                                                    <option value=\"\">" . $appConfig['LABEL_KELURAHAN'] . "</option>";
                                                        if ($kel) {
                                                            foreach ($datKec as $row) {
                                                                if ($kec == $row['CPC_TKC_ID']) {
                                                                    foreach ($datKel as $row2) {
                                                                        if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                            echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        
                                                echo " </select>
                                            </div>
                                        </div>

                                        <div class=\"form-group col-md-3\" >
                                            <label>Buku</label>
                                            <select id=\"buku\" name=\"buku\" class=\"form-control buku{$selected}\" onchange=\"filBook(" . $selected . ",this)\">
                                                <option value=\"0\" " . ((isset($buku) && $buku == "0") ? "selected" : "") . ">Pilih Semua</option>
                                                <option value=\"1\" " . ((isset($buku) && $buku == "1") ? "selected" : "") . ">Buku 1</option>
                                                <option value=\"12\" " . ((isset($buku) && $buku == "12") ? "selected" : "") . ">Buku 1,2</option>
                                                <option value=\"123\" " . ((isset($buku) && $buku == "123") ? "selected" : "") . ">Buku 1,2,3</option>
                                                <option value=\"1234\" " . ((isset($buku) && $buku == "1234") ? "selected" : "") . ">Buku 1,2,3,4</option>
                                                <option value=\"12345\" " . ((isset($buku) && $buku == "12345") ? "selected" : "") . ">Buku 1,2,3,4,5</option>
                                                <option value=\"2\" " . ((isset($buku) && $buku == "2") ? "selected" : "") . ">Buku 2</option>
                                                <option value=\"23\" " . ((isset($buku) && $buku == "23") ? "selected" : "") . ">Buku 2,3</option>
                                                <option value=\"234\" " . ((isset($buku) && $buku == "234") ? "selected" : "") . ">Buku 2,3,4</option>
                                                <option value=\"2345\" " . ((isset($buku) && $buku == "2345") ? "selected" : "") . ">Buku 2,3,4,5</option>
                                                <option value=\"3\" " . ((isset($buku) && $buku == "3") ? "selected" : "") . ">Buku 3</option>
                                                <option value=\"34\" " . ((isset($buku) && $buku == "34") ? "selected" : "") . ">Buku 3,4</option>
                                                <option value=\"345\" " . ((isset($buku) && $buku == "345") ? "selected" : "") . ">Buku 3,4,5</option>
                                                <option value=\"4\" " . ((isset($buku) && $buku == "4") ? "selected" : "") . ">Buku 4</option>
                                                <option value=\"45\" " . ((isset($buku) && $buku == "45") ? "selected" : "") . ">Buku 4,5</option>
                                                <option value=\"5\" " . ((isset($buku) && $buku == "5") ? "selected" : "") . ">Buku 5</option>
                                            </select>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> 
                      
                    </div>
                </div>";
        if ($selected == "82") {
            echo "</span>";
        }
        if ($m == $appConfig['id_mdl_penetapan']) {
            if ($selected != 24) {
                echo "&nbsp;&nbsp;";
            } #ardi

            echo "<div class=\"col-md-12\" style=\"margin-bottom: 10px;\"><button class=\"btn btn-primary bg-maka\" type=\"button\" value=\"Proses Penetapan Terseleksi\" name=\"btn-penetapan\"/ onClick=\"return actionPenetapanTerseleksi('" . $PenilaianParam . "', '" . $appConfig['tahun_tagihan'] . "', '1','" . $uname . "');\">Proses Penetapan Terseleksi</button></div>"; #35utech 

            // echo "<input type=\"button\" value=\"Proses Penetapan Terseleksi\" name=\"btn-penetapan\"/ onClick=\"return actionPenetapanTerseleksi('".$PenilaianParam."', '".$appConfig['tahun_tagihan']."', '1');\">\n"; #ardi					
        }

        //echo "\t</td><td align=\"right\">";
        // echo "</td></tr></table>";
        echo "<div class=\"col-md-12\"><div class=\"table-responsive\"><table class=\"table table-bordered\">\n";
        echo "\t<tr>\n";
    }
    if ($selected == 10 || ($selected == 24) || /*($selected == 80 && $isSusulan)*/ $selected == 80 || $selected == 81 || $selected == 70) {
        echo "\t\t<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button\" type=\"checkbox\"/></td>\n";
    } else if ($selected == 50 && $isSusulan) {
        echo "\t\t<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button-tab-ditetapkan\" type=\"checkbox\"/></td>\n";
    } else if ($selected == 82) {
        echo "\t\t<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button-masal\" type=\"checkbox\"/></td>\n";
    } else if ($selected == 5) {
        echo "\t\t<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button-tertunda\" type=\"checkbox\"/></td>\n";
    } else if ($selected == 26) {
        echo "";
    } else {
        echo "\t\t<td width=\"20\" class=\"tdheader\">&nbsp;</td>\n";
    }
    echo createHeader($selected);
    echo "\t</tr>\n";
    echo printData($selected);
    echo "</table>\n";
    echo "\t</div>\n";

    if ($selected != 26) {
        echo "\t<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\">\n";
        echo "\t\t</div>\n";
        echo "\t\t<div style=\"float:right; color: #000;\">" . paging() . "</div>\n";
    }
    echo "\t</div>\n";
    echo "</div>\n";
    echo "</form>\n";
}

function createHeader($selected)
{
    global $appConfig;
    $header = null;
    //variable header set
    $hBasic =
        "\t\t<td class=\"tdheader\"> NOP </td> \n
        \t\t<td class=\"tdheader\"> Nama </td> \n
        \t\t<td class=\"tdheader\"> Alamat Wajib Pajak </td> \n
        \t\t<td class=\"tdheader\"> Alamat Objek Pajak </td> \n";

    $hTolak =
        "\t\t<td class=\"tdheader\"> Ditolak di </td> \n
        \t\t<td class=\"tdheader\"> Alasan </td> \n";

    $hVerifikasi =
        "\t\t<td class=\"tdheader\"> Status Verifikasi </td> \n";

    $hAdv =
        "\t\t<td class=\"tdheader\"> Kecamatan - " . $appConfig['LABEL_KELURAHAN'] . "</td> \n
        \t\t<td class=\"tdheader\"> ID Pendata </td> \n";

    $hService =
        "\t\t<td class=\"tdheader\"> No Penerimaan </td> \n
        \t\t<td class=\"tdheader\"> NOP Induk </td> \n
        \t\t<td class=\"tdheader\"> Nama WP </td> \n
        \t\t<td class=\"tdheader\"> Alamat Wajib Pajak </td> \n
        \t\t<td class=\"tdheader\"> Alamat Objek Pajak </td> \n
        \t\t<td class=\"tdheader\">Kecamatan - " . $appConfig['LABEL_KELURAHAN'] . "</td> \n
        \t\t<td class=\"tdheader\"> Jenis Berkas </td> \n
        ";

    if ($selected != 26) {
        $header = $hBasic;
    }
    switch ($selected) {
        case 5:
            $header = $hService;
            break;
        case 10:
            break;
        case 20:
        case 21:
        case 22:
        case 24:
        case 25:
            $header .= $hAdv . $hVerifikasi;
            break;
        case 30:
            $header .= $hTolak;
            break;
        case 31:
        case 32:
        case 35:
            $header .= $hAdv . $hTolak;
            break;
        case 24:
        case 41:
        case 42:
        case 45:
        case 50:
            $header .= $hAdv;
            break;
        case 60:
            $header .= "\t\t<td class=\"tdheader\"> NJKP </td> \n";
            $header .= "\t\t<td class=\"tdheader\">Kecamatan - " . $appConfig['LABEL_KELURAHAN'] . "</td> \n";
            break;
        case 70:
            $header .= "\t\t<td class=\"tdheader\"> NJKP </td> \n";
            $header .= "\t\t<td class=\"tdheader\">Kecamatan - " . $appConfig['LABEL_KELURAHAN'] . "</td> \n";
            $header .= "\t\t<td class=\"tdheader\">Tahun Pajak</td> \n";
            break;
        case 80:
        case 81:
            $header .= "\t\t<td class=\"tdheader\">Kecamatan - " . $appConfig['LABEL_KELURAHAN'] . "</td> \n";
            $header .= "\t\t<td class=\"tdheader\"> Total NJOP (Rp)</td> \n";
            break;
        case 82:
            $header .= "\t\t<td class=\"tdheader\">Kecamatan - " . $appConfig['LABEL_KELURAHAN'] . "</td> \n";
            $header .= "\t\t<td class=\"tdheader\"> Total NJOP (Rp)</td> \n";
            break;
    }

    return $header;
}

function printData($selected)
{
    global $isSusulan;

    $HTML = "";
    $aData = getData($selected);
    //var_dump($aData);exit;

    $i = 0;

    if ($aData != null && !empty($aData) && count($aData) > 0)
        foreach ($aData as $data) {
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($selected != 26) {
                $HTML .= "\t<tr>\n";
            }
            if ($selected == 10 || $selected == 42) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . (isset($data['CPM_TRAN_ID']) ? $data['CPM_TRAN_ID'] : '') . "\" /></td>\n";
            } else if (($selected == 60 && $data['FLAG'] == 2) || $selected == 70) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['NOP'] . "\" /></td>\n";
            } else if ($selected == 24) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
            } else if ((($selected == 80) /*&& $isSusulan*/) || ($selected == 81)) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
            } else if (($selected == 82)) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all-masal[]\" class=\"check-all-masal\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
            } else if ($selected == 50 && $isSusulan) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_NOP'] . "\" /></td>\n";
            } else if ($selected == 5) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all-tertunda[]\" class=\"check-all-tertunda\" type=\"checkbox\" value=\"" . $data['CPM_ID'] . "\" /></td>\n";
            } else if ($selected == 26) {
                $HTML .= "";
            } else {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">&nbsp;</td>\n";
            }
            if ($selected != 26) {
                $HTML .= parseData($data, $selected, $class);

                $HTML .= "\t</tr>\n";
            }
            $i++;
        }
    return $HTML;
}

function getData($selected)
{
    global $dbSpptTran, $dbFinalSppt, $dbGwCurrent, $dbServices, $srch, $arConfig, $appConfig, $isSusulan,
        $data, $kec, $kel, $buku, $custom, $jumlah, $displayDat, $totalrows, $perpage, $page, $dbUtils, $tahun, $daftarNOP;
    // echo $daftarNOP;
    $filter['CPM_TRAN_FLAG'] = 0;

    // for new version
    if ($selected == 10) {
        //show data with status = 0
        $filter['CPM_TRAN_STATUS'][] = 0;
    } else if ($selected == 20) {
        $filter['CPM_TRAN_STATUS'][] = 1;
        $filter['CPM_TRAN_STATUS'][] = 2;
        $filter['CPM_TRAN_STATUS'][] = 3;
        //$filter['CPM_TRAN_STATUS'][] = 4;
    } else if ($selected == 21) {
        $filter['CPM_TRAN_STATUS'][] = 1;
    } else if ($selected == 22) {
        $filter['CPM_TRAN_STATUS'][] = 2;
    } else if ($selected == 24) {
        $filter['CPM_TRAN_STATUS'][] = 1;
        $filter['CPM_TRAN_STATUS'][] = 2;
        $filter['CPM_TRAN_STATUS'][] = 3;
        //$filter['CPM_TRAN_STATUS'][] = 4;
        //$filter['CPM_TRAN_STATUS'][] = 5;// reserved buat tab "tertunda" modul "penetapan"
        #Verifikasi III >> Tab Tertunda
    } else if ($selected == 25) {
        $filter['CPM_TRAN_STATUS'][] = 3;
    } else if ($selected == 30) {
        $filter['CPM_TRAN_STATUS'][] = 6;
        $filter['CPM_TRAN_STATUS'][] = 7;
        $filter['CPM_TRAN_STATUS'][] = 8;
    } else if ($selected == 31) {
        $filter['CPM_TRAN_STATUS'][] = 6;
        $filter['CPM_TRAN_STATUS'][] = 7;
        $filter['CPM_TRAN_STATUS'][] = 8;
        $filter['CPM_TRAN_STATUS'][] = 9;
    } else if ($selected == 32) {
        if ($arConfig['usertype'] == "dispenda")
            $filter['CPM_TRAN_STATUS'][] = 7;
        $filter['CPM_TRAN_STATUS'][] = 8;
        $filter['CPM_TRAN_STATUS'][] = 9;
        #Verifikasi III >> Tab Ditolak
    } else if ($selected == 35) {
        if ($arConfig['usertype'] == "dispenda2")
            $filter['CPM_TRAN_STATUS'][] = 8;
    } else if ($selected == 40) {
        $filter['CPM_TRAN_STATUS'][] = 4;
    } else if ($selected == 41) {
        $filter['CPM_TRAN_STATUS'][] = 2;
    } else if ($selected == 42) {
        if ($arConfig['usertype'] == "dispenda") {
            $filter['CPM_TRAN_STATUS'][] = 3;
        } else {
            $filter['CPM_TRAN_STATUS'][] = 4;
        }
        #Verifikasi III >> Tab Disetujui
    } else if ($selected == 45) {
        if ($arConfig['usertype'] == "dispenda2") {
            $filter['CPM_TRAN_STATUS'][] = 4;
        }
    } else if ($selected == 50) {
        $filter['CPM_TRAN_STATUS'][] = 4; // reserved buat tab "telah ditetapkan" modul "penetapan"
    }

    $qBuku = null;

    if ($buku != 0) {
        switch ($buku) {
            case 1:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000 ";
                break;
            case 12:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000 ";
                break;
            case 123:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
                break;
            case 1234:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
                break;
            case 12345:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
                break;
            case 2:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000 ";
                break;
            case 23:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
                break;
            case 234:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
                break;
            case 2345:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
                break;
            case 3:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
                break;
            case 34:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
                break;
            case 345:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
                break;
            case 4:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
                break;
            case 45:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
                break;
            case 5:
                $qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
                break;
        }
    }

    //Jika ada keyword pencarian
    if ($srch) {
        $custom = "(CPM_NOP LIKE '%$srch%' OR CPM_WP_NAMA LIKE '%$srch%')";
    }

    $perpage = $appConfig['ITEM_PER_PAGE'];
    $uid = $data->uid;
    $uname  = $data->uname;
    //die(var_dump($arConfig['usertype']));
    //$filter = array();
    if ($selected == 5) {
        $filter = array();
        $filter['CPM_STATUS'][] = 1;
        $filter['CPM_TYPE'][] = 1;
        $filter['CPM_TYPE'][] = 2;
        $additionalWhereQuery = " AND ( CPM_ID NOT IN 
			(SELECT CPM_NEW_SID AS SERVICE_ID FROM cppmod_pbb_service_new_op
			UNION ALL
			SELECT CPM_SP_SID AS SERVICE_ID FROM cppmod_pbb_service_split)
			)";

        if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;

        $data = $dbServices->get($filter, $srch, $jumlah, $perpage, $page, $additionalWhereQuery);
        //var_dump($filter);/*var_dump($srch);var_dump($jumlah);var_dump($perpage);var_dump($page);var_dump($additionalWhereQuery);*/exit();
        $totalrows = $dbServices->totalrows;
    } else if ($selected == 60 || $selected == 70) {
        $filter = array();
        //        $kelName = $dbUtils->getKelurahanNama($kel);
        //        if($kelName) $filter['OP_KELURAHAN'] = $kelName;
        if ($kel) $filter['OP_KELURAHAN_KODE'] = $kel;

        if (!$tahun) $tahun = $appConfig['tahun_tagihan'];
        // $tahun = date('Y');

        if ($selected == 60) {
            $data = $dbGwCurrent->gets($filter, $srch, $jumlah, $perpage, $page);
            // die(var_dump($data));
        } else if ($selected == 70) {
            if ($daftarNOP) {
                $filter['NOP'] = trim($daftarNOP);
            }
            $data = $dbGwCurrent->get70s($filter, $srch, $qBuku, $jumlah, $perpage, $page, $tahun, $appConfig);
        }
        $totalrows = $dbGwCurrent->totalrows;
    } else if ($selected == 24 && $arConfig['usertype'] == "pejabatdispenda") {
        if (empty($displayDat)) {
            $displayDat = 25;
        }

        if ($isSusulan) {
            $filter = array();
            $filter['CPM_SPPT_THN_PENETAPAN !'] = $appConfig['tahun_tagihan'];

            if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;
            // echo "masuk";
            $data = $dbFinalSppt->get_susulan($filter, $srch, $jumlah, $displayDat, $page);
            $totalrows = $dbFinalSppt->totalrows;
        } else {
            $filter = array();
            $filter['CPM_SPPT_THN_PENETAPAN !'] = $appConfig['tahun_tagihan'];

            if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;

            if ($daftarNOP) {
                // var_dump($daftarNOP);
                $filter['CPM_NOP'] = trim($daftarNOP);
            }

            $filter['CPM_SPPT_TEMP_PENETAPAN_STATUS'] = 0;

            $data = $dbFinalSppt->get_where($filter, $srch, $jumlah, $displayDat, $page);
            $totalrows = $dbFinalSppt->totalrows;
            /*if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;

            $data = $dbSpptTran->getDetail("", $filter, $custom, $jumlah, $perpage, $page);
            $totalrows = $dbSpptTran->totalrows;*/
        }
    } else if ($selected == 26 && $arConfig['usertype'] == "pejabatdispenda2") {
    } else if ($selected == 50 && $arConfig['usertype'] == "pejabatdispenda") {
        $filter = array();
        if ($isSusulan) {
            $filter['CPM_SPPT_THN_PENETAPAN '] = $appConfig['tahun_tagihan'];
            if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;

            $data = $dbFinalSppt->get_susulan($filter, $srch, $jumlah, $perpage, $page);
        } else {
            $filter['CPM_SPPT_THN_PENETAPAN '] = $appConfig['tahun_tagihan'];
            if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;
            $filter['CPM_SPPT_TEMP_PENETAPAN_STATUS'] = 1;
            $data = $dbFinalSppt->get_where($filter, $srch, $jumlah, $perpage, $page);
        }
        $totalrows = $dbFinalSppt->totalrows;
    } else if ($selected == 80 && $arConfig['usertype'] == "dispenda-penilaian") {
        if (empty($displayDat)) {
            $displayDat = 25;
        }

        if ($isSusulan) {
            $filter = array();

            if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;

            $data = $dbFinalSppt->get_susulan($filter, $srch, $jumlah, $displayDat, $page);
            $totalrows = $dbFinalSppt->totalrows;
        } else {
            $filter = array();

            if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;

            $data = $dbFinalSppt->get_where($filter, $srch, $jumlah, $displayDat, $page);
            $totalrows = $dbFinalSppt->totalrows;
        }
    } else if ($selected == 81) {

        $filter = array();

        if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;


        if (count($filter) > 0 || $srch != "") {
            $data = $dbFinalSppt->get_susulan($filter, $srch, $jumlah, $perpage, $page);
        } else {
            $data = "";
        }

        $totalrows = $dbFinalSppt->totalrows;
    } else if ($selected == 82) {


        $filter = array();
        if ($daftarNOP) {
            // var_dump($daftarNOP);
            $filter['CPM_NOP'] = trim($daftarNOP);
        }

        if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;

        // var_dump($filter);exit;
        if (count($filter) > 0 || $srch != "") {
            // echo "mantap";
            $data = $dbFinalSppt->get_where_finalsusulan($filter, $srch, $jumlah, $perpage, $page);
        } else {
            // echo "mantap 2";
            $data = "";
        }
        // var_dump($data);

        $totalrows = $dbFinalSppt->totalrows;
    } else if ($selected == 65) {
        $filter = array();
        $filter['CPM_OT_JENIS'] = '4';
        $filter['CPM_SPPT_THN_PENETAPAN !'] = $appConfig['tahun_tagihan'];
        if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;
        //$filter['CPM_SPPT_TEMP_PENETAPAN_STATUS'] = 1;
        $data = $dbFinalSppt->get_fasilitas_umum($filter, $srch, $jumlah, $perpage, $page);
        $totalrows = $dbFinalSppt->totalrows;
    } else {
        // echo "masuk";
        if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;

        $data = $dbSpptTran->getDetail("", $filter, $custom, $jumlah, $perpage, $page);
        $totalrows = $dbSpptTran->totalrows;
    }

    return $data;
}

function kecShow($kode)
{
    global $dbSpec;
    $dbUtils = new DbUtils($dbSpec);
    return $dbUtils->getKecamatanNama($kode);
}
function kelShow($kode)
{
    global $dbSpec;
    $dbUtils = new DbUtils($dbSpec);
    return $dbUtils->getKelurahanNama($kode);
}

function parseData($data, $selected, $class)
{
    // print_r($)
    // var_dump($selected);

    global $arConfig, $appConfig, $a, $m;

    $bSlash = "\'";
    $ktip = "'";
    $parse = null;

    //sedikit penambahan untuk tampilan pejabat dispenda
    if (($selected == 24 || $selected == 50) && $arConfig['usertype'] == "pejabatdispenda") {
        if ($data['CPM_SPPT_THN_PENETAPAN'] < $appConfig['tahun_tagihan'])
            $data['CPM_TRAN_STATUS'] = 4;
        else
            $data['CPM_TRAN_STATUS'] = 4;

        $data['CPM_TRAN_INFO'] = "";
    }

    //menentukan jenis tampilan, form input atau view biasa
    if ($selected != 60 && $selected != 70) {
        if (($selected == 24 || $selected == 50 || $selected == 65) && $arConfig['usertype'] == "pejabatdispenda") {
            $params = "a=$a&m=$m&f=" . $arConfig['id_view_spop'] . "&idd=" . $data['CPM_SPPT_DOC_ID'] . "&v=" . $data['CPM_SPPT_DOC_VERSION'];
        } else if ($selected == 80 && $arConfig['usertype'] == "dispenda-penilai") {
            $params = "a=$a&m=$m&f=" . $arConfig['id_view_spop'] . "&idd=" . $data['CPM_SPPT_DOC_ID'] . "&v=" . $data['CPM_SPPT_DOC_VERSION'];
        } else if ($selected == 5) {
            if ($data['CPM_TYPE'] == '1')
                $params = "a=$a&m=$m&f=" . $arConfig['id_spop'] . "&idServices=" . $data['CPM_ID'];
            else $params = "a=$a&m=$m&f=" . $arConfig['id_spop'] . "&idServices=" . $data['CPM_ID'] . "&NOP_INDUK=" . $data['CPM_OP_NUMBER'];
        } else {
            $params = "a=$a&m=$m&f=" . $arConfig['id_view_spop'] . "&idt=" . (isset($data['CPM_TRAN_ID']) ? $data['CPM_TRAN_ID'] : '');
            if ($selected != 20 && (
                ($arConfig["usertype"] == "consol" && ($data['CPM_TRAN_STATUS'] == "0" || $data['CPM_TRAN_STATUS'] == "6" || $data['CPM_TRAN_STATUS'] == "7" || $data['CPM_TRAN_STATUS'] == "8")) ||
                ($arConfig["usertype"] == "kelurahan" && $data['CPM_TRAN_STATUS'] == "1"))) {
                $params = "a=$a&m=$m&f=" . $arConfig['id_spop'] . "&idt=" . $data['CPM_TRAN_ID'];
            }
        }
        #Before
        /*
        $status = array(
            0 => "Draft",
            1 => "Verifikasi I",
            2 => "Verifikasi II",
            3 => "Penetapan",
            4 => "Finalisasi",
            5 => "Verifikasi I",
            6 => "Verifikasi II",
            7 => "Penetapan",
            8 => "Belum Ditetapkan",
            9 => "Sudah Ditetapkan"
        );
		*/
        #After
        $status = array(
            0 => "Draft",
            1 => "Verifikasi I",
            2 => "Verifikasi II",
            3 => "Verifikasi III",
            4 => "Penetapan",
            5 => "Finalisasi",
            6 => "Verifikasi I",
            7 => "Verifikasi II",
            8 => "Verifikasi III",
            9 => "Penetapan"
        );

        /*$dBasic = '<td class="' . $class . '"><a href="main.php?param=' . base64_encode($params) . '">' . isset($data['CPM_NOP']) ? $data['CPM_NOP'] : '' . '</a> </td>
        <td class="' . $class . '"> ' . $data['CPM_WP_NAMA'] . '</td>
        <td class="' . $class . '"> ' . $data['CPM_WP_ALAMAT'] . '</td>
        <td class="' . $class . '"> ' . $data['CPM_OP_ALAMAT'] . ' ' . $data['CPM_OP_NOMOR'] . ' </td>';*/

        $dBasic =
            "\t\t<td class=\"$class\"><a href=\"main.php?param=" . base64_encode($params) . "\">" . (isset($data['CPM_NOP']) ? $data['CPM_NOP'] : '') . " </a></td> \n
            <td class=\"$class\">" . (isset($data['CPM_WP_NAMA']) ? $data['CPM_WP_NAMA'] : '') . " </td> \n
            <td class=\"$class\">" . (isset($data['CPM_WP_ALAMAT']) ? $data['CPM_WP_ALAMAT'] : '') . " </td> \n
            <td class=\"$class\">" . (isset($data['CPM_OP_ALAMAT']) ? $data['CPM_OP_ALAMAT'] : '') . " " . (isset($data['CPM_OP_NOMOR']) ? $data['CPM_OP_NOMOR'] : '') . " </td> \n";

        $dTolak =
            "\t\t<td class=\"$class\"> " . (isset($data['CPM_TRAN_STATUS']) && isset($status[$data['CPM_TRAN_STATUS']]) ? $status[$data['CPM_TRAN_STATUS']] : '') . " </td> \n
        \t\t<td class=\"$class\"> " . ((isset($data['CPM_TRAN_INFO']) && strlen($data['CPM_TRAN_INFO']) > 25) ? "<label class=\"tipclass\" title=\"" . (isset($data['CPM_TRAN_INFO']) ? $data['CPM_TRAN_INFO'] : '') . "\">" . (isset($data['CPM_TRAN_INFO']) ? substr($data['CPM_TRAN_INFO'], 0, 25) : '') . "...</label>" : (isset($data['CPM_TRAN_INFO']) ? $data['CPM_TRAN_INFO'] : '')) . " </td> \n";

        $dVerifikasi =
            "\t\t<td class=\"$class\"> " . (isset($data['CPM_TRAN_STATUS']) && isset($status[$data['CPM_TRAN_STATUS']]) ? $status[$data['CPM_TRAN_STATUS']] : '') . " </td> \n";

        $dAdv =
            "\t\t<td class=\"$class\"> " . (isset($data['CPM_OP_KECAMATAN']) ? kecShow($data['CPM_OP_KECAMATAN']) : '') . " -  " . (isset($data['CPM_OP_KELURAHAN']) ? kelShow($data['CPM_OP_KELURAHAN']) : '') . " </td> \n
            \t\t<td class=\"$class\"> " . (isset($data['CPM_OPR_NIP']) ? $data['CPM_OPR_NIP'] : '') . " </td> \n";

        // $dAdv =
        "\t\t<td class=\"$class\"> " . (isset($data['CPM_OP_KECAMATAN']) ? kecShow($data['CPM_OP_KECAMATAN']) : '') . "-" . (isset($data['CPM_OP_KELURAHAN']) ? kelShow($data['CPM_OP_KELURAHAN']) : '') . " </td> \n
        \t\t<td class=\"$class\"> " . (isset($data['CPM_OPR_NIP']) ? $data['CPM_OPR_NIP'] : '') . " </td> \n";

        $statusService = array(
            1 => "OP Baru",
            2 => "Pemecahan"
        );
        $dService =
            "\t\t<td class=\"$class\" align=\"center\"><a href='main.php?param=" . base64_encode($params) . "'>" . (isset($data['CPM_ID']) ? $data['CPM_ID'] : '') . "</a> </td> \n
            \t\t<td class=\"$class\"> " . (isset($data['CPM_OP_NUMBER']) ? $data['CPM_OP_NUMBER'] : '') . "</td> \n
            \t\t<td class=\"$class\"> " . (isset($data['CPM_WP_NAME']) ? $data['CPM_WP_NAME'] : '') . "</td> \n
			\t\t<td class=\"$class\"> " . (isset($data['CPM_WP_ADDRESS']) ? $data['CPM_WP_ADDRESS'] : '') . "</td> \n
            \t\t<td class=\"$class\"> " . (isset($data['CPM_OP_ADDRESS']) ? $data['CPM_OP_ADDRESS'] : '') . " </td> \n
            \t\t<td class=\"$class\"> " . (isset($data['CPC_TKC_KECAMATAN']) && isset($data['CPC_TKL_KELURAHAN']) ? $data['CPC_TKC_KECAMATAN'] . " - " . $data['CPC_TKL_KELURAHAN'] : '') . " </td> \n
            \t\t<td class=\"$class\" align=\"center\"> " . (isset($data['CPM_TYPE']) && isset($statusService[$data['CPM_TYPE']]) ? $statusService[$data['CPM_TYPE']] : '') . " </td> \n
            ";

        if ($selected != 26) {
            $parse .= $dBasic;
        }
    }

    switch ($selected) {
        case 5:
            $parse = $dService;
            break;
        case 10:
            break;
        case 20:
        case 21:
        case 22:
        case 24:
            $parse .= $dAdv . $dVerifikasi;
            break;
        case 25:
            $parse .= $dAdv . $dVerifikasi;
            break;
        case 30:
            $parse .= $dTolak;
            break;
        case 31:
        case 32:
        case 35:
            $parse .= $dAdv . $dTolak;
            break;
        case 24:
        case 41:
        case 42:
        case 45:
        case 50:
            $parse .= $dAdv;
            break;
        case 60:
            //            if ($data['FLAG'] == 0)
            //                $sStatus = "Belum disahkan";
            //            else if ($data['FLAG'] == 1)
            //                $sStatus = "Siap diprint";
            //            else if ($data['FLAG'] == 2)
            //                $sStatus = "Sudah diprint";
            //            else if ($data['FLAG'] == 3)
            //                $sStatus = "SPPT dibatalkan";
            //            else
            //                $sStatus = "Sudah masuk daftar tagihan";

            $parse = "";
            $parse .= "\t\t<td class=\"$class\"> " . $data['NOP'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['WP_NAMA'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['WP_ALAMAT'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['OP_ALAMAT'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . number_format($data['OP_NJKP'], 0, ',', '.') . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . kecShow($data['OP_KECAMATAN_KODE']) . " - " . kelShow($data['OP_KELURAHAN_KODE']) . " </td> \n";
            //            $parse .= "\t\t<td class=\"$class\"> $sStatus </td> \n";
            break;
        case 70:
            $parse = "";
            $parse .= "\t\t<td class=\"$class\"> " . $data['NOP'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['WP_NAMA'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['WP_ALAMAT'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['OP_ALAMAT'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\" align=\"right\"> " . number_format($data['OP_NJKP'], 0, ',', '.') . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['OP_KECAMATAN'] . " - " . $data['OP_KELURAHAN'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['SPPT_TAHUN_PAJAK'] . " </td> \n";
            break;
        case 80:
        case 81:
            $parse = "";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_NOP'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_WP_NAMA'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_WP_ALAMAT'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_OP_ALAMAT'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . kecShow($data['CPM_OP_KECAMATAN']) . "-" . kelShow($data['CPM_OP_KELURAHAN']) . " </td> \n";
            $parse .= "\t\t<td class=\"$class\" align=\"right\"> " . number_format($data['CPM_NJOP_TANAH'] + $data['CPM_NJOP_BANGUNAN'], 0, ',', '.') . " </td> \n";
            break;
        case 82:
            $parse = "";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_NOP'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_WP_NAMA'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_WP_ALAMAT'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_OP_ALAMAT'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . kecShow($data['CPM_OP_KECAMATAN']) . "-" . kelShow($data['CPM_OP_KELURAHAN']) . " </td> \n";
            $parse .= "\t\t<td class=\"$class\" align=\"right\"> " . number_format($data['CPM_NJOP_TANAH'] + $data['CPM_NJOP_BANGUNAN'], 0, ',', '.') . " </td> \n";
            break;
    }

    return $parse;
}
function paging()
{
    global $a, $m, $n, $s, $page, $np, $perpage, $defaultPage, $totalrows, $displayDat;

    if (!empty($displayDat)) {
        $perpage = $displayDat;
    }

    $params = "a=" . $a . "&m=" . $m;

    $html = "<div>";
    $row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
    $rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
    $html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

    if ($page != 1) {
        $html .= "&nbsp;<a onclick=\"setPage('" . $s . "','" . $s . "','0')\"><span id=\"navigator-left\"></span></a>";
    }
    if ($rowlast < $totalrows) {
        $html .= "&nbsp;<a onclick=\"setPage('" . $s . "','" . $s . "','1')\"><span id=\"navigator-right\"></span></a>";
    }
    $html .= "</div>";
    return $html;
}
//mulai program
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page     = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np     = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$srch     = @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$tahun     = @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";
$kel     = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$buku     = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";
$jumlah = @isset($_REQUEST['jumlah']) ? $_REQUEST['jumlah'] : "";
$kec     = @isset($_REQUEST['kel']) ? substr($_REQUEST['kel'], 0, 7) : "";
$displayDat     = @isset($_REQUEST['displayDat']) ? substr($_REQUEST['displayDat'], 0, 7) : "";
$daftarNOP     = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$filterType    = @isset($_REQUEST['filterType']) ? $_REQUEST['filterType'] : "";

// print_r($_REQUEST);exit;

// echo $daftarNOP;exit;

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//set new page
if (isset($_SESSION['stSPOP'])) {
    if ($_SESSION['stSPOP'] != $s) {
        $_SESSION['stSPOP'] = $s;
        $kel = "";
        $kec = "";
        $srch = "";
        $tahun = "";
        $buku = "";
        $displayDat = 25;
        $jumlah = 10;
        $page = 1;
        $np = 1;
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stSPOP'] = $s;
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp($a);
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

/* === Get cookie data === */
$cData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
$data = null;
if (strlen(trim($cData)) > 0) {
    $data = $json->decode(base64_decode($cData));
}

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$dbServices = new DbServices($dbSpec);

$PenilaianParam = base64_encode('{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}');

$defaultPage = 1;
$perpage = $appConfig['ITEM_PER_PAGE'];

$uid = $data->uid;
//$userArea = $dbUtils->getUserDetailPbb($uid);
$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);
//$isSusulan = false;

$conf_kdkel = isset($appConfig['PENDATAAN_FILTER_KD_KELURAHAN']) ? $appConfig['PENDATAAN_FILTER_KD_KELURAHAN'] : '';
if ($conf_kdkel != "") {
    $kdkec  = substr($conf_kdkel, 0, 7);
    $filter = array("CPC_TKL_ID" => $conf_kdkel);
} else {
    $kdkec = null;
    $filter = null;
}
$aKecamatanPendataan = $dbUtils->getKecamatan($kdkec, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$aKelurahanPendataan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA'], $filter);
$aKelurahan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);

?>
<script type="text/javascript">
    var appID = '<?php echo $a; ?>';
    var tab = '<?php echo $s; ?>';
    var filterType = '<?php echo $filterType; ?>';
    var daftarNOP = "<?php echo trim(preg_replace('/\s+/', ' ', $daftarNOP)); ?>";
    $(document).ready(function() {
        // alert(tab);
        // alert(daftarNOP);
        // alert(filterType);
        if (tab == 24 || tab == 70 || tab == 82) {
            if (filterType != "") {
                if (filterType == "multi") {
                    $("textarea#daftarNOP").text(daftarNOP);
                    $("#multi").prop("checked", true);
                    $("#singleFilter").hide();
                    $("#multiFilter").show();
                } else {
                    $("#single").prop("checked", true);
                    $("#singleFilter").show();
                    $("#multiFilter").hide();
                }
            } else {
                $("#single").prop("checked", true);
                $("#singleFilter").show();
                $("#multiFilter").hide();
            }
        } else if (tab == 26) {

            $("#single").prop("checked", false);
            $("#singleFilter").hide();
            $("#multiFilter").hide();
        }

        $("#single").click(function() {
            $("#singleFilter").show();
            $("#multiFilter").hide();
        });
        $("#multi").click(function() {
            $("#singleFilter").hide();
            $("#multiFilter").show();
        });
        //        $( "input:submit, input:button").button();
        $("#tgl_penetapan").datepicker({
            dateFormat: 'dd-mm-yy'
        });

        $("#all-check-button").click(function() {
            $('.check-all').each(function() {

                this.checked = $("#all-check-button").is(':checked');
                $('#btn-penilaian').attr('disabled', false);
            });
        });

        $("#all-check-button-masal").click(function() {
            $('.check-all-masal').each(function() {
                this.checked = $("#all-check-button-masal").is(':checked');
            });
        });

        $("#all-check-button-tertunda").click(function() {
            $('.check-all-tertunda').each(function() {
                this.checked = $("#all-check-button-tertunda").is(':checked');
            });
        });

        $("#all-check-button-tab-ditetapkan").click(function() {
            $('.check-all').each(function() {

                this.checked = $("#all-check-button-tab-ditetapkan").is(':checked');
                $('#btn-penilaian').attr('disabled', false);
            });
        });
        $('.check-all').click(function() {
            $('#btn-penilaian').attr('disabled', false);
        });
        $(".tipclass").tooltip({
            track: false,
            delay: 0,
            showBody: " - ",
            bodyHandler: function() {
                var value = $(this)[0].tooltipText.replace(/\n/g, '<br />');
                return value;
            },
            fade: 250,
            extraClass: "fix",
            opacity: 0
        })

        <?php
        if ($kec != '') {
            echo "showKel2(" . $kec . ");";
        }
        ?>

        $('#selectedPrinter').change(function() {
            changePrinter($(this).val(), '<?php echo $uid ?>');
        })
    });

    function checkAll() {
        $("#all-check-button").click(function() {
            $('.check-all').each(function() {
                this.checked = $("#all-check-button").is(':checked');
                $('#btn-penilaian').attr('disabled', false);
            });
        });
        $('.check-all').click(function() {
            $('#btn-penilaian').attr('disabled', false);
        });
    }

    function showKel(x) {
        var val = x.value;
        showKel2(val);
    }

    function showKel2(val) {
        var s = <?php echo $s ?>;
        <?php foreach ($aKecamatan as $row) { ?>
            if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                document.getElementById('sKel' + s).innerHTML = "<?php echo "<select name='kel' class='form-control' id='kel' onchange='filKel(" . $s . ",this);'><option value=''>" . $appConfig['LABEL_KELURAHAN'] . "</option>";
                                                                    foreach ($aKelurahan as $row2) {
                                                                        if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                            $digit3 = substr($row2['CPC_TKL_ID'], 7, 3);
                                                                            echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . " - $digit3" . "</option>";
                                                                        }
                                                                    }
                                                                    echo "</select>"; ?>";
            }
        <?php } ?>
    }


    function showKel3(val) {
        var s = <?php echo $s ?>;
        <?php foreach ($aKecamatan as $row) { ?>
            if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                document.getElementById('sKelNOP' + s).innerHTML = "<?php echo "Kelurahan <select name='kelNOP' id='kelNOP'>";
                                                                    foreach ($aKelurahan as $row2) {
                                                                        if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                            $digit3 = substr($row2['CPC_TKL_ID'], 7, 3);
                                                                            echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($nop1) && substr($nop1, 0, 10) == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . " - $digit3" . "</option>";
                                                                        }
                                                                    }
                                                                    echo "</select>"; ?>";
            }
        <?php } ?>
    }

    function showKel4(val) {
        var s = <?php echo $s ?>;
        <?php foreach ($aKecamatan as $row) { ?>
            if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                document.getElementById('sKelNOPMundur' + s).innerHTML = "<?php echo "Kelurahan <select name='kelNOPMundur' id='kelNOPMundur'>";
                                                                            foreach ($aKelurahan as $row2) {
                                                                                if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                                    $digit3 = substr($row2['CPC_TKL_ID'], 7, 3);
                                                                                    echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($nop1) && substr($nop1, 0, 10) == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . " - $digit3" . "</option>";
                                                                                }
                                                                            }
                                                                            echo "</select>"; ?>";
            }
        <?php } ?>
    }

    function actionVerifikasiMassal() {

        if ($('#kel').val() == '') {
            $("<div>Pilih kelurahan terlebih dahulu!</div>").dialog({
                modal: true,
                buttons: {
                    OK: function() {
                        $(this).dialog("close");
                    }
                }
            });
        } else {
            $("<div>Anda yakin akan memverifikasi semua data pada kelurahan ini? Data akan dikirim ke Penetapan</div>").dialog({
                modal: true,
                buttons: {
                    Ya: function() {
                        $(this).dialog("close");
                        $("#load-mask").css("display", "block");
                        $("#load-content").fadeIn();
                        var kelurahan = $('#kel').val();
                        var params = '&kelurahan=' + kelurahan + '&appID=' + appID;
                        //ajax
                        $.ajax({
                            type: 'POST',
                            data: params,
                            url: './function/PBB/consol/proses-verifikasi-massal.php',
                            success: function(res) {
                                d = jQuery.parseJSON(res);
                                if (d.r == true) {
                                    $("<div>Verifikasi Massal berhasil!</div>").dialog({
                                        modal: true,
                                        buttons: {
                                            OK: function() {
                                                $(this).dialog("close");
                                                $("#load-mask").css("display", "none");
                                                $("#load-content").hide();
                                                filKel('21', kelurahan);
                                            }
                                        }
                                    });
                                } else {
                                    $("<div>" + d.errstr + "</div>").dialog({
                                        modal: true,
                                        buttons: {
                                            OK: function() {
                                                $(this).dialog("close");
                                                $("#load-mask").css("display", "none");
                                                $("#load-content").hide();
                                                filKel('21', kelurahan);
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    },
                    Tidak: function() {
                        $(this).dialog("close");
                    }
                }
            });
        }



    }

    function detailSPPT(nop, thn) {
        $('.sppt_detail').dialog({
            autoOpen: true,
            width: 700,
            position: {
                my: 'top',
                at: 'top'
            },
            modal: true,
            resizable: false,
            closeOnEscape: true
        });
        $('.sppt_detail').html('loading...');

        var params = {
            NOP: nop,
            appID: '<?php echo $a; ?>',
            tahun: thn
        };
        params = Base64.encode(Ext.encode(params));

        $.ajax({
            url: 'function/PBB/print/svc-sppt-preview.php',
            type: 'post',
            data: {
                req: params
            },
            success: function(res) {
                $('.sppt_detail').html(res);
                var opt = "";
                $("select#tahun").find('option').each(function() {
                    var th = $(this).val();
                    opt += "<option value='" + th + "' " + (th == thn ? 'selected' : '') + ">" + th + "</option>";
                });
                if (res == "") {
                    var html = "Maaf, data tidak tersedia untuk tahun " + thn + ". silakan pilih : <b>Tahun Pajak </b><select name=\"preview_tahun\" id=\"preview_tahun\" style=\"width:100px\" onchange=\"javascript:changeYear(this,'" + nop + "')\"></select>";
                    $('.sppt_detail').html(html);
                }
                $('#preview_tahun').html(opt);
            }
        });
    }

    function changeYear(obj, nop) {
        var thn = $(obj).val();
        detailSPPT(nop, thn);
    }

    function penilaianMundur(sts) {
        var kecamatan = $("#kecamatanOP").val();
        var kelurahan = $('#kel').val();
        var blok = $("#blok").val();
        var no_urut1 = $("#urut-1").val();
        var no_urut2 = $("#urut-2").val();
        var tahun = $("#tahun-penilaian").val();
        var tahun1 = $("#tahun-penilaian-1").val();
        var tahun2 = $("#tahun-penilaian-2").val();

        // var intervalThn = parseInt(tahun2) - parseInt(tahun1); 

        if (kecamatan == "") {
            alert("Kecamatan harus dipilih!!");
            return;
        }

        if (kelurahan == "") {
            alert("Kelurahan harus dipilih!!");
            return;
        }
        if (blok == "") {
            alert("Blok harus diisi!!");
            return;
        }
        if (no_urut1 == "" || no_urut2 == "") {
            alert("No urut harus diisi!!");
            return;
        }

        if (tahun1 > tahun2) {
            alert("Tahun ke-1 tidak boleh lebih besar dari Tahun ke-2");
            document.getElementById("tahun-penilaian-1").value = parseInt(tahun2) - 1;
            return
        }
        var params = {
            kelurahan: kelurahan,
            appID: '<?php echo $a; ?>',
            tahun: tahun,
            blok: blok,
            no_urut1: no_urut1,
            no_urut2: no_urut2,
            susulan: sts
        };
        params = Base64.encode(Ext.encode(params));
        $.ajax({
            url: 'view/PBB/svc-getPenetapanMundur.php',
            type: 'post',
            data: {
                req: params
            },
            dataType: "json",
            success: function(res) {
                // console.log(res.result);
                if (res.result == 'success') {

                    document.getElementById("nop_penetapan").value = res.msg;
                    return actionPenilaianMundur('<?php echo $PenilaianParam ?>', '5', sts);
                } else {
                    alert(res.msg);
                }
            }
        });
    }
</script>



<?php
displayContent($s);
?>
<div class="sppt_detail"></div>