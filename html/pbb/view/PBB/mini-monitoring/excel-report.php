<?php
require_once('../../inc/payment/json.php');
require_once("../../inc/payment/db-payment.php");
require_once("../../inc/payment/inc-payment-db-c.php");
require_once("../../inc/payment/inc-payment-c.php");
require_once("../../inc/payment/inc-dms-c.php");
require_once("../../inc/central/user-central.php");
require_once("monitoring_lookup.php");

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

$dateTrs = @isset($_REQUEST['dateTrs']) ? $_REQUEST['dateTrs'] : null;
$modreq = @isset($_REQUEST['module']) ? $_REQUEST['module'] : null;
$area = @isset($_REQUEST['area']) ? $_REQUEST['area'] : $data->area_code;
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] :$data->bank_code;
if ($modreq!=null) require_once("../".$modreq."/inc-".$modreq."-config.php");

$json = new Services_JSON();
$tmpData = base64_decode($_REQUEST['dataXls']);
$data  =  $json->decode($tmpData);

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$areaDbLink = $User->GetDbConnectionFromArea($area);
$areaName = $User-> GetAreaName($area);

function getDataTransaction(&$rw,$detail=null) {
	global $data,$DBLink,$areaDbLink,$table_lookup,$query_Detail_Key,$query_detail,$bank,$colTitle_Detail;
	$mOK = false;
	if ($detail==null) {
		$arrCol = $data->colTitle_All;
		$sQ =  $data->query_all;
		$tbllookup = $data->table_lookup;
	} else {
		$json = new Services_JSON();
		$tmpData = $json->encode($colTitle_Detail);
		$arrCol =  $json->decode($tmpData);
		$tmp = explode("/",$detail);
		$detail = $tmp[2].$tmp[1].$tmp[0];
		$tbllookup = $table_lookup;
		$sQ =  str_replace('{BANK}',$bank,$query_detail)." AND ".$query_Detail_Key."='".$detail."'";	
	}
	$i=0; $dataRow = array(); $col =''; $where='';
	$f = count($arrCol);
	$tot = array();
	for ($x=0;$x<$f;$x++) {
		if ($arrCol[$x]->field <> '')	{
			switch ($arrCol[$x]->type) {
				case 'int':
					$tot[$arrCol[$x]->field] = 0;
					break;
				case 'currency';
					$tot[$arrCol[$x]->field] = 0;
					break;
			}
		}
	}

	$arrDBL = LOOKUP_ALL_MONITORING($tbllookup,$areaDbLink);
	foreach ($arrDBL as $res) {	
		SCANPayment_ConnectToDB($DBLink2, $DBConn2, $res["DB_HOST"], $res["DB_USER"], $res["DB_PWD"], $res["DB_NAME"]);
		$sQ1 = str_replace("{TABLE}",$res["DB_TABLE"],$sQ);
		if ($res = mysqli_query($DBLink2, $sQ1)) {
				while ($rws = mysqli_fetch_array($res)){
					for ($x=0;$x<$f;$x++) {
						if (@isset($arrCol[$x]['field']) != '')	{
							switch ($arrCol[$x]['type']) {
								case 'string':
									$dataRow[$i][$arrCol[$x]['field']] = $rws[$arrCol[$x]['field']];
									break;
								case 'int':
									$dataRow[$i][$arrCol[$x]['field']] = formatInt($rws[$arrCol[$x]['field']]);
									$tot[$arrCol[$x]['field']] += $rws[$arrCol[$x]['field']];
									break;
								case 'currency';
									$dataRow[$i][$arrCol[$x]['field']] = formatCurrency($rws[$arrCol[$x]['field']]);
									$tot[$arrCol[$x]['field']] += $rws[$arrCol[$x]['field']];
									break;
								case 'date':
									$dataRow[$i][$arrCol[$x]['field']] = formatDate($rws[$arrCol[$x]['field']]);
									break;
								case 'linkUrl':
									$dataRow[$i][$arrCol[$x]['field']] = linkUrlString($rws[$arrCol[$x]['field']]);
									break;
							}
						} else if (@isset($arrCol[$x]["subTitle"]))	{
							$arrSb = $arrCol[$x]["subTitle"];
							for ($z=0;$z<count($arrSb);$z++) {
								switch ($arrSb[$z]['type']) {
									case 'string':
										$dataRow[$i][$arrSb[$z]['field']] = $rws[$arrSb[$z]['field']];
										break;
									case 'int':
										$dataRow[$i][$arrSb[$z]['field']] = formatInt($rws[$arrSb[$z]['field']]);
										$tot[$arrSb[$z]['field']] += $rws[$arrSb[$z]['field']];
										break;
									case 'currency';
										$dataRow[$i][$arrSb[$z]['field']] = formatCurrency($rws[$arrSb[$z]['field']]);
										$tot[$arrSb[$z]['field']] += $rws[$arrSb[$z]['field']];
										break;
									case 'date':
										$dataRow[$i][$arrSb[$z]['field']] = formatDate($rws[$arrSb[$z]['field']]);
										break;
									case 'linkUrl':
										$dataRow[$i][$arrSb[$z]['field']] = linkUrlString($rws[$arrSb[$z]['field']]);
										break;
								}
							}
						}
					}
					$i++;
					$mOK = true;
				}
			} else {
				echo "Error : ".mysqli_error($DBLink);
				SCANPayment_CloseDB($DBLink2);
			}
			SCANPayment_CloseDB($DBLink2);
		}
		if ($mOK) {
			for ($x=0;$x<$f;$x++) {
				if (@isset($arrCol[$x]['field']) != ''){
					switch ($arrCol[$x]['type']) {
						case 'string':
							$dataRow[$i][$arrCol[$x]['field']] = $rws[$arrCol[$x]['field']];
							break;
						case 'int':
							$dataRow[$i][$arrCol[$x]['field']] = formatInt($tot[$arrCol[$x]['field']]);
							break;
						case 'currency';
							$dataRow[$i][$arrCol[$x]['field']] = formatCurrency($tot[$arrCol[$x]['field']]);
							break;
						case 'date':
							$dataRow[$i][$arrCol[$x]['field']] = formatDate($rws[$arrCol[$x]['field']]);
							break;
						case 'linkUrl':
							$dataRow[$i][$arrCol[$x]['field']] = linkUrlString($rws[$arrCol[$x]['field']]);
							break;
					}
				} else if (@isset($arrCol[$x]["subTitle"]))	{
					$arrSb = $arrCol[$x]["subTitle"];
					for ($z=0;$z<count($arrSb);$z++) {
						switch ($arrSb[$z]['type']) {
							case 'string':
								$dataRow[$i][$arrSb[$z]['field']] = $tot[$arrSb[$z]['field']];
								break;
							case 'int':
								$dataRow[$i][$arrSb[$z]['field']] = formatInt($tot[$arrSb[$z]['field']]);
								$tot[$arrSb[$z]['field']] += $rws[$arrSb[$z]['field']];
								break;
							case 'currency';
								$dataRow[$i][$arrSb[$z]['field']] = formatCurrency($tot[$arrSb[$z]['field']]);
								$tot[$arrSb[$z]['field']] += $rws[$arrSb[$z]['field']];
								break;
							case 'date':
								$dataRow[$i][$arrSb[$z]['field']] = formatDate($tot[$arrSb[$z]['field']]);
								break;
							case 'linkUrl':
								$dataRow[$i][$arrSb[$z]['field']] = linkUrlString($tot[$arrSb[$z]['field']]);
								break;
						}
					}
				}
			}
			$rw = $dataRow;
		}
	return $mOK;
}
$dt2 = date("YmdHis");

