<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
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

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
//$appConfig = $User->GetAppConfig($appID);
$arConfig = $User->GetModuleConfig('mLkt');

$dataNotaris = "";
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

function getConfigValue($id, $key) {
    global $DBLink;
    //$id= $appID;
    //$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
    $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function mysql2json($mysql_result, $name) {
    $json = "{\n'$name': [\n";
    $field_names = array();
    $fields = mysqli_num_fields($mysql_result);
    for ($x = 0; $x < $fields; $x++) {
        $field_name = mysqli_fetch_field($mysql_result);
        if ($field_name) {
            $field_names[$x] = $field_name->name;
        }
    }
    $rows = mysqli_num_rows($mysql_result);
    for ($x = 0; $x < $rows; $x++) {
        $row = mysqli_fetch_array($mysql_result);
        $json.="{\n";
        for ($y = 0; $y < count($field_names); $y++) {
            $json.="'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
            if ($y == count($field_names) - 1) {
                $json.="\n";
            } else {
                $json.=",\n";
            }
        }
        if ($x == $rows - 1) {
            $json.="\n}\n";
        } else {
            $json.="\n},\n";
        }
    }
    $json.="]\n}";
    return($json);
}

function getData($idssb) {
    global $data, $DBLink, $dataNotaris;

    $query = "SELECT * FROM cppmod_ssb_berkas LEFT JOIN cppmod_ssb_doc ON cppmod_ssb_doc.CPM_SSB_ID = cppmod_ssb_berkas.CPM_SSB_DOC_ID  WHERE CPM_BERKAS_ID='$idssb'";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo $query . "<br>";
        echo mysqli_error($DBLink);
    }
    $json = new Services_JSON();
    $dataNotaris = $json->decode(mysql2json($res, "data"));
    $dt = $dataNotaris->data[0];
    return $dt;
}

