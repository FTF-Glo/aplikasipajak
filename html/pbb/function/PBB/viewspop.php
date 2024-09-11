<?php

// var_dump($_SERVER);

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

require_once("inc/payment/uuid.php");

require_once("inc/PBB/dbSppt.php");

require_once("inc/PBB/dbSpptTran.php");

require_once("inc/PBB/dbSpptExt.php");

require_once("inc/PBB/dbFinalSppt.php");

require_once("inc/PBB/dbSpptHistory.php");

require_once("function/PBB/gwlink.php");

require_once("inc/PBB/dbUtils.php");

require_once("inc/PBB/dbServices.php");

require_once("inc/PBB/dbGwCurrent.php");

require_once("inc/PBB/dbSpptPerubahan.php");

require_once("inc/PBB/dbSpptPerubahan.php");

require_once("inc/PBB/dbWajibPajak.php");

$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$dbSppt = new DbSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbSpptHistory = new DbSpptHistory($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$dbServices = new DbServices($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbSpptPerubahan = new DbSpptPerubahan($dbSpec);
$dbWajibPajak = new DbWajibPajak($dbSpec);
$NBParam_before = '{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}';
$NBParam = base64_encode($NBParam_before);

// echo "<pre>"; print_r($_REQUEST); echo "</pre>";
// ////////////////////////////
// Process approved by staff dispenda
// ///////////////////////////

if (isset($_REQUEST['btn-process']) && ($arConfig['usertype'] == "dispenda" || $arConfig['usertype'] == "pejabatdispenda" || $arConfig['usertype'] == "dispenda2")) {

    // echo "string";
    // print_r($_REQUEST);
    // exit;

    if (isset($rekomendasi)) {
        $aVal['CPM_TRAN_FLAG'] = 1;
        $vals = $dbSpptTran->get($idt);
        $dbSpptTran->edit($idt, $aVal);
        unset($vals[0]['CPM_TRAN_ID']);
        unset($vals[0]['CPM_TRAN_DATE']);

        // Set Status

        if (($rekomendasi == "y") && ($arConfig['usertype'] == "dispenda")) {
            $vals[0]['CPM_TRAN_STATUS'] = 3;
        } else
        if (($rekomendasi == "y") && ($arConfig['usertype'] == "dispenda2")) {
            $vals[0]['CPM_TRAN_STATUS'] = 4;
        } else
        if (($rekomendasi == "n") && ($arConfig['usertype'] == "dispenda")) {
            $vals[0]['CPM_TRAN_STATUS'] = 7;
            $vals[0]['CPM_TRAN_INFO'] = $TRAN_INFO;
        } else
        if (($rekomendasi == "n") && ($arConfig['usertype'] == "dispenda2")) {
            $vals[0]['CPM_TRAN_STATUS'] = 8;
            $vals[0]['CPM_TRAN_INFO'] = $TRAN_INFO;
        }

        $vals[0]['CPM_TRAN_OPR_DISPENDA_1'] = $uname;
        $vals[0]['CPM_TRAN_DATE'] = date("Y-m-d H:i:s");

        // Untuk Verifikasi III

        $lastID = c_uuid();
        $bOK = $dbSpptTran->add($lastID, $vals[0]);
        $idd = $vals[0]['CPM_TRAN_SPPT_DOC_ID'];
        $v = $vals[0]['CPM_SPPT_DOC_VERSION'];
        $docSPPT = $dbSppt->get($idd, $v);
        /*********************** Update ke tabel cppmod_pbb_service jika data OP berasal dari pelayanan *********************************/
        if (($bOK && $rekomendasi == "y") && ($arConfig['usertype'] == "dispenda" && $appConfig['jumlah_verifikasi'] == 3)) {
            $updateService = array();
            $updateService['CPM_STATUS'] = '3';
            $updateService['CPM_VERIFICATOR'] = $uname;
            $updateService['CPM_DATE_VERIFICATION'] = date("Y-m-d");
            $dbServices->updateTransactionFromPendataan($docSPPT[0]['CPM_NOP'], $updateService);
        } else
        if (($bOK && $rekomendasi == "y") && (($arConfig['usertype'] == "dispenda" && $appConfig['jumlah_verifikasi'] == 2) || ($arConfig['usertype'] == "dispenda2" && $appConfig['jumlah_verifikasi'] == 3))) {
            $updateService = array();
            $updateService['CPM_STATUS'] = '4';
            $updateService['CPM_APPROVER'] = $uname;
            $updateService['CPM_DATE_APPROVER'] = date("Y-m-d");
            $x = $dbServices->updateTransactionFromPendataan($docSPPT[0]['CPM_NOP'], $updateService);

            // 09 MEI 2018 BY 35U TECH START

            if ($x) {

                // GET NOP INDUK BY NO SERVICE

                $xx = getNOPIndukFromSplit($docSPPT[0]['CPM_NOP']);
                if (count($xx) > 0) {
                    setChangeInduk($xx);
                }
            } // end $x

            // 09 MEI 2018 BY 35U TECH END

        }

        /*********************** End update ke tabel cppmod_pbb_service *********************************/
        /*
        Pengecekan verifikasi
        Jika jumlah verifikasi yang ditentukan adalah 2 kali, maka begitu verifikasi 2 (bisa diketahui dari 'usertype' == 'dispenda') maka data langsung masuk ke penetapan, jika tidak maka data masuk ke verifikasi 3
        Jika jumlah verifikasi yang ditentukan adalah 3 kali, maka begitu verifikasi 3 (bisa diketahui dari 'usertype' == 'dispenda2') maka data langsung masuk ke penetapan
        */
        // echo "masuk";
        // exit;
        $dbLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['ADMIN_SW_DBNAME'], $appConfig['GW_DBPORT']); // koneksi ke gw
        //mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $dbLink) or die(mysqli_error($DBLink));


        if (($bOK && $rekomendasi == "y") && (($arConfig['usertype'] == "dispenda" && $appConfig['jumlah_verifikasi'] == 2) || ($arConfig['usertype'] == "dispenda2" && $appConfig['jumlah_verifikasi'] == 3))) {

            // periksa tanggal untuk tau masuk transaksi normal atau susulan

            if ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']) {
                $bOK = $dbSpptHistory->goSusulan($lastID);
            } else {
                $bOK = $dbSpptHistory->goFinal($lastID);
            }
        }

        if ($bOK) {
            header("Location: main.php?param=" . base64_encode("a=$a&m=$m"));
        } else {
            echo "<div class='error'>Kesalahan saat finalisasi ke database</div>";
        }
    } else {
        echo "<div class='error'>Anda belum memilih rekomendasi yang diberikan</div>";
    }
}