$nama_file = $areaName."-".$dt2;

//HeaderingExcel($nama_file.'.xls');

//membuat area kerja
$workbook=new Workbook("-");
//class untuk mencetak tulisan besar dan tebal
$fBesar=& $workbook->add_format();
$fBesar->set_size(14);
$fBesar->set_align("merge");
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

$fDtlLeft=& $workbook->add_format();
$fDtlLeft->set_border(1);
$fDtlLeft->set_align("center");
$fDtlLeft->set_align("vcentre");
$fDtlLeft->set_text_wrap(1);

//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai string)
$fDtl=& $workbook->add_format();
$fDtl->set_border(1);
//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai numerik)
$fDtlNumber=& $workbook->add_format();
$fDtlNumber->set_border(1);
$fDtlNumber->set_align("right");
$fDtlNumber->set_num_format(0);

//my $fDtlCurrency = $workbook->add_format(num_format => '#,##0.00');

$fDtlCurrency=& $workbook->add_format();
$fDtlCurrency->set_border(1);
$fDtlCurrency->set_align("right");
$fDtlCurrency->set_num_format(3);
//class untuk men-zoom laporan 75%
$worksheet1= & $workbook->add_worksheet("Halaman 1");
$worksheet1->set_zoom(100);

$header = $p->header;
$worksheet1->set_row(3,30); 
//$worksheet1->set_column(0,0,10);
//sesuaikan dengan judul kolom pada table anda
$colt = 1;