function getHTML($idssb, $initData, $fileLogo) {
    global $uname, $NOP, $appId, $arConfig,$sRootPath;
    $data = getData($idssb);
    //print_r($data);
    $data->CPM_BERKAS_HARGA_TRAN = (int) $data->CPM_BERKAS_HARGA_TRAN;
    // die(var_dump($data));
    //echo $fileLogo;exit;
    //print_r($initData); exit;
    $lampiran = $data->CPM_BERKAS_LAMPIRAN;
    $header_berkas = getConfigValue($appId, 'C_HEADER_DISPOSISI');
    $alamat_berkas = getConfigValue($appId, 'C_ALAMAT_DISPOSISI');

	$fileLogo = getConfigValue($appId, 'LOGO_CETAK_PDF');
    $buktiTitle = "BUKTI PENERIMAAN / LEMBAR EKSPEDISI BERKAS BPHTB";

    $parse1 = "";
    //Koordinator Pendataan
    $bKoorPend = "<td width=\"343\" height=\"150\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<table border=\"0\" cellspacing=\"3\" width=\"250\">
					<tr><td align=\"center\">Petugas Input Data</td></tr>
					<tr><td align=\"left\"><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td align=\"left\">___________________________________<br />NIP : </td></tr>
					</table>
				</td>";
    //Koordinator Penilaian			
    $bKoorPen = "<td width=\"343\" height=\"150\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<table border=\"0\" cellspacing=\"3\" width=\"250\">
					<tr><td align=\"center\">Petugas Persetujuan</td></tr>
					<tr><td><font size=\"-1\">Tanggal :</font></td></tr> 
					<tr><td><br><br><br><br></td></tr>
					<tr><td>___________________________________<br />NIP : </td></tr>
					</table>
				</td>";



    $openTable = "<table border=\"1\" cellpadding=\"12\">
					<tr>";
    $closeTable = " </tr></table>";

    $parse1 = $bKoorPend . $bKoorPen;
    $noplus = 3;
    $parse2 = "<td><table border=\"1\" cellpadding=\"2\" width=\"100%\">
                <tr>
                    <td rowspan=\"2\" align=\"center\" width=\"30\">No</td>
                    <td rowspan=\"2\" align=\"center\" width=\"160\">Petugas</td>
                    <td colspan=\"2\" align=\"center\">Berkas Masuk</td>
                    <td colspan=\"2\" align=\"center\">Berkas Keluar</td>
                    <td rowspan=\"2\" align=\"center\">Keterangan</td>
                </tr>
                <tr>
                    <td align=\"center\">Tanggal/Pukul</td>
                    <td align=\"center\">Paraf</td>
                    <td align=\"center\">Tanggal/Pukul</td>
                    <td align=\"center\">Paraf</td>
                </tr>
                <tr>
                    <td align=\"right\">1.</td>
                    <td>Petugas Loket Penerimaan</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td align=\"right\">2.</td>
                    <td>Kasubbid Pelayanan dan Pengolahan Data</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <td align=\"right\">3.</td>
                    <td>Kasubbid BPHTB</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td align=\"right\">4.</td>
                    <td>KABID PBB dan BPHTB</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td align=\"right\">5.</td>
                    <td>Sekretaris Badan</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td align=\"right\">6.</td>
                    <td>Petugas Loket Pengembalian</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                </table></td>";

    $lampiran = array();
    $jnsPerolehan = $initData['CPM_BERKAS_JNS_PEROLEHAN'];
    $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD) </td></tr>" : "";
    
    $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB Lunas (Terdapat Kode Bayar)</td></tr>" : "";
    
    $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "905") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Lunas PBB 5 tahun terakhir (Informasi data pembayaran)</td></tr>" : "";

    if ($jnsPerolehan == 1) {

        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "906") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat  Surat keterangan jual beli tanah / Bukti transaksi dilegalisir</td></tr>" : "";

        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "908") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> daftar harga (Pricelist) dalam hal pembelian dan pengembangan (perumahan/kavlingan) dilegalisir</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 2) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "912") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";

    } elseif ($jnsPerolehan == 3){
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "911") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "914") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Pertanyaan Hibah/Surat keterangan Hibah dilegalisir</td></tr>" : "";

    } elseif ($jnsPerolehan == 4) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "916") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP Para ahli Waris/penerima Hibah Wasiat dilegalisir</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "917") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat/Keterangan Kematian dilegalisir</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "918") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan hibah dilegalisir</td></tr>" : "";
        
    }  elseif ($jnsPerolehan == 5) {

        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "916") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP Para ahli Waris/penerima Hibah Wasiat dilegalisir</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "917") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat/Keterangan Kematian dilegalisir</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "925") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan Waris dilegalisir</td></tr>" : "";
        
    }  elseif ($jnsPerolehan == 6) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "926") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta Pendirian Perusahaan yang terbaru dilegalisir</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "928") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya dilegalisir</td></tr>" : "";
    }  elseif ($jnsPerolehan == 7) {

        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "916") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">S KTP para ahli waris dilegalisir</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "917") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat/Keterangan Kematian dilegalisir</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "925") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan Waris dilegalisir</td></tr>" : "";
        
    }  elseif ($jnsPerolehan == 8) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "929") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Kwitansi lelang/Risalah Lelang dilegalisir</td></tr>" : "";
        
    }  elseif ($jnsPerolehan == 9) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "930") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Keputusan Hakim/Pengadilan dilegalisir</td></tr>" : "";

    }  elseif ($jnsPerolehan == 10) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "930") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Keputusan Hakim/Pengadilan dilegalisir</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "926") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta Pendirian Perusahaan yang terbaru dilegalisir</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "928") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">
             Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya dilegalisir</td></tr>" : "";
        
        
    }  elseif ($jnsPerolehan == 11) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "926") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta Pendirian Perusahaan yang terbaru dilegalisir</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "928") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya dilegalisir</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 12) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "926") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta Pendirian Perusahaan yang terbaru dilegalisir</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "928") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya dilegalisir</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 13) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "931") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan Hadiah dari yang mengalihkan hak dilegalisir</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 14) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "932") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank dilegalisir</td></tr>" : "";

    } elseif ($jnsPerolehan == 21) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "933") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pelepasan Hak Atas Tanah atau sejenisnya dari BPN dilegalisir</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 22) {
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "933") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pelepasan Hak Atas Tanah atau sejenisnya dari BPN dilegalisir</td></tr>" : "";
        
    }
    $lampiran[20] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "910") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta</td></tr>" : "";
    $lampiran[21] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "913") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Dokumen Pendukung Lainnya </td></tr>" : "";


    $lamp = implode(" ", $lampiran);
    $arr_jnsPerolehan = array(1=>"Jual Beli",2=>"Tukar Menukar",3=>"Hibah",4=>"Hibah Wasiat", 5=>"Waris", 6=>"Pemasukan dalam perseroan/badan hukum lainnya", 7=>"Pemisahan hak yang mengakibatkan peralihan", 8=>"Penunjukan pemberi dalam lelang", 9=>"Pelaksanaan putusan hakim yang mempunyai kekuatan hukum tetap", 10=>"Penggabungan usaha",11=>"Pelebaran usaha",12=>"Pemekaran usaha",13=>"Hadiah", 14=>"Perolehan hak Rumah Sederhana dan RSS melalui KPR bersubsidi", 21=>"Pemberian hak baru sebagai kelanjutan pelepasan hak", 22=>"Pemberian hak baru diluar pelepasan hak",30=>"1 Wajib Pajak : Jual Beli",31=>"1 Wajib Pajak : Waris",32=>"1 Wajib Pajak : Hibah/Waris",33=>"1 Wajib Pajak : APHB");
    // die(var_dump($data));

    $html = "
	<table border=\"1\" cellpadding=\"5\">
            <tr>
                <td rowspan=\"2\" align=\"center\" width=\"20%\">
                	<!-- <img src=\"{$sRootPath}view/Registrasi/configure/logo/{$fileLogo}\" width=\"70px\" height=\"86px\" > -->
                </td>
                <td align=\"center\" width=\"60%\">
                        <!-- <font size=\"+4\"> --> " . $header_berkas . "<br />
                        <!-- </font> -->
                </td>
                <!--KOSONG-->
                <td rowspan=\"2\" align=\"center\" width=\"20%\">
                    <h2><font size=\"-1\">" . $arr_jnsPerolehan[$data->CPM_BERKAS_JNS_PEROLEHAN] . "</font></h2>
                </td>
            </tr>
            <tr>
                <td align=\"center\">
                    " . $alamat_berkas . "
                </td>
            </tr>
            <tr>            
                <td colspan=\"3\">
                    <table border=\"0\" cellpadding=\"2\" cellspacing=\"5\">
                        <tr><td colspan=\"3\" ALIGN=\"center\"><font size=\"+2\">" . $buktiTitle . "<br /></font></td></tr>
                        <tr>
                            <td>Nomor Pelayanan</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NOPEL . "</td>
                        </tr>
                        <tr>
                            <td>Nama Wajib Pajak</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NAMA_WP . "</td>
                        </tr>
                        <tr>
                            <td>Nomor Telp Wajib Pajak</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_TELP_WP . "</td>
                        </tr>
                        <tr>
                            <td>Tanggal Surat Masuk</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_TANGGAL . "</td>
                        </tr>";
    $html .= "          <tr>
                            <td>NOP</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_NOP . "</td>
                        </tr>
                        <tr>
                            <td>Kelurahan</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_KELURAHAN_OP . "</td>
                        </tr>
                        <tr>
                            <td>Kecamatan</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_KECAMATAN_OP . "</td>
                        </tr>
                        <tr>
                            <td>Persyaratan administrasi</td><td>:</td> 
                            <td width=\"auto\" cellspacing=\"5\">" . ($lampiran != '' ? "<table border='1'>" . $lamp . "</table>" : "") . "</td>
                        </tr>
                        <tr>
                            <td>Harga Transaksi</td><td>:</td>
                            <td>Rp. " . number_format($data->CPM_SSB_AKUMULASI,0) . "</td>
                        </tr>
                    </table>					
		</td>
            </tr>
            <!--SALINAN DISPOSISI-->
            <tr>
                <td colspan=\"3\"><table border=\"0\">
                        <tr>
                            <td><table border=\"1\" cellpadding=\"12\">
                                  <tr>
                                        " . $parse2 . "
                                  </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
	</table>
        <br><br>
        <table border=\"\" cellpadding=\"2\" width=\"100%\">
	        <tr>
		    	<td colspan=\"2\">
                    <table border=\"1\" width=\"70%\">
                        <tr>
                            <td height=\"80px\">Catatan:</td>
                        </tr>
                    </table>
                </td>
		        <td align=\"left\"><br>
			        <font size=\"9\">Dibuat oleh wajib pajak / Kuasa&nbsp;&nbsp;
			        <br>Lampung Selatan, ..................................</font><br><br><br><br>
			        (.........................................................)
		        </td>
	        </tr>
        </table>";

    $html .= "<br><br><table border=\"1\" cellpadding=\"5\">
            <tr>
                <td align=\"center\" width=\"20%\" rowspan=\"2\">
                    <!-- <img src=\"{$sRootPath}view/Registrasi/configure/logo/{$fileLogo}\" width=\"70px\" height=\"86px\" > -->
                 </td>
                <td align=\"center\" width=\"60%\" rowspan=\"2\">
                    <p>" . $header_berkas . "</p><br>
                </td>
                <td align=\"center\" width=\"20%\" rowspan=\"2\">
                    <h2><font size=\"-1\">" . $arr_jnsPerolehan[$data->CPM_BERKAS_JNS_PEROLEHAN] . "</font></h2>
                </td>
            </tr>
            <tr>
            	<td align=\"center\">
                </td>
            </tr>
            <tr>            
                <td colspan=\"3\">
                    <table border=\"0\" cellspacing=\"5\">
                        <tr>
                            <td>Nomor Pelayanan</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NOPEL . "</td>
                        </tr>
                        <tr>
                            <td>NPWP</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NPWP . "</td>
                        </tr>
                        <tr>
                            <td>Nama Wajib Pajak</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NAMA_WP . "</td>
                        </tr>
                        <tr>
                            <td>Nomor Objek Pajak</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_NOP . "</td>
                        </tr>
                        <tr>
                            <td>Tanggal Berkas Masuk</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_TANGGAL . "</td>
                        </tr>
                        <tr>
                        	<td></td>
                        </tr>
                        <tr>
                        	<td></td>
                        	<td></td>
                        	<td></td>
                        	<td>
                        	 Lampung Selatan, ...........................
                        	</td>
                        </tr>
                        <tr>
                        	<td></td>
                        	<td></td>
                        	<td></td>
                        	<td align=\"center\">
                        		<br><br><br><br>
                        		(......................................)
                        	</td>
                        </tr>
                    </table>					
				</td>
            </tr>
	</table>
    <tablle border=\"0\">
        <tr>
            <td align=\"right\" colspan=\"3\" style=\"font-style: italic;font-size:22px\">
                        <br>*Berkas yang tidak diambil setelah 1 bulan di BPPRD bukan tanggung jawab pihak BPPRD
            </td>
        </tr>
    </table>
     ";
    return $html;
    echo $html;
}
function getHTML2($idssb, $initData, $fileLogo) {
    global $uname, $NOP, $appId, $arConfig,$sRootPath;
    $data = getData($idssb);
    //print_r($data);
    $data->CPM_BERKAS_HARGA_TRAN = (int) $data->CPM_BERKAS_HARGA_TRAN;
    // die(var_dump($data));
    //echo $fileLogo;exit;
    //print_r($initData); exit;
    $lampiran = $data->CPM_BERKAS_LAMPIRAN;
    $header_berkas = getConfigValue($appId, 'C_HEADER_DISPOSISI');
    $alamat_berkas = getConfigValue($appId, 'C_ALAMAT_DISPOSISI');
    $buktiTitle = "BUKTI PENERIMAAN / LEMBAR EKSPEDISI BERKAS BPHTB";

    $lampiran = array();

    $jnsPerolehan = $initData['CPM_BERKAS_JNS_PEROLEHAN'];
    if ($jnsPerolehan == 1) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[7] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "8") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Daftar harga/Pricelist dalam hal pembelian dan pengembangan</td></tr>" : "";
        $lampiran[8] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "9") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti transaksi/rincian pembayaran</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 2) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[9] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "10") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Tukar Hak Atas Tanah dari yang mengalihkan hak</td></tr>" : "";
    } elseif ($jnsPerolehan == 3){
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[10] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP Pemberi dan Penerima Hibah yang masih berlaku</td></tr>" : "";
        $lampiran[11] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Pernyataan Hibah/Surat keterangan Hibah yang diketahui oleh Kepala Desa</td></tr>" : "";

    } elseif ($jnsPerolehan == 4) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[12] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "13") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP Para ahli Waris/penerima Hibah Wasiat</td></tr>" : "";
        $lampiran[13] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "14") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat/Keterangan Kematian</td></tr>" : "";
		$lampiran[14] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "15") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan Waris</td></tr>" : "";
        $lampiran[15] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "16") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Kuasa Waris dalam hal Dikuasakan</td></tr>" : "";
        
    }  elseif ($jnsPerolehan == 5) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[12] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "13") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP Para ahli Waris/penerima Hibah Wasiat</td></tr>" : "";
        $lampiran[13] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "14") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat/Keterangan Kematian</td></tr>" : "";
		$lampiran[14] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "15") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan Waris</td></tr>" : "";
        $lampiran[15] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "16") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Kuasa Waris dalam hal Dikuasakan</td></tr>" : "";
        
    }  elseif ($jnsPerolehan == 6) {
		$lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[16] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "17") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta Pendirian Perusahaan yang terbaru</td></tr>" : "";
        $lampiran[17] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "18") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> NPWP Perusahaan</td></tr>" : "";
		$lampiran[18] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "19") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</td></tr>" : "";
    }  elseif ($jnsPerolehan == 7) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[19] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "20") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP para ahli waris</td></tr>" : "";
        $lampiran[20] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "21") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat/keterangan Kematian</td></tr>" : "";
		$lampiran[21] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "22") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan waris</td></tr>" : "";

        
    }  elseif ($jnsPerolehan == 8) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[23] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "24") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Kwitansi lelang/Risalah Lelang</td></tr>" : "";
        
    }  elseif ($jnsPerolehan == 9) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[24] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "25") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Keputusan Hakim/Pengadilan</td></tr>" : "";
    }  elseif ($jnsPerolehan == 10) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[16] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "17") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta Pendirian Perusahaan yang terbaru</td></tr>" : "";
        $lampiran[17] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "18") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> NPWP Perusahaan</td></tr>" : "";
		$lampiran[18] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "19") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</td></tr>" : "";
        
    }  elseif ($jnsPerolehan == 11) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[16] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "17") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta Pendirian Perusahaan yang terbaru</td></tr>" : "";
        $lampiran[17] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "18") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> NPWP Perusahaan</td></tr>" : "";
		$lampiran[18] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "19") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 12) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[16] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "17") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Akta Pendirian Perusahaan yang terbaru</td></tr>" : "";
        $lampiran[17] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "18") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> NPWP Perusahaan</td></tr>" : "";
		$lampiran[18] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "19") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</td></tr>" : "";
        
        
    } elseif ($jnsPerolehan == 13) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[25] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "26") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pernyataan Hadiah dari yang mengalihkan hak</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 14) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[26] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "27") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank</td></tr>" : "";
    } elseif ($jnsPerolehan == 21) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[27] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "28") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat Pelepasan Hak Atas Tanah dari BPN</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 22) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 30) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[7] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "8") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Daftar harga/Pricelist dalam hal pembelian dan pengembangan</td></tr>" : "";
        $lampiran[8] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "9") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti transaksi/rincian pembayaran</td></tr>" : "";
        
    } elseif ($jnsPerolehan == 31) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[12] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "13") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP Para ahli Waris/penerima Hibah Wasiat</td></tr>" : "";
        $lampiran[13] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "14") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat/Keterangan Kematian</td></tr>" : "";
		$lampiran[14] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "15") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan Waris</td></tr>" : "";
        $lampiran[15] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "16") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Kuasa Waris dalam hal Dikuasakan</td></tr>" : "";
        
    }elseif ($jnsPerolehan == 32) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[10] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP Pemberi dan Penerima Hibah yang masih berlaku</td></tr>" : "";
        $lampiran[11] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Pernyataan Hibah/Surat keterangan Hibah yang diketahui oleh Kepala Desa</td></tr>" : "";
        
    }elseif ($jnsPerolehan == 33) {
        $lampiran[0] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Formulir penyampaian SSPD BPHTB</td></tr>" : "";
        $lampiran[1] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">SSPD-BPHTB</td></tr>" : "";
        $lampiran[2] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP WP yang masih berlaku/Keterangan domisili</td></tr>" : "";
        $lampiran[3] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">Surat kuasa dari WP yang bermaterai dan  KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td></tr>" : "";
        $lampiran[4] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> SPPT yang sedang berjalan</td></tr>" : "";
        $lampiran[5] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Bukti Pembayaran/Lunas PBB dari tahun 2009</td></tr>" : "";
        $lampiran[6] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\">, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td></tr>" : "";
        $lampiran[19] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "20") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> KTP para ahli waris</td></tr>" : "";
        $lampiran[20] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "21") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat/keterangan Kematian</td></tr>" : "";
		$lampiran[21] = (strpos($initData['CPM_BERKAS_LAMPIRAN'], "22") !== false) ? "<tr><td width=\"10\">-</td><td width=\"400\" align=\"justify\"> Surat Pernyataan waris</td></tr>" : "";
        
    }



    $lamp = implode(" ", $lampiran);
    $arr_jnsPerolehan = array(1=>"Jual Beli",2=>"Tukar Menukar",3=>"Hibah",4=>"Hibah Wasiat", 5=>"Waris", 6=>"Pemasukan dalam perseroan/badan hukum lainnya", 7=>"Pemisahan hak yang mengakibatkan peralihan", 8=>"Penunjukan pemberi dalam lelang", 9=>"Pelaksanaan putusan hakim yang mempunyai kekuatan hukum tetap", 10=>"Penggabungan usaha",11=>"Pelebaran usaha",12=>"Pemekaran usaha",13=>"Hadiah", 14=>"Perolehan hak Rumah Sederhana dan RSS melalui KPR bersubsidi", 21=>"Pemberian hak baru sebagai kelanjutan pelepasan hak", 22=>"Pemberian hak baru diluar pelepasan hak",30=>"1 Wajib Pajak : Jual Beli",31=>"1 Wajib Pajak : Waris",32=>"1 Wajib Pajak : Hibah/Waris",33=>"1 Wajib Pajak : APHB");

    $html .= "<table border=\"1\" cellpadding=\"5\">
            <tr>
                <td align=\"center\" width=\"20%\" rowspan=\"2\"></td>
                <td align=\"center\" width=\"60%\" rowspan=\"2\">
                    <p>" . $header_berkas . "</p><br>
                </td>
                <td align=\"center\" width=\"20%\" rowspan=\"2\">
                    <h2><font size=\"-1\">" . $arr_jnsPerolehan[$data->CPM_BERKAS_JNS_PEROLEHAN] . "</font></h2>
                </td>
            </tr>
            <tr>
            	<td align=\"center\">
                </td>
            </tr>
            <tr>            
                <td colspan=\"3\">
                    <table border=\"0\" cellspacing=\"5\">
                        <tr>
                            <td>Nomor Pelayanan</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NOPEL . "</td>
                        </tr>
                        <tr>
                            <td>NPWP</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NPWP . "</td>
                        </tr>
                        <tr>
                            <td>Nama Wajib Pajak</td><td width=\"20\">:</td>
                            <td>" . $data->CPM_BERKAS_NAMA_WP . "</td>
                        </tr>
                        <tr>
                            <td>Nomor Objek Pajak</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_NOP . "</td>
                        </tr>
                        <tr>
                            <td>Tanggal Berkas Masuk</td><td>:</td>
                            <td>" . $data->CPM_BERKAS_TANGGAL . "</td>
                        </tr>
                        <tr>
                        	<td></td>
                        </tr>
                        <tr>
                        	<td></td>
                        	<td></td>
                        	<td></td>
                        	<td>
                        	<br>
                        	<br>
                        	 Lampung Selatan, ...........................
                        	</td>
                        </tr>
                        <tr>
                        	<td></td>
                        	<td></td>
                        	<td></td>
                        	<td align=\"center\">
                        		<br><br><br>
                        		(......................................)
                        	</td>
                        </tr>
                    </table>					
				</td>
            </tr>
	</table>
     ";
    return $html;
    echo $html;
}

