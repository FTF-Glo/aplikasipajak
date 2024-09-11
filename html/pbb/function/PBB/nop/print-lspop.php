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
    //        echo $query; exit();
    $result = mysqli_query($DBLink, $query);
    //$strHTML = '<html>';
    $idx = 0;
    while ($row = mysqli_fetch_array($result)) {
        if ($idx > 0) $strHTML .= '<br pagebreak="true"/>';
        $strHTML .= createHTML($row, $tab);
        $idx++;
    }
    //$strHTML .= '</html>';

    return $strHTML;
}

function createHTML($row, $tab)
{
    global $DBLink, $appConfig;

    foreach ($row as $key => $value) {
        $tKey = substr($key, 4);
        $$tKey = $value;
    }

    $docID = $SPPT_DOC_ID;

    switch ($tab) {
        case 0:
            $tableName = "cppmod_pbb_sppt_ext";
            break;
        case 1:
            $tableName = "cppmod_pbb_sppt_ext_susulan";
            break;
        case 2:
            $tableName = "cppmod_pbb_sppt_ext_final";
            break;
        case 3:
            $tableName = "cppmod_pbb_sppt_ext_final";
            break;
    }

    $query = "SELECT * FROM " . $tableName . "
                WHERE CPM_SPPT_DOC_ID='$docID' ORDER BY CPM_OP_NUM";
    // echo $query; exit();
    $result     = mysqli_query($DBLink, $query);
    //print_r($result);exit;
    while ($rowExt = mysqli_fetch_array($result)) {
        //$strHTML .= '<br pagebreak="true"/>';
        $strHTML .= createHTML_LSPOP($rowExt, $NOP, $OP_JML_BANGUNAN);
    }

    return $strHTML;
}