if ($dateTrs!=null) {
	$json = new Services_JSON();
	$tmpData = $json->encode($colTitle_Detail);
	$arrCol =  $json->decode($tmpData);
	$f = count($arrCol);
}else {
	$arrCol = $data->colTitle_All;
	$f = count($arrCol);
}



$sQ = "SELECT * FROM CDCCORE_BANK";
$opt='';
$bn='';
if ($resx = mysqli_query($areaDbLink, $sQ)) {
	while($rowx = mysqli_fetch_array($resx, MYSQL_ASSOC))
	{	
		if ($bank == $rowx['CDC_B_ID']) {
			$bn = $rowx['CDC_B_NAME'];
		}
	}
} 

$worksheet1->write_string(0,0,"DAFTAR PENERIMAAN ",$fBesar);
$worksheet1->write_string(1,0,"Pada ",$fBiasa);
$worksheet1->merge_cells(0,0, 0, 5);
$worksheet1->merge_cells(1,0, 1, 5);
$worksheet1->merge_cells(2,0, 2, 5);

$arrCol = $arrData;
$f = count($arrCol);
$col = '';
$col2= '';
$awal = 2;
$baris = $awal;

for ($x=1;$x<$f;$x++) {
	if (@isset($arrCol[$x]['span'])) {
		if ($arrCol[$x]['span'][0] == "row") {
			$titRow	= $arrCol[$x]['span'][1];
			$col .= "x";
			$worksheet1->merge_cells($baris,$x, $baris+$titRow-1, $x);
			$worksheet1->write_string($baris,$x,$arrCol[$x]['title'],$fBiasa);
		} else if ($arrCol[$x]['span'][0] == "col") {
			$titCol	= $arrCol[$x]['span'][1];
			//$col .= "<td align='center' width='".$arrCol[$x]['width']."' colspan='".$titCol."'>".$arrCol[$x]['title']."</td>";
			$worksheet1->merge_cells($baris,$x, $baris, $x+$titCol-1);
			$worksheet1->write_string($baris,$x,$arrCol[$x]['title'],$fBiasa);
		}
	} else {
		    $col .= "x";
			$worksheet1->merge_cells($baris,$x, $baris, $x);
			$worksheet1->write_string($baris,$x,$arrCol[$x]['title'],$fBiasa);
	}
	if (@isset($arrCol[$x]['subTitle'])){
		$fl = count ($arrCol[$x]['subTitle']);
		//var_dump($arrCol[$x]['subTitle']);
		$baris++;
		for ($y=0;$y<$fl;$y++) {
			$worksheet1->merge_cells($baris,$x, $baris, $x);
			$worksheet1->write_string($baris,$x,$arrCol[$x]['title'],$fBiasa);
		}			
	}
}
if ($col!='') {
	$this->header_all .= "<tr class='tableSubTitle' >".$col2."</tr>";
	$worksheet1->merge_cells($awal,0, $baris, 0);
}
/*create header table*/


