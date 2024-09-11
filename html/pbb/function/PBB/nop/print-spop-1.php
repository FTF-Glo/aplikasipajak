<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'nop', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/payment/cdatetime.php");
require_once($sRootPath . "inc/payment/error-messages.php");

require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/payment/nid.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
}

function doPrint($id, $tab, &$strHTML)
{
        global $DBLink, $tTime, $modConfig, $sRootPath, $sdata, $Setting, $prm, $appConfig;

        switch ($tab) {
                case 0:
                        $tableName = "cppmod_pbb_sppt";
                        break;
                case 1:
                        $tableName = "cppmod_pbb_sppt_susulan";
                        break;
                case 2:
                        $tableName = "cppmod_pbb_sppt_final";
                        break;
                case 3:
                        $tableName = "cppmod_pbb_sppt_final";
                        break;
        }

        $query = "SELECT *,(CPM_NJOP_TANAH+CPM_NJOP_BANGUNAN) as CPM_TOTAL, (CPM_NJOP_TANAH/CPM_OP_LUAS_TANAH) AS CPM_TANAH FROM " . $tableName . "
                WHERE CPM_NOP IN ($id)";
        // echo $query; exit();
        $result = mysqli_query($DBLink, $query);
        $strHTML = '<html>';
        $idx = 0;
        while ($row = mysqli_fetch_array($result)) {
                if ($idx > 0) $strHTML .= '<br pagebreak="true"/>';
                $strHTML .= createHTML($row);
                $idx++;
        }
        $strHTML .= '</html>';

        return $strHTML;
}