// Preparing Parameters

if (isset($idt)) {
    $tran = $dbSpptTran->gets($idt);
    $idd = $tran[0]['CPM_TRAN_SPPT_DOC_ID'];
    $v = $tran[0]['CPM_SPPT_DOC_VERSION'];
    $dispenda = $tran[0]['CPM_TRAN_OPR_DISPENDA_1'];
}

if (isset($idd) || isset($v)) {
    $docVal = $dbSppt->gets($idd, $v);

    if ($docVal == null) {
        $docVal = $dbFinalSppt->gets($idd, $v);

        if ($docVal == null) {
            $docVal = $dbFinalSppt->getSusulan($idd, $v);
        }
    }

    $aDocExt = $dbSpptExt->gets($idd, $v);

    if ($aDocExt == null) {
        $aDocExt = $dbFinalSppt->getExts($idd, $v);
        if ($aDocExt == null) {
            $aDocExt = $dbFinalSppt->getExtSusulans($idd, $v);
        }
    }

    foreach ($docVal[0] as $key => $value) {
        $tKey = substr($key, 4);
        $$tKey = $value;
    }

    $serti = $dbSppt->get_sertifikat($NOP);
    if(count($serti)>0){
        foreach ($serti[0] as $key => $value) {
            $tKey = substr($key, 4);
            $$tKey = $value;
        }
    }

    if (isset($aDocExt)) {
        $HtmlExt = "";
        foreach ($aDocExt as $docExt) {
            $param = "a=$a&m=$m&f=" . $arConfig['id_view_lampiran'] . "&idd=$idd&v=$v&num=" . $docExt['CPM_OP_NUM'];
            $HtmlExt .= "<li><a href='main.php?param=" . base64_encode($param) . "'>Lampiran Bangunan " . $docExt['CPM_OP_NUM'] . "</a></li>";
        }
    }

    if ($NOP_BERSAMA != '') {
        $docAnggota = $dbSppt->getAnggota($NOP_BERSAMA, $NOP);
        foreach ($docAnggota[0] as $key => $value) {
            $tKey = substr($key, 4);
            $$tKey = $value;
        }
    }
}