//$worksheet1->write_string(0,0,"DAFTAR PENERIMAAN ".strtoupper($areaName),$fBesar);
//$worksheet1->write_string(1,0,"Pada ".strtoupper($bn),$fBiasa);
/*if ($dateTrs==null) { $worksheet1->write_string(2,0,"Periode : ".$data->start_date." s/d ".$data->end_date,$fBiasa); }
else { $worksheet1->write_string(2,0,"Tanggal : ".$dateTrs,$fBiasa);}

$arrCol = $arrData;
		$f = count($arrCol);
		$col = '';
		$col2= '';
		for ($x=0;$x<$f;$x++) {
			if (@isset($arrCol[$x]['span'])) {
				if ($arrCol[$x]['span'][0] == "row") {
					$titRow	= $arrCol[$x]['span'][1];
					$col .= "<td align='center' width='".$arrCol[$x]['width']."' rowspan='".$titRow."'>".$arrCol[$x]['title']."</td>";
				} else if ($arrCol[$x]['span'][0] == "col") {
					$titCol	= $arrCol[$x]['span'][1];
					$col .= "<td align='center' width='".$arrCol[$x]['width']."' colspan='".$titCol."'>".$arrCol[$x]['title']."</td>";
				}
			} else {
				$col .= "<td align='center' width='".$arrCol[$x]['width']."'>".$arrCol[$x]['title']."</td>";
			}
			if (@isset($arrCol[$x]['subTitle'])){
				$fl = count ($arrCol[$x]['subTitle']);
				//var_dump($arrCol[$x]['subTitle']);
				for ($y=0;$y<$fl;$y++) {
					$col2 .= "<td align='center' width='".$arrCol[$x]['subTitle'][$y]['width']."'>".$arrCol[$x]['subTitle'][$y]['title']."</td>";
				}			
			}
		}
		$this->header_all = "<tr class='tableTitle' ><td align='center' width='5%' rowspan='".$titRow."'>No.</td>".$col."</tr>";
		if ($col!='') {
			$this->header_all .= "<tr class='tableSubTitle' >".$col2."</tr>";
		}
		
for ($x=0;$x<$f;$x++) {
	if ($arrCol[$x]->field <> '')	{
		$width = ($arrCol[$x]->width*1);
		$worksheet1->set_column(0,$x,$width);
		$worksheet1->write_string(3,$x,$arrCol[$x]->title,$fDtlHead);	
	}
}

$baris = 4;
if (getDataTransaction($rw,$dateTrs)) {
	foreach ($rw as $row) {
		$tot = array();
		for ($x=0;$x<$f;$x++) {
			if ($arrCol[$x]->field <> '')	{
				switch ($arrCol[$x]->type) {
					case 'string':
						$worksheet1->write_string($baris,$x,formatString($row[$arrCol[$x]->field]),$fDtlLeft);
						break;
					case 'int':
						$worksheet1->write_number($baris,$x,formatInt($row[$arrCol[$x]->field]),$fDtlNumber);
						break;
					case 'currency';
						$worksheet1->write($baris,$x,formatInt($row[$arrCol[$x]->field]),$fDtlCurrency);
						break;
					case 'date':
						$worksheet1->write_string($baris,$x,formatDate($row[$arrCol[$x]->field]),$fDtlCenter);
						break;
				}
			}
		}
		$baris++;
	}
}
*/
$workbook->close();

?>