function getInitData($id = "") {
    global $DBLink;

    if ($id == '')
        return getDataDefault();

    $qry = "select * from cppmod_ssb_berkas where CPM_BERKAS_ID='{$id}'";

    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
        return getDataDefault();
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_BERKAS_TANGGAL'] = substr($row['CPM_BERKAS_TANGGAL'], 8, 2) . '-' . substr($row['CPM_BERKAS_TANGGAL'], 5, 2) . '-' . substr($row['CPM_BERKAS_TANGGAL'], 0, 4);
            return $row;
        }
    }
}

function getDataDefault() {
    $default = array('CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '',
        'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '',
        'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => '');
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$NOP = "";
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('');
$pdf->SetSubject('');
$pdf->SetKeywords('');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5, 1.5, 5);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(5);
//set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);
// ---------------------------------------------------------
// set font
//$pdf->SetFont('helvetica', 'B', 20);
// add a page
//$pdf->AddPage();
//$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";


$d_row = $json->decode($q->svcId);
$v = count($d_row);
$appId = $q->appId;
$fileLogo = getConfigValue($appId, 'LOGO_CETAK_PDF');
//echo $fileLogo;exit;

//$pdf->AddPage('P', 'A4');
//$HTML = getHTML($idssb, $initData, $fileLogo);
//$pdf->writeHTML($HTML, true, false, false, false, '');
//$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 7, 19, 35, '', '', '', '', false, 300, '', false);
//$pdf->SetAlpha(0.3);

for ($i = 0; $i < $v; $i++) {
    $idssb = $d_row[$i]->id;
    $pdf->AddPage('P', 'F4');
    $initData = getInitData($idssb);
    $HTML = getHTML($idssb, $initData, $fileLogo);
    $pdf->writeHTML($HTML, true, false, false, false, '');
    //$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 7, 19, 35, '', '', '', '', false, 300, '', false);
    $pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 16, 4, 18, '', '', '', '', false, 300, '', false);
    $pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 16, 194	, 18, '', '', '', '', false, 300, '', false);
    $pdf->SetAlpha(0.3);
}


// -----------------------------------------------------------------------------
//Close and output PDF document
$pdf->Output($idssb . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
?>