$aOPKabKota = $dbUtils->getKabKota($OP_KOTAKAB);
$aOPKecamatan = $dbUtils->getKecamatan($OP_KECAMATAN);
$aOPKelurahan = $dbUtils->getKelurahan($OP_KELURAHAN);

// $aWPKabKota = $dbUtils->getKabKota($WP_KOTAKAB);
// $aWPKecamatan = $dbUtils->getKecamatan($WP_KECAMATAN);
// $aWPKelurahan = $dbUtils->getKelurahan($WP_KELURAHAN);

echo "<link rel=\"stylesheet\" href=\"function/PBB/viewspop.css\" type=\"text/css\">";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
?>

<script type="text/javascript">
    $(document).ready(function() {
        $("input:submit, input:button").button();
    });
</script>
<input type="hidden" id="DPC_TODAY_TEXT" value="today">
<input type="hidden" id="DPC_BUTTON_TITLE" value="Open calendar...">
<input type="hidden" id="DPC_MONTH_NAMES" value="['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']">
<input type="hidden" id="DPC_DAY_NAMES" value="['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']">

<?php
$tahun_tagihan = $appConfig['tahun_tagihan'];
include("viewtmp.php");

if (($arConfig['usertype'] == "dispenda" && $tran[0]['CPM_TRAN_STATUS'] == 2) || ($arConfig['usertype'] == "dispenda2" && $tran[0]['CPM_TRAN_STATUS'] == 3)) {

    // echo $tran[0]['CPM_TRAN_FLAG'];exit;

?>
    <br />
    <form method="post">
        <table border=0 cellpadding=5>
            <tr>
                <td colspan=2 class="tbl-rekomen"><b>Masukkan rekomendasi anda</b></td>
            </tr>
            <tr>
                <td class="tbl-rekomen" valign="top"><label><input checked="" type="radio" name="rekomendasi" value="y"> Setuju</label></td>
                <td class="tbl-rekomen">
                    <?php

                    // if ($dispenda == "") {

                    ?>
                    <!--
                        <table>
                            <tr><td colspan=5 class="tbl-rekomen"><b>Pejabat yang berwenang</b></td></tr>
                            <tr><td class="tbl-rekomen">Tanggal penelitian</td>
                                <td colspan=4 class="tbl-rekomen"><input type="text" name="PJB_TGL_PENELITIAN" id="PJB_TGL_PENELITIAN" datepicker="true" datepicker_format="DD/MM/YYYY"></td></tr>
                            <tr><td class="tbl-rekomen">Nama Jelas</td>
                                <td colspan=4 class="tbl-rekomen"><input type="text" name="PJB_NAMA" size="34"></td></tr>
                            <tr><td class="tbl-rekomen">NIP</td>
                                <td colspan=4 class="tbl-rekomen"><input type="text" name="PJB_NIP" size="17"></td></tr>
                        </table>
                    <?php

                    // }

                    ?>
                    -->
                </td>
            </tr>
            <tr>
                <td valign="top" class="tbl-rekomen"><label><input type="radio" name="rekomendasi" value="n"> Tolak</label></td>
                <td class="tbl-rekomen">Alasan<br /><textarea name="TRAN_INFO" cols=70 rows=7></textarea></td>
            </tr>
            <tr>
                <td colspan=2 align="right" class="tbl-rekomen"><input type="submit" name="btn-process" value="Submit"></td>
            </tr>
        </table>
    </form>
<?php
}

?>
<?php