function createHTML_LSPOP($row, $NOP, $OP_JML_BANGUNAN)
{
    global $DBLink;

    foreach ($row as $key => $value) {
        $tKey = substr($key, 4);
        $$tKey = $value;
    }

    $strHTML = '
        <table border="0" width="700" cellspacing="0" cellpadding="5">
        <tr>
            <td align="center"><h2>LAMPIRAN SURAT PEMBERITAHUAN OBJEK PAJAK</h2></td>
        </tr>
        
        <table border="1" width="800" cellspacing="0" cellpadding="4">
	<tr>
            <td width="40">&nbsp;</td>
            <td width="180">&nbsp;</td>
            <td width="240" align="center">Data Saat Ini</td>
            <td width="240" align="center">Data Seharusnya</td>
        </tr>
        <tr><td>1.</td>
            <td>NOP</td>
            <td colspan="2">' . $NOP . '</td>
        </tr>
        <tr><td>2.</td>
            <td>Jumlah Bangunan</td>
            <td>' . $OP_JML_BANGUNAN . '</td>
            <td></td>
        </tr>
	<tr><td>3.</td><td>Bangunan Ke</td>
		<td>' . $OP_NUM . '</td>
            <td></td></tr>
        <tr><td colspan="4"><br>A. RINCIAN DATA BANGUNAN</td></tr>
	<tr><td>4.</td><td>Jenis penggunaan bangunan</td>
            <td>[ ' . (($OP_PENGGUNAAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Perumahan<br/>
                [ ' . (($OP_PENGGUNAAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Perkantoran Swasta<br/>
                [ ' . (($OP_PENGGUNAAN == "3") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Pabrik<br/>
                [ ' . (($OP_PENGGUNAAN == "4") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Toko / Apotik / Pasar / Ruko<br/>
                [ ' . (($OP_PENGGUNAAN == "5") ? "V" : "&nbsp;&nbsp;") . ' ] 5. Rumah Sakit / Klinik<br/>
                [ ' . (($OP_PENGGUNAAN == "6") ? "V" : "&nbsp;&nbsp;") . ' ] 6. Olah Raga / Rekreasi<br/>
                [ ' . (($OP_PENGGUNAAN == "7") ? "V" : "&nbsp;&nbsp;") . ' ] 7. Hotel / Wisma<br/>
                [ ' . (($OP_PENGGUNAAN == "8") ? "V" : "&nbsp;&nbsp;") . ' ] 8. Bengkel / Gudang / Pertanian<br/>
                [ ' . (($OP_PENGGUNAAN == "9") ? "V" : "&nbsp;&nbsp;") . ' ] 9. Gedung Pemerintah<br/>
                [ ' . (($OP_PENGGUNAAN == "10") ? "V" : "&nbsp;&nbsp;") . ' ] 10. Lain-lain<br/>
                [ ' . (($OP_PENGGUNAAN == "11") ? "V" : "&nbsp;&nbsp;") . ' ] 11. Bangunan Tidak Kena Pajak<br/>
                [ ' . (($OP_PENGGUNAAN == "12") ? "V" : "&nbsp;&nbsp;") . ' ] 12. Bangunan Parkir<br/>
                [ ' . (($OP_PENGGUNAAN == "13") ? "V" : "&nbsp;&nbsp;") . ' ] 13. Apartemen<br/>
                [ ' . (($OP_PENGGUNAAN == "14") ? "V" : "&nbsp;&nbsp;") . ' ] 14. Pompa Bensin<br/>
                [ ' . (($OP_PENGGUNAAN == "15") ? "V" : "&nbsp;&nbsp;") . ' ] 15. Tangki Minyak<br/>
                [ ' . (($OP_PENGGUNAAN == "16") ? "V" : "&nbsp;&nbsp;") . ' ] 16. Gedung Sekolah
            </td>
            <td>[ &nbsp;&nbsp; ] 1. Perumahan<br/>
                [ &nbsp;&nbsp; ] 2. Perkantoran Swasta<br/>
                [ &nbsp;&nbsp; ] 3. Pabrik<br/>
                [ &nbsp;&nbsp; ] 4. Toko / Apotik / Pasar / Ruko<br/>
                [ &nbsp;&nbsp; ] 5. Rumah Sakit / Klinik<br/>
                [ &nbsp;&nbsp; ] 6. Olah Raga / Rekreasi<br/>
                [ &nbsp;&nbsp; ] 7. Hotel / Wisma<br/>
                [ &nbsp;&nbsp; ] 8. Bengkel / Gudang / Pertanian<br/>
                [ &nbsp;&nbsp; ] 9. Gedung Pemerintah<br/>
                [ &nbsp;&nbsp; ] 10. Lain-lain<br/>
                [ &nbsp;&nbsp; ] 11. Bangunan Tidak Kena Pajak<br/>
                [ &nbsp;&nbsp; ] 12. Bangunan Parkir<br/>
                [ &nbsp;&nbsp; ] 13. Apartemen<br/>
                [ &nbsp;&nbsp; ] 14. Pompa Bensin<br/>
                [ &nbsp;&nbsp; ] 15. Tangki Minyak<br/>
                [ &nbsp;&nbsp; ] 16. Gedung Sekolah
            </td>
        </tr>
	<tr><td>5.</td><td>Luas bangunan (m2)</td>
		<td>' . (($OP_LUAS_BANGUNAN != "") ? number_format($OP_LUAS_BANGUNAN, 0, ',', '.') : "-") . '</td>
            <td></td></tr>
	<tr><td>6.</td><td>Jumlah lantai</td>
		<td>' . (($OP_JML_LANTAI != "") ? $OP_JML_LANTAI : "-") . '</td>
            <td></td></tr>
	<tr><td>7.</td><td>Tahun dibangun</td>
		<td>' . (($OP_THN_DIBANGUN != "") ? $OP_THN_DIBANGUN : "-") . '</td>
            <td></td></tr>
	<tr><td>8.</td><td>Tahun direnovasi</td>
		<td>' . (($OP_THN_RENOVASI != "") ? $OP_THN_RENOVASI : "-") . '</td>
            <td></td></tr>
	<tr><td>9.</td><td>Daya listrik terpasang</td>
		<td>' . (($OP_DAYA != "") ? $OP_DAYA : "-") . '</td>
            <td></td></tr>
        <tr><td>10.</td><td>Kondisi pada umumnya</td>
		<td>[ ' . (($OP_KONDISI == "1") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Sangat Baik<br/>
                    [ ' . (($OP_KONDISI == "2") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Baik<br/>
                    [ ' . (($OP_KONDISI == "3") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Sedang<br/>
                    [ ' . (($OP_KONDISI == "4") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Jelek
		</td>
                <td>[ &nbsp;&nbsp; ] 1. Sangat Baik<br/>
                    [ &nbsp;&nbsp; ] 2. Baik<br/>
                    [ &nbsp;&nbsp; ] 3. Sedang<br/>
                    [ &nbsp;&nbsp; ] 4. Jelek
		</td>
		</tr>
	<tr><td>11.</td><td>Konstruksi</td>
		<td>
			[ ' . (($OP_KONSTRUKSI == "1") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Baja<br/>
			[ ' . (($OP_KONSTRUKSI == "2") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Beton<br/>
			[ ' . (($OP_KONSTRUKSI == "3") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Batu Bata<br/>
			[ ' . (($OP_KONSTRUKSI == "4") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Kayu
		</td>
                <td>
			[ &nbsp;&nbsp; ] 1. Baja<br/>
			[ &nbsp;&nbsp; ] 2. Beton<br/>
			[ &nbsp;&nbsp; ] 3. Batu Bata<br/>
			[ &nbsp;&nbsp; ] 4. Kayu
		</td>
	</tr>
	<tr><td>12.</td><td>Atap</td>
		<td>
			[ ' . (($OP_ATAP == "1") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Decrabon/Beton/Gtg Glazur<br/>
			[ ' . (($OP_ATAP == "2") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Gtg Beton/Aluminium<br/>
			[ ' . (($OP_ATAP == "3") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Gtg Biasa/Sirap<br/>
			[ ' . (($OP_ATAP == "4") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Asbes<br/>
			[ ' . (($OP_ATAP == "5") ? "V" : "&nbsp;&nbsp;") . ' ] 5. Seng
		</td>
        <td>
			[ &nbsp;&nbsp; ] 1. Decrabon/Beton/Gtg Glazur<br/>
			[ &nbsp;&nbsp; ] 2. Gtg Beton/Aluminium<br/>
			[ &nbsp;&nbsp; ] 3. Gtg Biasa/Sirap<br/>
			[ &nbsp;&nbsp; ] 4. Asbes<br/>
			[ &nbsp;&nbsp; ] 5. Seng
		</td>
	</tr>
	<tr><td>13.</td><td>Dinding</td>
		<td>
			[ ' . (($OP_DINDING == "1") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Kaca/Aluminium<br/>
			[ ' . (($OP_DINDING == "2") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Beton<br/>
			[ ' . (($OP_DINDING == "3") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Batu Bata/Conblok<br/>
			[ ' . (($OP_DINDING == "4") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Kayu<br/>
			[ ' . (($OP_DINDING == "5") ? "V" : "&nbsp;&nbsp;") . ' ] 5. Seng<br/>
			[ ' . (($OP_DINDING == "6") ? "V" : "&nbsp;&nbsp;") . ' ] 6. Tidak ada
		</td>
                <td>
			[ &nbsp;&nbsp; ] 1. Kaca/Aluminium<br/>
			[ &nbsp;&nbsp; ] 2. Beton<br/>
			[ &nbsp;&nbsp; ] 3. Batu Bata/Conblok<br/>
			[ &nbsp;&nbsp; ] 4. Kayu<br/>
			[ &nbsp;&nbsp; ] 5. Seng<br/>
			[ &nbsp;&nbsp; ] 6. Tidak ada
		</td>
	</tr>
	<tr><td>14.</td><td>Lantai</td>
		<td>
			[ ' . (($OP_LANTAI == "1") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Marmer<br/>
			[ ' . (($OP_LANTAI == "2") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Keramik<br/>
			[ ' . (($OP_LANTAI == "3") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Teraso<br/>
			[ ' . (($OP_LANTAI == "4") ? "V" : "&nbsp;&nbsp;") . ' ] 4. Ubin PC/Papan<br/>
			[ ' . (($OP_LANTAI == "5") ? "V" : "&nbsp;&nbsp;") . ' ] 5. Semen
		</td>
                <td>
			[ &nbsp;&nbsp; ] 1. Marmer<br/>
			[ &nbsp;&nbsp; ] 2. Keramik<br/>
			[ &nbsp;&nbsp; ] 3. Teraso<br/>
			[ &nbsp;&nbsp; ] 4. Ubin PC/Papan<br/>
			[ &nbsp;&nbsp; ] 5. Semen
		</td>
	</tr>
	<tr><td>15.</td><td>Langit-langit</td>
		<td>
			[ ' . (($OP_LANGIT == "1") ? "V" : "&nbsp;&nbsp;") . ' ] 1. Akustik/Jati<br/>
			[ ' . (($OP_LANGIT == "2") ? "V" : "&nbsp;&nbsp;") . ' ] 2. Triplek/Asbes/Bambu<br/>
			[ ' . (($OP_LANGIT == "3") ? "V" : "&nbsp;&nbsp;") . ' ] 3. Tidak ada
		</td>
        <td>
			[ &nbsp;&nbsp; ] 1. Akustik/Jati<br/>
			[ &nbsp;&nbsp; ] 2. Triplek/Asbes/Bambu<br/>
			[ &nbsp;&nbsp; ] 3. Tidak ada
		</td>
	</tr>
        </table>';
    $strHTML .= '<br pagebreak="true"/>';
    $strHTML .= '<table border="1" width="700" cellspacing="0" cellpadding="4">
	<tr><td colspan="4"><br>B. FASILITAS</td></tr>
	<tr><td width="40">16.</td>
            <td width="180">Jumlah AC</td>
            <td width="240">[ ' . (($FOP_AC_SPLIT != "") ? $FOP_AC_SPLIT : "0") . ' ] Split <br/>
                [ ' . (($FOP_AC_WINDOW != "") ? $FOP_AC_WINDOW : "0") . ' ] Window 
            </td>
            <td width="240">[ ...... ] Split <br/>
                [ ...... ] Window 
            </td>
	</tr>
	<tr><td>17.</td><td>AC sentral</td>
		<td>
			[ ' . (($FOP_AC_CENTRAL == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Ada <br/>
			[ ' . (($FOP_AC_CENTRAL == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Tidak ada
		</td>
        <td>
			[ &nbsp;&nbsp; ] Ada <br/>
			[ &nbsp;&nbsp; ] Tidak ada
		</td>
	</tr>
	<tr><td>18.</td><td>Luas kolam renang</td>
		<td>' . (($FOP_KOLAM_LUAS != "") ? $FOP_KOLAM_LUAS : "0") . '&nbsp; <br/>
                    [ ' . (($FOP_KOLAM_LAPISAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Diplester <br/>
                    [ ' . (($FOP_KOLAM_LAPISAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Dengan pelapis
                </td>
        <td>&nbsp; <br/>
            [&nbsp;&nbsp;&nbsp;&nbsp;] Diplester <br/>
            [&nbsp;&nbsp;&nbsp;&nbsp;] Dengan pelapis
        </td>
	</tr>
    <tr><td>19.</td><td>Luas Perkerasan Halaman</td>
		<td>
			[ ' . (($FOP_PERKERASAN_RINGAN != "") ? $FOP_PERKERASAN_RINGAN : "0") . ' ]Ringan <br>
			[ ' . (($FOP_PERKERASAN_BERAT != "") ? $FOP_PERKERASAN_BERAT : "0") . ' ]Berat <br>
			[ ' . (($FOP_PERKERASAN_SEDANG != "") ? $FOP_PERKERASAN_SEDANG : "0") . ' ]Sedang <br>[ ' . (($FOP_PERKERASAN_PENUTUP != "") ? $FOP_PERKERASAN_PENUTUP : "0") . ' ]Dengan penutup lantai
		</td>
        <td>
			[ ........ ] Ringan<br>
			[ ........ ] Berat<br>
			[ ........ ] Sedang<br>[ ........ ] Dengan penutup lantai
		</td>
	</tr>
	<tr>
        <td>20.</td>
        <td>Jumlah Lapangan Tenis
		</td>
        <td>
			<table border="0">
				<tr>
					<td width="150">Dengan Lampu</td><td>Tanpa Lampu</td>
				</tr>
				<tr>
					<td>[ ' . (($FOP_TENIS_LAMPU_BETON != "") ? $FOP_TENIS_LAMPU_BETON : "0") . ' ] Beton</td><td>[ ' . (($FOP_TENIS_TANPA_LAMPU_BETON != "") ? $FOP_TENIS_TANPA_LAMPU_BETON : "0") . ' ]</td>
				</tr>
				<tr>
					<td>[ ' . (($FOP_TENIS_LAMPU_ASPAL != "") ? $FOP_TENIS_LAMPU_ASPAL : "0") . ' ] Aspal</td><td>[ ' . (($FOP_TENIS_TANPA_LAMPU_ASPAL != "") ? $FOP_TENIS_TANPA_LAMPU_ASPAL : "0") . ' ]</td>
				</tr>
				<tr>
					<td>[ ' . (($FOP_TENIS_LAMPU_TANAH != "") ? $FOP_TENIS_LAMPU_TANAH : "0") . ' ] Tanah liat/Rumput</td><td>[ ' . (($FOP_TENIS_TANPA_LAMPU_TANAH != "") ? $FOP_TENIS_TANPA_LAMPU_TANAH : "0") . ' ]</td>
				</tr>
			</table>
        </td>
        <td>
			<table border="0">
				<tr>
					<td width="150">Dengan Lampu</td><td>Tanpa Lampu</td>
				</tr>
				<tr>
					<td>[.........] Beton</td><td>[.........]</td>
				</tr>
				<tr>
					<td>[.........] Aspal</td><td>[.........]</td>
				</tr>
				<tr>
					<td>[.........] Tanah liat/Rumput</td><td>[.........]</td>
				</tr>
			</table>
		</td>
    </tr>
	<tr>
        <td>21.</td>
        <td>Jumlah Lift</td>
        <td>[ ' . (($FOP_LIFT_PENUMPANG != "") ? $FOP_LIFT_PENUMPANG : "0") . ' ] Penumpang<br/>
		[ ' . (($FOP_LIFT_KAPSUL != "") ? $FOP_LIFT_KAPSUL : "0") . ' ] Kapsul<br/>
                [ ' . (($FOP_LIFT_BARANG != "") ? $FOP_LIFT_BARANG : "0") . ' ] Barang
        </td>
        <td>[.........] Penumpang<br/>
            [.........] Kapsul<br/>
            [.........] Barang
        </td>
    </tr>
	<tr>
        <td>22.</td>
        <td>Jumlah Tangga Berjalan</td>
        <td>
			[ ' . (($FOP_ESKALATOR_SEMPIT != "") ? $FOP_ESKALATOR_SEMPIT : "0") . ' ] Lebar &lt;= 0,8m<br/>
                            [ ' . (($FOP_ESKALATOR_LEBAR != "") ? $FOP_ESKALATOR_LEBAR : "0") . ' ]Lebar  &gt; 0,8m
        </td>
        <td>
			[.........] Lebar &lt;= 0,8m <br/>
                        [.........] Lebar  &gt; 0,8m
		</td>
    </tr>
	<tr>
        <td>23.</td>
        <td>Panjang Pagar</td>
        <td>' . (($PAGAR_BESI_PANJANG > 0) ? $PAGAR_BESI_PANJANG : $PAGAR_BATA_PANJANG) . '&nbsp;&nbsp;<br/>[ ' . (($PAGAR_BESI_PANJANG > 0) ? "V" : "&nbsp;&nbsp;") . ' ] Baja / Besi <br/> [ ' . (($PAGAR_BATA_PANJANG > 0) ? "V" : "&nbsp;&nbsp;") . ' ] Bata / Batako</td>
        <td>&nbsp;&nbsp;&nbsp;<br/>[&nbsp;&nbsp;&nbsp;&nbsp;] Baja / Besi <br/>[&nbsp;&nbsp;&nbsp;&nbsp;] Bata / Batako</td>
    </tr>
	<tr>
        <td>24.</td>
        <td>Pemadam Kebakaran</td>
        <td>
			[ ' . (($PEMADAM_HYDRANT == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Hydrant<br/>
			[ ' . (($PEMADAM_SPRINKLER == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Sprinkler<br/>
			[ ' . (($PEMADAM_FIRE_ALARM == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Fire Alarm
        </td>
        <td>
			[ &nbsp;&nbsp; ] Hydrant<br/>
			[ &nbsp;&nbsp; ] Sprinkler<br/>
			[ &nbsp;&nbsp; ] Fire Alarm
        </td>
    </tr>
	<tr>
        <td>25.</td>
        <td>Jml Saluran Pswt PABX</td>
        <td>
			' . (($FOP_SALURAN != "" || $FOP_SALURAN != "0") ? $FOP_SALURAN : "0") . '
        </td>
        <td>
		</td>
    </tr>
	<tr>
        <td>26.</td>
        <td>Kedalaman Sumur Artesis (m)</td>
        <td>
			' . (($FOP_SUMUR != "" || $FOP_SUMUR != "0") ? $FOP_SUMUR : "0") . ' 
        </td>
        <td>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
    </tr>
    <tr><td colspan="4">C. DATA TAMBAHAN</td></tr>';
    if ($OP_PENGGUNAAN == '2') {
        $strHTML .= '<tr><td colspan="4">Bangunan Perkantoran Swasta / Gedung pemerintah</td></tr>
    <tr>
        <td>27.</td>
        <td>Kelas Bangunan</td>
        <td>[ ' . (($JPB2_KELAS_BANGUNAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 1<br/>
            [ ' . (($JPB2_KELAS_BANGUNAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 2<br/>
            [ ' . (($JPB2_KELAS_BANGUNAN == "3") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 3<br/>
            [ ' . (($JPB2_KELAS_BANGUNAN == "4") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 4
        </td>
        <td>[ &nbsp;&nbsp; ] Kelas 1<br/>
        [ &nbsp;&nbsp; ] Kelas 2<br/>
        [ &nbsp;&nbsp; ] Kelas 3<br/>
        [ &nbsp;&nbsp; ] Kelas 4
        </td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '3') {
        $strHTML .= '<tr><td colspan="4">Bangunan Pabrik / Bengkel / Gudang / Pertanian</td></tr>
	<tr>
        <td>27.</td>
        <td>Tinggi Kolom (m)</td>
        <td>
			' . (($JPB3_TINGGI_KOLOM != "" || $JPB3_TINGGI_KOLOM > 0) ? $JPB3_TINGGI_KOLOM : "0") . '
        </td>
        <td>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
    </tr>
	<tr>
        <td>28.</td>
        <td>Lebar Bentang (m)</td>
        <td>
			' . (($JPB3_LEBAR_BENTANG != "" || $JPB3_LEBAR_BENTANG > 0) ? $JPB3_LEBAR_BENTANG : "0") . '
        </td>
        <td></td>
    </tr>
	<tr>
        <td>29.</td>
        <td>Daya Dukung Lantai (Kg/m&sup2;)</td>
        <td>
			' . (($JPB3_DAYA_DUKUNG_LANTAI != "" || $JPB3_DAYA_DUKUNG_LANTAI > 0) ? $JPB3_DAYA_DUKUNG_LANTAI : "0") . ' 
        </td>
        <td></td>
    </tr>
	<tr>
        <td>30.</td>
        <td>Keliling Dinding (m)</td>
        <td>
			' . (($JPB3_KELILING_DINDING != "" || $JPB3_KELILING_DINDING > 0) ? $JPB3_KELILING_DINDING : "0") . '
        </td>
        <td></td>
    </tr>
		<tr>
        <td>31.</td>
        <td>Luas Mezzanine (m&sup2;)</td>
        <td>
			' . (($JPB3_LUAS_MEZZANINE != "" || $JPB3_LUAS_MEZZANINE > 0) ? $JPB3_LUAS_MEZZANINE : "0") . '
        </td>
        <td></td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '4') {
        $strHTML .= '<tr><td colspan="4">Bangunan Toko / Apotik / Pasar / Ruko</td></tr>
	<tr>
        <td>27.</td>
        <td>Kelas Bangunan</td>
        <td>
			[ ' . (($JPB4_KELAS_BANGUNAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 1<br/>
			[ ' . (($JPB4_KELAS_BANGUNAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 2<br/>
			[ ' . (($JPB4_KELAS_BANGUNAN == "3") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 3
        </td>
        <td>
			[ &nbsp;&nbsp; ] Kelas 1<br/>
			[ &nbsp;&nbsp; ] Kelas 2<br/>
			[ &nbsp;&nbsp; ] Kelas 3
		</td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '5') {
        $strHTML .= '<tr><td colspan="4">Bangunan Rumah Sakit / Klinik</td></tr>
	<tr>
        <td>27.</td>
        <td>Kelas Bangunan</td>
        <td>[ ' . (($JPB5_KELAS_BANGUNAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 1<br/>
            [ ' . (($JPB5_KELAS_BANGUNAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 2<br/>
            [ ' . (($JPB5_KELAS_BANGUNAN == "3") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 3<br/>
            [ ' . (($JPB5_KELAS_BANGUNAN == "4") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 4
        </td>
        <td>[ &nbsp;&nbsp; ] Kelas 1<br/>
            [ &nbsp;&nbsp; ] Kelas 2<br/>
            [ &nbsp;&nbsp; ] Kelas 3<br/>
            [ &nbsp;&nbsp; ] Kelas 4
        </td>
    </tr>
	<tr>
        <td>28.</td>
        <td>Luas Kmr dg AC Sentral (m&sup2;)</td>
        <td>
			' . (($JPB5_LUAS_KMR_AC_CENTRAL != "" || $JPB5_LUAS_KMR_AC_CENTRAL > 0) ? $JPB5_LUAS_KMR_AC_CENTRAL : "0") . ' 
        </td>
        <td></td>
    </tr>
	<tr>
        <td>29.</td>
        <td>Luas Ruang Lain dgn AC Sentral (m&sup2;)</td>
        <td>
			' . (($JPB5_LUAS_RUANG_AC_CENTRAL != "" || $JPB5_LUAS_RUANG_AC_CENTRAL > 0) ? $JPB5_LUAS_RUANG_AC_CENTRAL : "0") . ' 
        </td>
        <td></td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '6') {
        $strHTML .= '<tr><td colspan="4">Bangunan Olahraga / Rekreasi</td></tr>
	<tr>
        <td>27.</td>
        <td>Kelas Bangunan</td>
        <td>
			[ ' . (($JPB6_KELAS_BANGUNAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 1 <br/>
			[ ' . (($JPB6_KELAS_BANGUNAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 2
        </td>
        <td>
			[ &nbsp;&nbsp; ] Kelas 1 <br/>
			[ &nbsp;&nbsp; ] Kelas 2
		</td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '7') {
        $strHTML .= '<tr><td colspan="4">Bangunan Hotel / Wisma</td></tr>
	<tr>
        <td>27.</td>
        <td>Jenis Hotel</td>
        <td>
			[ ' . (($JPB7_JENIS_HOTEL == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Non Resort <br/>
			[ ' . (($JPB7_JENIS_HOTEL == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Resort
        </td>
        <td>
			[ &nbsp;&nbsp; ] Non Resort <br/>
			[ &nbsp;&nbsp; ] Resort
		</td>
    </tr>
	<tr>
        <td>28.</td>
        <td>Jumlah Bintang</td>
        <td>[ ' . (($JPB7_JUMLAH_BINTANG == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Bintang 5<br/>
            [ ' . (($JPB7_JUMLAH_BINTANG == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Bintang 4<br/>
            [ ' . (($JPB7_JUMLAH_BINTANG == "3") ? "V" : "&nbsp;&nbsp;") . ' ] Bintang 3<br/>
            [ ' . (($JPB7_JUMLAH_BINTANG == "4") ? "V" : "&nbsp;&nbsp;") . ' ] Bintang 1-2<br/>
            [ ' . (($JPB7_JUMLAH_BINTANG == "0") ? "V" : "&nbsp;&nbsp;") . ' ] Non Bintang
        </td>
        <td>[ &nbsp;&nbsp; ] Bintang 5<br/>
            [ &nbsp;&nbsp; ] Bintang 4<br/>
            [ &nbsp;&nbsp; ] Bintang 3<br/>
            [ &nbsp;&nbsp; ] Bintang 1-2<br/>
            [ &nbsp;&nbsp; ] Non Bintang
        </td>
    </tr>
	<tr>
        <td>29.</td>
        <td>Jumlah Kamar</td>
        <td>
			' . (($JPB7_JUMLAH_KAMAR != "" || $JPB7_JUMLAH_KAMAR > 0) ? $JPB7_JUMLAH_KAMAR : "0") . '
        </td>
        <td></td>
    </tr>
	<tr>
        <td>30.</td>
        <td>Luas Kmr dg AC Sentral (m&sup2;)</td>
        <td>
			' . (($JPB7_LUAS_KMR_AC_CENTRAL != "" || $JPB7_LUAS_KMR_AC_CENTRAL > 0) ? $JPB7_LUAS_KMR_AC_CENTRAL : "0") . '
        </td>
        <td></td>
    </tr>
	<tr>
        <td>31.</td>
        <td>Luas Ruang Lain dgn AC Sentral(m&sup2;)</td>
        <td>
			' . (($JPB7_LUAS_RUANG_AC_CENTRAL != "" || $JPB7_LUAS_RUANG_AC_CENTRAL > 0) ? $JPB7_LUAS_RUANG_AC_CENTRAL : "0") . '
        </td>
        <td>
		</td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '8') {
        $strHTML .= '<tr><td colspan="4">Bangunan Pabrik / Bengkel / Gudang / Pertanian</td></tr>
	<tr>
        <td>27.</td>
        <td>Tinggi Kolom (m)</td>
        <td>
			' . (($JPB8_TINGGI_KOLOM != "" || $JPB8_TINGGI_KOLOM > 0) ? $JPB8_TINGGI_KOLOM : "0") . ' 
        </td>
        <td></td>
    </tr>
	<tr>
        <td>28.</td>
        <td>Lebar Bentang (m)</td>
        <td>
			' . (($JPB8_LEBAR_BENTANG != "" || $JPB8_LEBAR_BENTANG > 0) ? $JPB8_LEBAR_BENTANG : "0") . ' 
        </td>
        <td></td>
    </tr>
	<tr>
        <td>29.</td>
        <td>Daya Dukung Lantai (Kg/m&sup2;)</td>
        <td>
			' . (($JPB8_DAYA_DUKUNG_LANTAI != "" || $JPB8_DAYA_DUKUNG_LANTAI > 0) ? $JPB8_DAYA_DUKUNG_LANTAI : "0") . ' 
        </td>
        <td></td>
    </tr>
	<tr>
        <td>30.</td>
        <td>Keliling Dinding (m)</td>
        <td>
			' . (($JPB8_KELILING_DINDING != "" || $JPB8_KELILING_DINDING > 0) ? $JPB8_KELILING_DINDING : "0") . '
        </td>
        <td></td>
    </tr>
		<tr>
        <td>31.</td>
        <td>Luas Mezzanine (m&sup2;)</td>
        <td>
			' . (($JPB8_LUAS_MEZZANINE != "" || $JPB8_LUAS_MEZZANINE > 0) ? $JPB8_LUAS_MEZZANINE : "0") . ' 
        </td>
        <td></td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '9') {
        $strHTML .= '<tr><td colspan="4">Bangunan Perkantoran Swasta / Gedung Pemerintah</td></tr>
	<tr>
        <td>27.</td>
        <td>Kelas Bangunan</td>
        <td>
	[ ' . (($JPB9_KELAS_BANGUNAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 1<br/>
	[ ' . (($JPB9_KELAS_BANGUNAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 2<br/>
	[ ' . (($JPB9_KELAS_BANGUNAN == "3") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 3<br/>
	[ ' . (($JPB9_KELAS_BANGUNAN == "4") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 4
        </td>
        <td>[ &nbsp;&nbsp; ] Kelas 1<br/>
            [ &nbsp;&nbsp; ] Kelas 2<br/>
            [ &nbsp;&nbsp; ] Kelas 3<br/>
            [ &nbsp;&nbsp; ] Kelas 4
        </td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '12') {
        $strHTML .= '<tr><td colspan="4">Bangunan Parkir</td></tr>
	<tr>
        <td>27.</td>
        <td>Tipe Bangunan</td>
        <td>[ ' . (($JPB12_TIPE_BANGUNAN == "4") ? "V" : "&nbsp;&nbsp;") . ' ] Tipe 4<br/>
            [ ' . (($JPB12_TIPE_BANGUNAN == "3") ? "V" : "&nbsp;&nbsp;") . ' ] Tipe 3<br/>
            [ ' . (($JPB12_TIPE_BANGUNAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Tipe 2<br/>
            [ ' . (($JPB12_TIPE_BANGUNAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Tipe 1
        </td>
        <td>[ &nbsp;&nbsp; ] Tipe 4<br/>
            [ &nbsp;&nbsp; ] Tipe 3<br/>
            [ &nbsp;&nbsp; ] Tipe 2<br/>
            [ &nbsp;&nbsp; ] Tipe 1
        </td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '13') {
        $strHTML .= '<tr><td colspan="4">Bangunan Apartemen</td></tr>
	<tr>
        <td>27.</td>
        <td>Kelas Bangunan</td>
        <td>[ ' . (($JPB13_KELAS_BANGUNAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 1<br/>
            [ ' . (($JPB13_KELAS_BANGUNAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 2<br/>
            [ ' . (($JPB13_KELAS_BANGUNAN == "3") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 3<br/>
            [ ' . (($JPB13_KELAS_BANGUNAN == "4") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 4
        </td>
        <td>[ &nbsp;&nbsp; ] Kelas 1<br/>
            [ &nbsp;&nbsp; ] Kelas 2<br/>
            [ &nbsp;&nbsp; ] Kelas 3<br/>
            [ &nbsp;&nbsp; ] Kelas 4
        </td>
    </tr>
	<tr>
        <td>28.</td>
        <td>Jumlah Apartemen</td>
        <td>
			' . (($JPB13_JUMLAH_APARTEMEN != "" || $JPB13_JUMLAH_APARTEMEN > 0) ? $JPB13_JUMLAH_APARTEMEN : "0") . '
        </td>
        <td>
		</td>
    </tr>
	<tr>
        <td>29.</td>
        <td>Luas Apt dg AC Sentral</td>
        <td>
			' . (($JPB13_LUAS_APARTEMEN_AC_CENTRAL != "" || $JPB13_LUAS_APARTEMEN_AC_CENTRAL > 0) ? $JPB13_LUAS_APARTEMEN_AC_CENTRAL : "0") . '
        </td>
        <td>
		</td>
    </tr>
	<tr>
        <td>30.</td>
        <td>Luas Rg Lain dg AC Sentral</td>
        <td>
			' . (($JPB13_LUAS_RUANG_AC_CENTRAL != "" || $JPB13_LUAS_RUANG_AC_CENTRAL > 0) ? $JPB13_LUAS_RUANG_AC_CENTRAL : "0") . '
        </td>
        <td>
		</td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '15') {
        $strHTML .= '<tr><td colspan="4">Bangunan Tangki Minyak</td></tr>
	<tr>
        <td>27.</td>
        <td>Kapasitas Tangki</td>
        <td>
			' . (($JPB15_TANGKI_MINYAK_KAPASITAS != "" || $JPB15_TANGKI_MINYAK_KAPASITAS > 0) ? $JPB15_TANGKI_MINYAK_KAPASITAS : "0") . '
        </td>
        <td>
		</td>
    </tr>
	<tr>
        <td>28.</td>
        <td>Letak Tangki</td>
        <td>
			[ ' . (($JPB15_TANGKI_MINYAK_LETAK == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Diatas tanah <br/>
			[ ' . (($JPB15_TANGKI_MINYAK_LETAK == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Dibawah tanah
        </td>
        <td>
			[ &nbsp;&nbsp; ] Diatas tanah <br/>
			[ &nbsp;&nbsp; ] Dibawah tanah
		</td>
    </tr>';
    }
    if ($OP_PENGGUNAAN == '16') {
        $strHTML .= '<tr><td colspan="4">Bangunan Gedung Sekolah</td></tr>
	<tr>
        <td>27.</td>
        <td>Kelas Bangunan</td>
        <td>
			[ ' . (($JPB16_KELAS_BANGUNAN == "1") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 1 <br/>
			[ ' . (($JPB16_KELAS_BANGUNAN == "2") ? "V" : "&nbsp;&nbsp;") . ' ] Kelas 2
        </td>
        <td>
			[ &nbsp;&nbsp; ] Kelas 1 <br/>
			[ &nbsp;&nbsp; ] Kelas 2
		</td>
    </tr>';
    }

    $strHTML .= '</table>
<table border="0" width="700" cellspacing="0" cellpadding="5">
    <tr>
        <td colspan="3"><br/>D. IDENTITAS PENDATA</td>
    </tr>
    <tr>
        <td width="300">NAMA / NIP PETUGAS PENDATA<br/><br/><br/><br/><br/>(..........................................)<br>NIP : ...................................<br/></td>
        <td width="200">TANGGAL<br/><br/><br/><br/><br/>(..........................................)</td>
        <td width="200">TANDA TANGAN<br/><br/><br/><br/><br/>(..........................................)</td>
    </tr>
    <tr>
        <td colspan="3">E. PEJABAT YANG BERWENANG</td>
    </tr>
    <tr>
        <td width="300">NAMA / NIP PEJABAT<br/><br/><br/><br/><br/>(..........................................)<br>NIP : ...................................</td>
        <td width="200">TANGGAL<br/><br/><br/><br/><br/>(..........................................)</td>
        <td width="200">TANDA TANGAN<br/><br/><br/><br/><br/>(..........................................)</td>
    </tr>
</table>
';

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

$tTime         = time();
$paymentDt;
$params     = @isset($_REQUEST['req']) ? $_REQUEST['req'] : '';
$p             = base64_decode($params);
$json         = new Services_JSON();
$prm         = $json->decode($p);
$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
// print_r($prm);exit;
$appConfig     = $User->GetAppConfig($prm->appID);

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
//$pdf->Image($sRootPath.'image/Logo_okus_bw.jpg', 6, 3, 20, '', '', '', '', false, 300, '', false);
//$pdf->Image($sRootPath.'function/PBB/consol/cthgbr.jpg', 120, 300, 70, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($HTML, true, false, false, false, '');
$pdf->SetAlpha(0.3);

//Close and output PDF document
$filename = date("Ymdhis") . '.pdf';
if (strlen($prm->NOP) == 18) $filename = $prm->NOP . '.pdf';
$pdf->Output($filename, 'I');