function createHTML($row)
{
        global $DBLink, $appConfig;

        foreach ($row as $key => $value) {
                $tKey = substr($key, 4);
                $$tKey = $value;
        }

        $strHTML = '<table border="0" width="800" cellspacing="0" cellpadding="3">
    <tr>
        <td width="100">&nbsp;</td>
        <td align="left" width="auto"><font size="12">PEMERINTAH KABUPATEN OGAN KOMERING ULU SELATAN<br>DINAS PENDAPATAN DAERAH
		<br><br><b>SURAT PEMBERITAHUAN OBJEK PAJAK</b></font>
	</td>
    </tr>
    <table border="1" width="800" cellspacing="0" cellpadding="5">
    <tr>
        <td width="40">&nbsp;</td>
        <td width="160">&nbsp;</td>
        <td width="250" align="center">Data Saat Ini</td>
        <td width="250" align="center">Data Seharusnya</td>
    </tr>
    <tr><td colspan="4">A. INFORMASI TAMBAHAN UNTUK DATA BARU</td></tr>
    <tr>
        <td>2.</td>
        <td>NOP</td>
        <td>' . $NOP . '</td>
        <td>&nbsp;</td>
    </tr>
    <tr><td>3.</td><td>NOP Bersama</td>
            <td>' . $NOP_BERSAMA . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td colspan="4">B. DATA LETAK OBJEK PAJAK</td></tr>
    <tr><td>6.</td><td>Nama Jalan</td>
            <td>' . $OP_ALAMAT . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>7.</td><td>Blok/Kav/Nomor</td>
            <td>' . $OP_NOMOR . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>8.</td><td>' . $appConfig['LABEL_KELURAHAN'] . '</td>
            <td>' . getKelName($OP_KELURAHAN) . '</td>
    <td></td>
    </tr>
    <tr><td>9.</td><td>RT</td>
            <td>' . $OP_RT . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>10.</td><td>RW / Lingkungan</td>
            <td>' . $OP_RW . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>11.</td><td>Kecamatan</td>
            <td>' . getKecName($OP_KECAMATAN) . '</td>
    <td></td>
    </tr>
    <tr><td colspan="4" >C. DATA SUBJEK PAJAK</td></tr>
    <tr><td>12.</td><td>Status</td>
            <td>[ ' . (($WP_STATUS == "Pemilik") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Pemilik<br/>
            [ ' . (($WP_STATUS == "Penyewa") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Penyewa<br/>
            [ ' . (($WP_STATUS == "Pengelola") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Pengelola<br/>
            [ ' . (($WP_STATUS == "Pemakai") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Pemakai<br/>
            [ ' . (($WP_STATUS == "Sengketa") ? "V" : "&nbsp;&nbsp;") . ' ] 5. Sengketa
            </td>
            <td>[ &nbsp;&nbsp; ] 1. Pemilik<br/>
            [ &nbsp;&nbsp; ] 2. Penyewa<br/>
            [ &nbsp;&nbsp; ] 3. Pengelola<br/>
            [ &nbsp;&nbsp; ] 4. Pemakai<br/>
            [ &nbsp;&nbsp; ] 5. Sengketa
            </td>
    </tr>
    <tr><td>13.</td><td>Pekerjaan</td>
            <td>[ ' . (($WP_PEKERJAAN == "PNS") ? "V" : "&nbsp;&nbsp;") . ' ] 1. PNS<br/>
            [ ' . (($WP_PEKERJAAN == "TNI") ? "V" : "&nbsp;&nbsp;") . ' ] 2. TNI<br/>
            [ ' . (($WP_PEKERJAAN == "Pensiunan") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Pensiunan<br/>
            [ ' . (($WP_PEKERJAAN == "Badan") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Badan<br/>
            [ ' . (($WP_PEKERJAAN == "Lainnya") ? "V" : "&nbsp;&nbsp;") . ' ] 5. Lainnya
            </td>
            <td>[ &nbsp;&nbsp; ] 1. PNS<br/>
            [ &nbsp;&nbsp; ] 2. TNI<br/>
            [ &nbsp;&nbsp; ] 3. Pensiunan<br/>
            [ &nbsp;&nbsp; ] 4. Badan<br/>
            [ &nbsp;&nbsp; ] 5. Lainnya
            </td>
    </tr>
    <tr><td>14.</td><td>Nama Subjek Pajak</td>
            <td>' . $WP_NAMA . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>15.</td>
            <td colspan="3">[ &nbsp;&nbsp; ] Alamat subjek pajak sama dengan alamat objek pajak</td>
    </tr>
    <tr><td>16.</td><td>Nama Jalan + Nomor</td>
            <td>' . $WP_ALAMAT . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>17.</td><td>' . $appConfig['LABEL_KELURAHAN'] . '</td>
            <td>' . $WP_KELURAHAN . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>19.</td><td>RT</td>
            <td>' . $WP_RT . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>20.</td><td>RW / Lingkungan</td>
            <td>' . $WP_RW . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>21.</td><td>Kecamatan</td>
            <td>' . $WP_KECAMATAN . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>22.</td><td>Kabupaten / Kota</td>
            <td>' . $WP_KOTAKAB . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>23.</td><td>Kode Pos</td>
            <td>' . $WP_KODEPOS . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>24.</td><td>Nomor HP</td>
            <td></td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>25.</td><td>Nomor KTP</td>
            <td>' . $WP_NO_KTP . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>26.</td><td>NPWP</td>
            <td></td>
    <td>&nbsp;</td>
    </tr>

    <tr><td colspan="4" >D. DATA TANAH</td></tr>
    <tr><td>27.</td><td>Luas Tanah (m2)</td>
            <td>' . number_format($OP_LUAS_TANAH, 0, ',', '.') . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>28.</td><td>Zona Nilai Tanah *)</td>
            <td>' . $OT_ZONA_NILAI . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>29.</td><td>Latitude / Lintang</td>
            <td>' . $OT_LATITUDE . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>30.</td><td>Longitude / Bujur</td>
            <td>' . $OT_LONGITUDE . '</td>
    <td>&nbsp;</td>
    </tr>
    <tr><td>31.</td><td>Jenis Tanah</td>
            <td>[ ' . (($OT_JENIS == "1") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Tanah + Bangunan<br/>
            [ ' . (($OT_JENIS == "2") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Kavling Siap Bangun<br/>
            [ ' . (($OT_JENIS == "3") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Tanah Kosong<br/>
            [ ' . (($OT_JENIS == "4") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Fasilitas umum
            </td>
            <td>[ &nbsp;&nbsp; ] 1. Tanah + Bangunan<br/>
            [ &nbsp;&nbsp; ] 2. Kavling Siap Bangun<br/>
            [ &nbsp;&nbsp; ] 3. Tanah Kosong<br/>
            [ &nbsp;&nbsp; ] 4. Fasilitas umum
            </td>
    </tr>
    </table>
    <table border="0" width="700" cellspacing="0" cellpadding="0">
    <tr>
        <td width="350"><i>*)diisi oleh petugas</i></td>
        <td width="350" align="right"><i>dilanjutkan pada halaman berikutnya...</i></td>
    </tr>
    </table>
    <br pagebreak="true"/>
    
    <table border="1" width="700" cellspacing="0" cellpadding="5">
    <tr><td colspan="4">E. DATA BANGUNAN</td></tr>
    <tr>
        <td width="40">32.</td>
        <td width="160">Jumlah Bangunan</td>
        <td width="250" >' . $OP_JML_BANGUNAN . '</td>
        <td width="250" >&nbsp;</td>
    </tr>
    </table>
	<table border="1" width="700" cellspacing="0" cellpadding="5">
    <tr><td colspan="4">F. DATA HARGA BANGUNAN</td></tr>
    <tr>
        <td width="40">33.</td>
        <td width="160">Harga Pasar</td>
        <td width="250" >' . $TOTAL . '</td>
        <td width="250" >&nbsp;</td>
    </tr>
	<tr>
        <td width="40">34.</td>
        <td width="160">Harga Pasar Tanah/m<sup>2</sup></td>
        <td width="250" >' . $TANAH . '</td>
        <td width="250" >&nbsp;</td>
    </tr>
    </table><br/>
    <table border="0" width="700" cellspacing="0" cellpadding="5">
    <tr>
        <td colspan="3">G. PERNYATAAN SUBJEK PAJAK</td>
    </tr>
    <tr>
        <td colspan="3">Saya menyatakan bahwa informasi yang telah saya berikan dalam formulir ini termasuk lampirannya adalah benar, jelas dan lengkap menurut keadaan sebenarnya,
    sesuai dengan pasal 83 ayat (2) Undang-Undang Nomor 28 Tahun 2009 tentang Pajak Daerah dan Retribusi Daerah.</td>
    </tr>
    <tr>
        <td width="300">35. NAMA SUBJEK PAJAK / KUASANYA **)<br/><br/><br/><br/><br/>(..........................................)</td>
        <td width="200">36. TANGGAL<br/><br/><br/><br/><br/>(..........................................)</td>
        <td width="200">37. TANDA TANGAN<br/><br/><br/><br/><br/>(..........................................)</td>
    </tr>
    <tr>
        <td colspan="3"><i>**) Dalam hal ini bertindak sebagai kuasa, surat kuasa harap dilampirkan.</i><br/></td>
    </tr>
    <tr>
        <td colspan="3"><br/>H. IDENTITAS PENDATA</td>
    </tr>
    <tr>
        <td width="200">38. TANGGAL<br/><br/><br/><br/><br/>(..........................................)</td>
        <td width="200">39. TANDA TANGAN<br/><br/><br/><br/><br/>(..........................................)</td>
        <td width="300">40. NAMA / NIP PETUGAS PENDATA<br/><br/><br/><br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(..........................................)<br>39. NIP : ...................................<br/></td>
    </tr>
    <tr>
        <td colspan="3">I. PEJABAT YANG BERWENANG</td>
    </tr>
    <tr>
        <td width="200">41. TANGGAL<br/><br/><br/><br/><br/>(..........................................)</td>
        <td width="200">42. TANDA TANGAN<br/><br/><br/><br/><br/>(..........................................)</td>
        <td width="300">43. NAMA / NIP PEJABAT<br/><br/><br/><br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(..........................................)<br>44. NIP : ...................................</td>
    </tr>
    <tr>
        <td colspan="3" align="center"><br/><b>SKET / DENAH LOKASI OBJEK PAJAK</b><br/><br/>
            <table border="1" width="680">
                <tr><td><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="3"><br>
            KETERANGAN :
            <br>- Gambarkan sket/denah lokasi objek pajak (tanpa skala), yang dihubungkan dengan jalan raya / jalan protokol, jalan lingkungan dan lain-lain, yang mudah diketahui oleh umum.
            <br>- Sebutkan batas-batas pemilikan sebelah utara, selatan, timur dan barat.
        </td>
    </tr>
    </table>';

        // $docID = $SPPT_DOC_ID;

        // $query = "SELECT * FROM cppmod_pbb_sppt_ext_existing
        // WHERE CPM_SPPT_DOC_ID='$docID' ORDER BY CPM_OP_NUM";

        // $result 	= mysqli_query($DBLink, $query);
        // while ($rowExt = mysqli_fetch_array($result)) {
        // $strHTML .= '<br pagebreak="true"/>';
        // $strHTML .= createHTML_LSPOP($rowExt, $NOP, $OP_JML_BANGUNAN);
        // }

        return $strHTML;
}



function getKecName($kd)
{
        global $DBLink;

        $query = "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '" . $kd . "';";
        $res   = mysqli_query($DBLink, $query);
        $row   = mysqli_fetch_array($res);
        return $row['CPC_TKC_KECAMATAN'];
}

function getKelName($kd)
{
        global $DBLink;

        $query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kd . "';";
        $res   = mysqli_query($DBLink, $query);
        $row   = mysqli_fetch_array($res);
        return $row['CPC_TKL_KELURAHAN'];
}

$tTime                 = time();
$paymentDt;
$params         = @isset($_REQUEST['req']) ? $_REQUEST['req'] : '';
$p                         = base64_decode($params);
$json                 = new Services_JSON();
$prm                 = $json->decode($p);
$User                 = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
// print_r($prm);exit;
$appConfig         = $User->GetAppConfig($prm->appID);

$Setting = new SCANCentralSetting(DEBUG, LOG_FILENAME, $DBLink);

$arrValues = array();
if ($params) {
        $HTML = doPrint($prm->NOP, $prm->tab);
        //    echo $HTML; exit();
}


// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5, 3, 5);
//remove foot margin
$pdf->SetAutoPageBreak(TRUE, 8);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

$pdf->AddPage('P', 'F4');
$pdf->Image($sRootPath . 'image/' . $appConfig['LOGO_CETAK_PDF'], 13, 3, 18, '', '', '', '', false, 300, '', false);
//$pdf->Image($sRootPath.'function/PBB/consol/cthgbr.jpg', 120, 300, 70, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($HTML, true, false, false, false, '');
$pdf->SetAlpha(0.3);

//Close and output PDF document
$filename = date("Ymdhis") . '.pdf';
if (strlen($prm->NOP) == 18) $filename = $prm->NOP . '.pdf';
$pdf->Output($filename, 'I');