function getNOPIndukFromSplit($nop_anak)
{
    global $appConfig;
    $dbLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['ADMIN_SW_DBNAME'], $appConfig['GW_DBPORT']); // koneksi ke gw
    //mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $dbLink) or die(mysqli_error($DBLink));
    $query = "SELECT
            A.*, B.CPM_OP_NUMBER AS NOP_INDUK
            FROM
            cppmod_pbb_service_split A
            INNER JOIN cppmod_pbb_services B ON A.CPM_SP_SID = B.CPM_ID
            WHERE A.CPM_SP_NOP = '$nop_anak'
             ";
    $data = mysqli_query($dbLink, $query) or die(mysqli_error($dbLink));
    $numrows = mysqli_num_rows($data);
    $array = array();
    if ($numrows > 0) {
        while ($row = mysqli_fetch_array($data)) {
            $array['CPM_SP_ID'] = $row['CPM_SP_ID'];
            $array['CPM_SP_SID'] = $row['CPM_SP_SID'];
            $array['CPM_SP_NOP'] = $row['CPM_SP_NOP'];
            $array['CPM_SP_PENETAPAN_INDUK'] = $row['CPM_SP_PENETAPAN_INDUK'];
            $array['NOP_INDUK'] = $row['NOP_INDUK'];
        }
    }

    return $array;
}

function hitungTagihan($njop, $njoptkp)
{
    global $appConfig, $dbUtils;
    $njoptkp = ($njoptkp != 0 || $njoptkp != null || $njoptkp != "" ? $njoptkp : 0);
    $minTagihan = ($appConfig['minimum_sppt_pbb_terhutang'] != 0 ? $appConfig['minimum_sppt_pbb_terhutang'] : 0);
    if ($njop > $njoptkp) {
        $njkp = $njop - $njoptkp;
    } else {
        $njkp = 0;
    }

    // echo $njkp;
    // exit;

    $tarif = $dbUtils->getTarif($njkp);
    $tagihan = $njkp * ($tarif / 100);
    if ($tagihan < $minTagihan) $tagihan = $minTagihan;
    return $tagihan;
}

function getNJKP($njop, $njoptkp)
{
    global $appConfig, $dbUtils;
    $njoptkp = ($njoptkp != 0 || $njoptkp != null || $njoptkp != "" ? $njoptkp : 0);
    $minTagihan = ($appConfig['minimum_sppt_pbb_terhutang'] != 0 ? $appConfig['minimum_sppt_pbb_terhutang'] : 0);

    // var_dump("123");
    // var_dump("NJOP ".$njop);
    // var_dump("NJOPTKP ".$njoptkp);
    // exit;

    if ($njop > $njoptkp) {
        $njkp = $njop - $njoptkp;
    } else {
        $njkp = 0;
    }

    return $njkp;
}

