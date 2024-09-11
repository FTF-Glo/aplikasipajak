<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB/print', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/ctools.php"); 
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/payment/error-messages.php"); 

require_once($sRootPath."inc/report/eng-report.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/payment/nid.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

function doPrint($id, &$arrValues, $tahunCetak) {
	global $paymentDt,$host,$port,$timeOut,$DBLink;
	
	$paymentDt = strftime("%Y%m%d%H%M%S",time());
	
	$arrValues['result'] = true;
	$arrValues['message'] = 'Payment Berhasil !';
	$arrValues['printValue'] = printRequest($id, $strHTML, $tahunCetak);
	$arrValues['HtmlValue'] = $strHTML;
	return true;
}

function printRequest($nop, &$strHTML, $tahunCetak) {
	global $DBLink, $tTime, $modConfig, $appConfig, $sRootPath,$sdata, $Setting, $prm, $tempatPembayaran;
	
	if($tahunCetak == date('Y')) $table = 'cppmod_pbb_sppt_current';
	else $table = "cppmod_pbb_sppt_cetak_{$tahunCetak}";
	
	$strHTML = '';
	$query = "SELECT 
			A.SPPT_TAHUN_PAJAK, A.NOP,
			A.OP_ALAMAT,A.OP_RT,A.OP_RW, A.OP_KELURAHAN, A.OP_KECAMATAN, A.OP_KOTAKAB,
			IFNULL(E.CPM_WP_NAMA,A.WP_NAMA) AS WP_NAMA, IFNULL(E.CPM_WP_ALAMAT,A.WP_ALAMAT) AS WP_ALAMAT, IFNULL(E.CPM_WP_RT,A.WP_RT) AS WP_RT, 
			IFNULL(E.CPM_WP_RW, A.WP_RW) AS WP_RW, IFNULL(E.CPM_WP_KELURAHAN, A.WP_KELURAHAN) AS WP_KELURAHAN, IFNULL(E.CPM_WP_KECAMATAN, A.WP_KECAMATAN) AS WP_KECAMATAN, 
			IFNULL(E.CPM_WP_KOTAKAB, A.WP_KOTAKAB) AS WP_KOTAKAB, IFNULL(E.CPM_WP_KODEPOS, A.WP_KODEPOS) AS WP_KODEPOS,
			A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN, A.OP_KELAS_BUMI, A.OP_KELAS_BANGUNAN, A.OP_NJOP_BUMI, A.OP_NJOP_BANGUNAN, A.OP_NJOP,
			A.OP_NJOPTKP,A.OP_NJKP,
			A.SPPT_TANGGAL_JATUH_TEMPO, A.SPPT_PBB_HARUS_DIBAYAR, A.SPPT_TANGGAL_TERBIT, A.SPPT_TANGGAL_CETAK,
			A.SPPT_PBB_PENGURANGAN, A.SPPT_PBB_PERSEN_PENGURANGAN, A.OP_TARIF, A.SPPT_DOC_ID, A.OP_TARIF,
			A.OP_LUAS_BUMI_BERSAMA, A.OP_LUAS_BANGUNAN_BERSAMA, 
			A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA,                
			A.OP_KELAS_BUMI_BERSAMA, A.OP_KELAS_BANGUNAN_BERSAMA,
			A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA,
			C.CPC_NM_SEKTOR, C.CPC_KD_AKUN, IF(B.CPC_TKL_KDSEKTOR='10','PEDESAAN','PERKOTAAN') AS SEKTOR
			FROM {$table} A 
			LEFT JOIN cppmod_tax_kelurahan B ON A.OP_KELURAHAN_KODE=B.CPC_TKL_ID 
			LEFT JOIN cppmod_pbb_jns_sektor C ON C.CPC_KD_SEKTOR=B.CPC_TKL_KDSEKTOR
			LEFT JOIN (
				SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt WHERE CPM_NOP='{$nop}'
				UNION ALL 
				SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt_final WHERE CPM_NOP='{$nop}'
				UNION ALL 
				SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP='{$nop}'
			) D ON A.NOP=D.CPM_NOP
			LEFT JOIN cppmod_pbb_wajib_pajak E ON E.CPM_WP_ID=D.CPM_WP_NO_KTP
			WHERE A.NOP='{$nop}'";
	$result = mysqli_query($DBLink, $query);
	if ($row = mysqli_fetch_array($result)) {
			        
		$strHTML = "<style>
						#detail_sppt td{padding:4px;background:none} 
						#detail_sppt table{border:none;width:100%}
						#detail_sppt .border{border:1px #999 solid;} 
						#detail_sppt hr{border:0;border-bottom:1px dashed #ccc;background: #999;}
						#detail_sppt .bold{font-weight:bold}
					</style>
					<div id='detail_sppt'>
						<table class='bold'>
							<tr>
								<td style='width:80%'>NOP : {$nop}</td>
								<td>Tahun Pajak : <!--{$row['SPPT_TAHUN_PAJAK']}-->
									<select name=\"preview_tahun\" id=\"preview_tahun\" style=\"width:100px\" onchange=\"javascript:changeYear(this,'{$nop}')\">
									</select>
								</td>
							</tr>
						</table><hr/>
						<table>
							<tr>
								<td style='width:150px'>Letak Objek Pajak</td>
								<td style='width:200px'>: {$row['OP_ALAMAT']}</td>
								<td style='width:100px'>Nama WP</td>
								<td>: {$row['WP_NAMA']}</td>
							</tr>
							<tr>
								<td>RT / RW</td>
								<td width='100'>: {$row['OP_RT']} / {$row['OP_RW']}</td>
								<td>Alamat WP</td>
								<td>: {$row['WP_ALAMAT']}</td>
							</tr>
							<tr>
								<td>Persil</td>
								<td colspan='3'>: </td>
							</tr>
						</table>
						<hr/>
						<table>
							<tr>
								<td></td>
								<td>Luas</td>
								<td>Kelas</td>
								<td>NJOP Per M2</td>
								<td>Total NJOP</td>
							</tr>
							<tr>
								<td>Bumi</td>
								<td class='border' align='right'>".number_format($row['OP_LUAS_BUMI'])."</td>
								<td class='border'>{$row['OP_KELAS_BUMI']}</td>
								<td class='border' align='right'>".number_format($row['OP_NJOP_BUMI'] / $row['OP_LUAS_BUMI'])."</td>
								<td class='border' align='right'>".number_format($total_njop_bumi = $row['OP_NJOP_BUMI'])."</td>
							</tr>
							<tr>
								<td>Bangunan</td>
								<td class='border' align='right'>".number_format($row['OP_LUAS_BANGUNAN'])."</td>
								<td class='border'>{$row['OP_KELAS_BANGUNAN']}</td>
								<td class='border' align='right'>".number_format($row['OP_NJOP_BANGUNAN']/$row['OP_LUAS_BANGUNAN'])."</td>
								<td class='border' align='right'>".number_format($total_njop_bangunan = $row['OP_NJOP_BANGUNAN'])."</td>
							</tr>
							<tr>
								<td>Bumi *</td>
								<td class='border' align='right'>".number_format($row['OP_LUAS_BUMI_BERSAMA'])."</td>
								<td class='border'>{$row['OP_KELAS_BUMI_BERSAMA']}</td>
								<td class='border' align='right'>".number_format($row['OP_NJOP_BUMI_BERSAMA']/$row['OP_LUAS_BUMI_BERSAMA'])."</td>
								<td class='border' align='right'>".number_format($total_njop_bumi_bersama = $row['OP_NJOP_BUMI_BERSAMA'])."</td>
							</tr>
							<tr>
								<td>Bangunan *</td>
								<td class='border' align='right'>".number_format($row['OP_LUAS_BANGUNAN_BERSAMA'])."</td>
								<td class='border'>{$row['OP_KELAS_BANGUNAN_BERSAMA']}</td>
								<td class='border' align='right'>".number_format($row['OP_NJOP_BANGUNAN_BERSAMA']/$row['OP_LUAS_BANGUNAN_BERSAMA'])."</td>
								<td class='border' align='right'>".number_format($total_njop_bangunan_bersama = $row['OP_NJOP_BANGUNAN_BERSAMA'])."</td>
							</tr>
						</table>
						<br/><hr/>
						<table>
							<tr><td>Jumlah NJOP Bumi</td><td align='right'>".number_format($total_njop_bumi)."</td></tr>
							<tr><td>Jumlah NJOP Bangunan</td><td align='right'>".number_format($total_njop_bangunan)."</td></tr>
							<tr><td>NJOP Sebagai Dasar Pengenaan PBB</td><td align='right'>".number_format($njop_pbb = $total_njop_bumi + $total_njop_bangunan)."</td></tr>
							<tr><td>BTKP / NJOPTKP</td><td align='right'>".number_format($row['OP_NJOPTKP'])."</td></tr>
							<tr><td>Nilai Jual Kena Pajak</td><td align='right'>".number_format($row['OP_NJKP'])."</td></tr>
							<tr><td>Pajak Bumi dan Bangunan Terhutang</td><td align='right'>".number_format($row['SPPT_PBB_HARUS_DIBAYAR'])."</td></tr>
							<tr><td>Faktor Pengurang</td><td align='right'>".number_format($row['SPPT_PBB_PENGURANGAN'])."</td></tr>
							<tr><td>Pajak Bumi dan Bangunan Yang Harus Dibayar</td><td align='right'>".number_format($harus_dibayar = $row['SPPT_PBB_HARUS_DIBAYAR']-$row['SPPT_PBB_PENGURANGAN'])."</td></tr>
							<tr><td>Denda Yang Telah Dibayar</td><td align='right'>0</td></tr>
							<tr><td>Pajak Bumi dan Bangunan Yang Telah Dibayar</td><td align='right'>0</td></tr>
							<tr><td>Selisih [Kurang Bayar]</td><td align='right'>".number_format($harus_dibayar)."</td></tr>
						</table>
						<hr/><br/>
						<table>
							<tr>
								<td style='width:290px'>Tanggal Jatuh Tempo / Tempat Pembayaran</td>
								<td class='border'>{$row['SPPT_TANGGAL_JATUH_TEMPO']} / {$tempatPembayaran}</td>
							</tr>
						</table><br/><hr/>
						<table>
							<tr>
								<td>Tanggal Terbit : {$row['SPPT_TANGGAL_TERBIT']}</td>
								<td>Tanggal Cetak : {$row['SPPT_TANGGAL_CETAK']}</td>
								<td>NIP Pencetak : </td>
							</tr>
						</table>
					</div>";
	}
        
	return $strHTML;
} 

$tTime = time();
$paymentDt;
$params = @isset($_REQUEST['req']) ? $_REQUEST['req'] : '';
$p = base64_decode($params);
$json = new Services_JSON();
$prm = $json->decode($p);
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$appConfig = $User->GetAppConfig($prm->appID);
$tempatPembayaran = $appConfig['TEMPAT_PEMBAYARAN'];
$tahunCetak = $prm->tahun;

$Setting = new SCANCentralSetting(DEBUG, LOG_FILENAME, $DBLink);

$arrValues = array();
if ($params) {
    doPrint($prm->NOP, $arrValues, $tahunCetak);                
}

echo $arrValues['HtmlValue'];

?>
