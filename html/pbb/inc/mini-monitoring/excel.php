<?php
require_once('../../inc/payment/json.php');
require_once("../../inc/payment/db-payment.php");
require_once("../../inc/payment/inc-payment-db-c.php");
require_once("../../inc/central/user-central.php");


require_once("OLEwriter.php");
require_once("BIFFwriter.php");
require_once("Worksheet.php");
require_once("Workbook.php");

function HeaderingExcel($filename){
	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename=$filename");
	header("Expires:0");
	header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
	header("Pragma: public");
}

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$params = $_REQUEST['q'] ? $_REQUEST['q'] : '';

//Tempatkan query anda disini (harus "SELECT")
/*$sql="SELECT * FROM xxx";
$link=open_connect();
$qry=mysql_query($sql,$link) or die ("Invalid Query");*/
//nama file yang dihasilkan adalah ‘xxx.xls’


$json = new Services_JSON();
$params = base64_decode($params);
$p = $json->decode($params);

$areaDbLink = $User->GetDbConnectionFromArea($p->area);
$areaName = $User-> GetAreaName($p->area);

$start = $p->start_date;
$end = $p->end_date;
$bank = $p->bank;
$bank_name = $p->bank_name;

$date = explode("/",$start);
$dt = $date[2].$date[1].$date[0];

$date2 = explode("/",$end);
$dt2 = $date2[2].$date2[1].$date2[0];

$nama_file = str_replace(" ","-",$bank_name)."-".$dt.$dt2;

HeaderingExcel($nama_file.'.xls');
function createStrDate($sd) {
	if ($sd != '') {
		$date = explode("/",$sd);
		$dt = $date[2]."/".$date[1]."/".$date[0];
		return $dt;
	} else {
		return $sd;
	}
}
function formatDate($sd) {
	if ($sd != '') {
		$yr = substr($sd, 0, 4);  // returns "cde"
		$mt = substr($sd, 4, 2);  // returns "cde"
		$dy = substr($sd, 6, 2);  // returns "cde"
		$dt = $dy."/".$mt."/".$yr;
		return $dt;
	} else {
		return $sd;
	}
}
function getDataTransaction(&$rw,$start,$end,$areaDbLink,$bank ) {
		global $p;
		$mOK = false;
		$i=0;
		$col ='';
		$header = $p->header;
		foreach ($header as $prow) {
			$col .= "SUM(CASE WHEN CDM_TM_PRODUCT_CODE='".$prow->CDM_PR_CODE."' THEN 1 ELSE 0 END) AS QTY".$prow->CDM_PR_CODE." ,";
			$col .= "SUM(CASE  WHEN CDM_TM_PRODUCT_CODE='".$prow->CDM_PR_CODE."' THEN CDM_TM_TRANSACT_AMOUNT ELSE 0 END) AS RUPIAH";
			$col .= $prow->CDM_PR_CODE.", ";
		}
	
		$col = substr($col,0,-2);
		$sQ = "SELECT CDM_TM_SETTLE_DATE, ".$col." FROM ";
		
		$groupby = " GROUP BY CDM_TM_SETTLE_DATE WITH ROLLUP";
					
		$where = " WHERE CDM_TM_SETTLE_DATE IS NOT NULL AND CDM_TM_FLAG=1 AND CDM_TM_CA = '".$bank."'"; 
		if ($start !='') {
			$where .= " AND CDM_TM_SAVED >= '". createStrDate($start)."' ";
		}
		if ($end !='') {
			$where .= " AND CDM_TM_SAVED <= '". createStrDate($end)."' ";
		}
		$sQ .= $p->tbl_transaction.$where.$groupby;
				
		$x=0;
		$tmpdate='';
		$pos = 0;
		
		if ($res = mysqli_query($areaDbLink, $sQ)) {
			$dataRow = array();
			while($row = mysqli_fetch_array($res))
			{
				$dataRow[$x]['DATE_TRANS'] = $row['CDM_TM_SETTLE_DATE'];
				$tmpdate = $row['CDM_TM_SETTLE_DATE'];
				foreach ($header as $prow) {
						$dataRow[$x][$prow->CDM_PR_NAME] ['QTY'] = $row['QTY'.$prow->CDM_PR_CODE];
						$dataRow[$x][$prow->CDM_PR_NAME] ['RUPIAH'] = $row['RUPIAH'.$prow->CDM_PR_CODE];
				}
				$x++;
			} 
			$mOK = true;
			$rw = $dataRow;
		} else {
			echo "error : " .mysqli_error($DBLink);
		}
			
		return $mOK;
}
//membuat area kerja
$workbook=new Workbook("-");
//class untuk mencetak tulisan besar dan tebal
$fBesar=& $workbook->add_format();
$fBesar->set_size(14);
$fBesar->set_align("center");
$fBesar->set_bold();