function setChangeInduk($xx)
{

    global $dbGwCurrent, $dbUtils, $dbSpptPerubahan, $dbSppt, $dbWajibPajak, $appConfig, $dbServices, $NBParam, $NBParam_before;
    $svc_id = $xx['CPM_SP_SID'];
    $NOP_INDUK = $xx['NOP_INDUK']; // NOP INDUK DI GUNAKAN UNTUK PENILAIAN DAN PENETAPAN
    $dataPerubahan = $dbServices->getDataChangeBySID($svc_id); // mendapatkan data perubahan dari service change
    $is_penetapanan_thn_ini = $xx['CPM_SP_PENETAPAN_INDUK'];

    // JIKA NOP INDUK D TETAPKAN TAHUN INI MAKA

    if ($is_penetapanan_thn_ini == "1") {
        $GWDBLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], $appConfig['GW_DBPORT']); // koneksi ke gw
        //mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) or die(mysqli_error($DBLink));
        $sppt = $dbGwCurrent->getDataTagihanSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $GWDBLink);

        // jika belum bayar [START]

        if ($sppt['PAYMENT_FLAG'] != 1 || $sppt['PAYMENT_FLAG'] === NULL) {

            // MELAKUKAN PENILAIAN MENGGUNAKAN SERVICE JAVA  [START]

            // $url = "127.0.0.1/inc/PBB/svc-penilaian.php";
            $url = "127.0.0.1:8080/inc/PBB/svc-penilaian.php";
            $param = array(
                "SVR_PRM" => $NBParam,
                'NOP' => $NOP_INDUK,
                "TAHUN" => $appConfig['tahun_tagihan'],
                "TIPE" => 2,
                "SUSULAN" => "0"
            );
            $param = json_encode($param);
            $param = base64_encode($param);
            $vars = array(
                "req" => $param
            );
            $postData = http_build_query($vars);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $array = json_decode($response);

            // MELAKUKAN PENILAIAN MENGGUNAKAN SERVICE JAVA  [END]
            // jika penilaian sukses maka

            if ($array->RC == "0000") {
                mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']) or die(mysqli_error($GWDBLink));

                // MULAI HITUNG KEMBALI [START]
                // ambil data current untuk mendapatkan nilai NJOPTKP dan Tagihan sebelumnya

                $dataCurrent = $dbGwCurrent->getDataCurrent($NOP_INDUK);
                $njoptkp = $dataCurrent['OP_NJOPTKP'];
                $tagihanLama = $dataCurrent['SPPT_PBB_HARUS_DIBAYAR'];
                $totalNJOPBaru = $dataPerubahan['CPM_NJOP_TANAH'] + $dataPerubahan['CPM_NJOP_BANGUNAN'];
                $tagihanBaru = hitungTagihan($totalNJOPBaru, $njoptkp); // mendapatkan nilai tagihan
                $njkp_baru = getNJKP($totalNJOPBaru, $njoptkp); // mendapatkan NJKP

                // MULAI HITUNG KEMBALI [END]

                mysqli_select_db($GWDBLink, $appConfig['GW_DBNAME']) or die(mysqli_error($GWDBLink));

                // data dibawah ini untuk data perubahan

                $valTagihanSPPT = array();
                $valTagihanSPPT['SPPT_PBB_HARUS_DIBAYAR'] = round($tagihanBaru);
                $valTagihanSPPT['WP_PEKERJAAN'] = $dataPerubahan['CPM_WP_PEKERJAAN'];
                $valTagihanSPPT['WP_NAMA'] = $dataPerubahan['CPM_WP_NAMA'];
                $valTagihanSPPT['WP_ALAMAT'] = $dataPerubahan['CPM_WP_ALAMAT'];
                $valTagihanSPPT['WP_KELURAHAN'] = $dataPerubahan['CPM_WP_KELURAHAN'];
                $valTagihanSPPT['WP_RT'] = $dataPerubahan['CPM_WP_RT'];
                $valTagihanSPPT['WP_RW'] = $dataPerubahan['CPM_WP_RW'];
                $valTagihanSPPT['WP_KOTAKAB'] = $dataPerubahan['CPM_WP_KOTAKAB'];
                $valTagihanSPPT['WP_KECAMATAN'] = $dataPerubahan['CPM_WP_KECAMATAN'];
                $valTagihanSPPT['WP_KODEPOS'] = $dataPerubahan['CPM_WP_KODEPOS'];
                $valTagihanSPPT['WP_NO_HP'] = $dataPerubahan['CPM_WP_NO_HP'];
                $valTagihanSPPT['OP_LUAS_BUMI'] = $dataPerubahan['CPM_OP_LUAS_TANAH'];
                $valTagihanSPPT['OP_LUAS_BANGUNAN'] = $dataPerubahan['CPM_OP_LUAS_BANGUNAN'];
                $valTagihanSPPT['OP_KELAS_BUMI'] = $dataPerubahan['CPM_OP_KELAS_TANAH'];
                $valTagihanSPPT['OP_KELAS_BANGUNAN'] = $dataPerubahan['CPM_OP_KELAS_BANGUNAN'];
                $valTagihanSPPT['OP_NJOP_BUMI'] = $dataPerubahan['CPM_NJOP_TANAH'];
                $valTagihanSPPT['OP_NJOP_BANGUNAN'] = $dataPerubahan['CPM_NJOP_BANGUNAN'];
                $valTagihanSPPT['OP_NJOP'] = $totalNJOPBaru;
                $valTagihanSPPT['OP_NJKP'] = $njkp_baru;
                $valTagihanSPPT['OP_ALAMAT'] = $dataPerubahan['CPM_OP_ALAMAT'];
                $valTagihanSPPT['OP_RT'] = $dataPerubahan['CPM_OP_RT'];
                $valTagihanSPPT['OP_RW'] = $dataPerubahan['CPM_OP_RW'];

                // UNTUK TABLE CURRENT

                //          echo "masuk";
                // exit;
                $valCurrentSPPT = array();
                $valCurrentSPPT['SPPT_PBB_HARUS_DIBAYAR'] = round($tagihanBaru);
                $ubahSPPT = $dbGwCurrent->updateTagihanSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $valTagihanSPPT, $GWDBLink);
                if ($ubahSPPT) { // jika berhasil Ubah SPPT [START]
                    mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $GWDBLink);
                    $ubahCurrent1 = $dbSpptPerubahan->updateToCurrent($svc_id, $appConfig);
                    if ($ubahCurrent1) {
                        $ubahCurrent2 = $dbGwCurrent->updateCurrentSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $valCurrentSPPT, $appConfig);
                    }
                }
                mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $GWDBLink);
                lastAction($dataPerubahan, $svc_id); // update to Final

                // jika berhasil Ubah SPPT [END]

            } else {

                echo "Penilaian NOP Induk Gagal";

                exit;
            }
        } // jika belum bayar [END]
        else { // jika udah bayar dan set tahun ini
            mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $GWDBLink);
            lastAction($dataPerubahan, $svc_id);
        }
    } // end  jika tahun ini d tetapkan
    else { // jika tahun depan maka
        mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $GWDBLink);
        lastAction($dataPerubahan, $svc_id);
    }
}

function lastAction($dataPerubahan, $svc_id)
{
    global $dbSpptPerubahan, $dbWajibPajak, $appConfig, $GWDBLink;

    // mau tahun depan atau sekarang tetap update final
    // GET DATA UNTUK SIMPEN KE WAJIB PAJAK [START]
    // mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $GWDBLink);

    $contentWP = array();
    $contentWP['CPM_WP_STATUS'] = $dataPerubahan['CPM_WP_STATUS'];
    $contentWP['CPM_WP_PEKERJAAN'] = $dataPerubahan['CPM_WP_PEKERJAAN'];
    $contentWP['CPM_WP_NAMA'] = $dataPerubahan['CPM_WP_NAMA'];
    $contentWP['CPM_WP_ALAMAT'] = $dataPerubahan['CPM_WP_ALAMAT'];
    $contentWP['CPM_WP_KELURAHAN'] = $dataPerubahan['CPM_WP_KELURAHAN'];
    $contentWP['CPM_WP_RT'] = $dataPerubahan['CPM_WP_RT'];
    $contentWP['CPM_WP_RW'] = $dataPerubahan['CPM_WP_RW'];
    $contentWP['CPM_WP_PROPINSI'] = $dataPerubahan['CPM_WP_PROPINSI'];
    $contentWP['CPM_WP_KOTAKAB'] = $dataPerubahan['CPM_WP_KOTAKAB'];
    $contentWP['CPM_WP_KECAMATAN'] = $dataPerubahan['CPM_WP_KECAMATAN'];
    $contentWP['CPM_WP_KODEPOS'] = $dataPerubahan['CPM_WP_KODEPOS'];
    $contentWP['CPM_WP_NO_HP'] = $dataPerubahan['CPM_WP_NO_HP'];

    // GET DATA UNTUK SIMPEN KE WAJIB PAJAK [END]
    // UPDATE KE TABLE FINAL [START]
    // update data wajib pajak

    $res = $dbWajibPajak->save($dataPerubahan['CPM_WP_NO_KTP'], $contentWP);
    $res3 = $dbSpptPerubahan->updateToFinal($dataPerubahan['CPM_SPPT_DOC_ID'], $svc_id);
    if ($res3) {

        $res3 = $dbSpptPerubahan->deleteDataPerubahan($dataPerubahan['CPM_SPPT_DOC_ID']);
    } else {
        echo "Gagal melakukan penghapusan data perubahan";
    }

    // UPDATE KE TABLE FINAL [END]

}

?>