$fBiasa=& $workbook->add_format();
$fBiasa->set_align("center");
//class untuk mencetak tulisan tanpa border (untuk judul laporan)
$fList=& $workbook->add_format();
$fList->set_border(0);
//class untuk mencetak tulisan dengan border dan ditengah kolom (untuk judul kolom)
$fDtlHead=& $workbook->add_format();
$fDtlHead->set_border(1);
$fDtlHead->set_align("center");
$fDtlHead->set_align("vcentre");
$fDtlHead->set_text_wrap(1);

$fDtlCenter=& $workbook->add_format();
$fDtlCenter->set_border(1);
$fDtlCenter->set_align("center");
$fDtlCenter->set_align("vcentre");
$fDtlCenter->set_text_wrap(1);

//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai string)
$fDtl=& $workbook->add_format();
$fDtl->set_border(1);
//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai numerik)
$fDtlNumber=& $workbook->add_format();
$fDtlNumber->set_border(1);
$fDtlNumber->set_align("right");
$fDtlNumber->set_num_format(3);
//class untuk men-zoom laporan 75%
$worksheet1= & $workbook->add_worksheet("Halaman 1");
$worksheet1->set_zoom(75);
$header = $p->header;
$worksheet1->set_row(3,30); 
$worksheet1->set_column(0,0,10);
//sesuaikan dengan judul kolom pada table anda

$worksheet1->merge_cells(3,0, 4, 0);
$worksheet1->write_string(3,0,"Tanggal Transaksi.",$fDtlHead);
$worksheet1->write_string(4,0,'',$fDtlHead);
$i=1;
foreach ($header as $row) {
		$worksheet1->merge_cells(3,$i, 3, $i+1);
		$worksheet1->write_string(3,$i,$row->CDM_PR_NAME,$fDtlHead);
		$worksheet1->write_string(3,$i+1,'',$fDtlHead);
		$worksheet1->set_column(0,$i,8);
		$worksheet1->write_string(4,$i,'QTY',$fDtlHead);
		$worksheet1->set_column(0,$i+1,15);
		$worksheet1->write_string(4,$i+1,'Rupiah',$fDtlHead);
		$i=$i+2;
}
$worksheet1->set_column(0,$i,15);
$worksheet1->merge_cells(3,$i, 3, $i);
$worksheet1->write_string(3,$i,"Jumlah",$fDtlHead);
$worksheet1->write_string(4,$i,'',$fDtlHead);
$worksheet1->merge_cells(0,0, 0, $i);
$worksheet1->merge_cells(1,0, 1, $i);
$worksheet1->merge_cells(2,0, 2, $i);
$worksheet1->write_string(0,0,"DAFTAR PENERIMAAN ".strtoupper($areaName),$fBesar);
$worksheet1->write_string(1,0,"Pada ".strtoupper($p->bank_name),$fBiasa);
$worksheet1->write_string(2,0,"Periode : ".$p->start_date." s/d ".$p->end_date,$fBiasa);

$baris = 5;
if (getDataTransaction($rw,$start,$end,$areaDbLink,$bank)) {
	foreach ($rw as $row) {
		$tot = 0;
		$kolom = 1;	
		$worksheet1->write_string($baris,0,formatDate($row['DATE_TRANS']),$fDtlCenter);
		foreach ($header as $hrow) {
			$worksheet1->write_number($baris,$kolom,$row[$hrow->CDM_PR_NAME]['QTY'],$fDtlNumber);
			$worksheet1->write_number($baris,$kolom+1,$row[$hrow->CDM_PR_NAME]['RUPIAH'],$fDtlNumber);
			$tot = $tot + $row[$hrow->CDM_PR_NAME]['RUPIAH'];
			$kolom = $kolom +2;
		}
		$worksheet1->write_number($baris,$kolom,$tot,$fDtlNumber);
	  $baris++;
	}
}

$workbook->close();
?>
