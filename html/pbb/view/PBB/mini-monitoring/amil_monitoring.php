<?php
/* 
	class monitoring amil zakat infak sadaqah
	
*/
require_once('inc/payment/json.php');

class amil_monitoring {
	public $AREA;
	public $BILLER;
	public $TBL_PRODUCT;
	public $TBL_TRANSACTION;
	public $DBLINK;
	public $DEF_START_DATE;
	public $DEF_END_DATE;
	
	public function getProductBiller(&$rw) {
		global $jcol;
		$mOK = false;
		$i=0;
		$sQ = "SELECT * FROM ".$this->TBL_PRODUCT;
		
		if ($res = mysqli_query($this->DBLINK, $sQ)) {
			while($row = mysqli_fetch_array($res))
			{
				$rw[$i]['CDM_PR_CODE'] = $row['CDM_PR_CODE'];
				$rw[$i]['CDM_PR_NAME']= $row['CDM_PR_NAME'];
				$i++;
			} 
			$jcol = $i;
			$mOK = true;
		} else {
			echo mysqli_error($DBLink); 
		}
		
		return $mOK;
	}
	
	public function exportToXls($filename,$start,$end) {
		echo "<h1>test</h1>";
		$this->HeaderingExcel($filename);
		
	}
	
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
	public function getDataTransaction(&$rw,$start,$end,$bank) {
		/*
		select CDM_TM_SETTLE_DATE, COUNT(CASE WHEN CDM_TM_PRODUCT_CODE='ZA00001' THEN 1 ELSE 0 END) AS "COL1",SUM(CASE  WHEN CDM_TM_PRODUCT_CODE='ZA00001' THEN CDM_TM_TRANSACT_AMOUNT ELSE 0 END),
COUNT(CASE  WHEN CDM_TM_PRODUCT_CODE='BM00001' THEN 1 ELSE 0 END),SUM(CASE  WHEN CDM_TM_PRODUCT_CODE='BM00001' THEN CDM_TM_TRANSACT_AMOUNT ELSE 0 END)
FROM CDCMOD_CHARITY_DPUDT_TRAN_MAIN WHERE CDM_TM_FLAG=1 GROUP BY CDM_TM_SETTLE_DATE WITH ROLLUP
		*/
		$mOK = false;
		$i=0;
		$col ='';
		if ($this->getProductBiller($prows)) { 
			foreach ($prows as $prow) {
				$col .= "SUM(CASE WHEN CDM_TM_PRODUCT_CODE='".$prow['CDM_PR_CODE']."' THEN 1 ELSE 0 END) AS QTY".$prow['CDM_PR_CODE']." ,";
				$col .= "SUM(CASE  WHEN CDM_TM_PRODUCT_CODE='".$prow['CDM_PR_CODE']."' THEN CDM_TM_TRANSACT_AMOUNT ELSE 0 END) AS RUPIAH";
				$col .= $prow['CDM_PR_CODE'].", ";
			}
		}
		
		$col = substr($col,0,-2);
		$sQ = "SELECT CDM_TM_SETTLE_DATE, ".$col." FROM ";
		
		$groupby = " GROUP BY CDM_TM_SETTLE_DATE WITH ROLLUP";
					
		$where = " WHERE CDM_TM_SETTLE_DATE IS NOT NULL AND CDM_TM_FLAG=1 AND CDM_TM_CA = '".$bank."'"; 
		if ($start !='') {
			$where .= " AND CDM_TM_SAVED >= '". $this->createStrDate($start)."' ";
		}
		if ($end !='') {
			$where .= " AND CDM_TM_SAVED <= '". $this->createStrDate($end)."' ";
		}
		$sQ .= $this->TBL_TRANSACTION.$where.$groupby;
		$x=0;
		$tmpdate='';
		$pos = 0;
		if ($this->getProductBiller($prows)) {
			if ($res = mysqli_query($this->DBLINK, $sQ)) {
				$dataRow = array();
				while($row = mysqli_fetch_array($res))
				{
					$dataRow[$x]['DATE_TRANS'] = $row['CDM_TM_SETTLE_DATE'];
					$tmpdate = $row['CDM_TM_SETTLE_DATE'];
					foreach ($prows as $prow) {
							$dataRow[$x][$prow['CDM_PR_NAME']] ['QTY'] = $row['QTY'.$prow['CDM_PR_CODE']];
							$dataRow[$x][$prow['CDM_PR_NAME']] ['RUPIAH'] = $row['RUPIAH'.$prow['CDM_PR_CODE']];
					}
					$x++;
				} 
				$mOK = true;
				$rw = $dataRow;
			}
			
		}
		return $mOK;
	}
	
	public function printHeader(&$hd,$body) {
		$mOK = false;
		$header = '<style type="text/css">
					<!--
					#transID {
						background-color: #aaa;
						border-style:solid;
						border-color:#000;
						border-width:0px;
					}
					#trans-mon{
						padding-top : 8px;
						padding-bottom : 8px;
						padding-right : 8px;
					}
					#frm-date{
						padding-top : 8px;
						padding-bottom : 8px;
					}
					#xls-file{
						position:absolute;
						padding-top : 8px;
						padding-bottom : 8px;
						left: 840px;
						top: 182px;
					}
					.grayArea {
						background-color: #cfcfcf;
					}
					-->
					</style>
					<script language="JavaScript">
						function funcNavigate(address) {
							window.location.href=address;
						}
					</script>
					';
		
		if ($this->getProductBiller($rows)) {
			$jProd = count($rows);
			$width = 220;
			$p['header']= $rows;
			$p['area'] = $this->AREA;
			$p['tbl_transaction'] = $this->TBL_TRANSACTION;
			$p['start_date'] = $this->DEF_START_DATE;
			$p['end_date'] = $this->DEF_END_DATE;
			$p['bank'] = $this->BANK;
			$json = new Services_JSON();
			
			
			
			$sQ = "SELECT * FROM ".$this->TBL_BANK;
			$opt='';
			$bn='';
			if ($resx = mysqli_query($this->DBLINK, $sQ)) {
				
				while($rowx = mysqli_fetch_array($resx))
				{	
					$sel = $this->BANK == $rowx['CDC_B_ID'] ? "selected='selected'":"";
					if ($this->BANK == $rowx['CDC_B_ID']) {
						$bn = $rowx['CDC_B_NAME'];
					}
					$opt .= "<option value ='".$rowx['CDC_B_ID']."' $sel>".$rowx['CDC_B_NAME']."</option>";		
				}
			
			} else {
				echo "error -> $this->TBL_BANK ".mysqli_error($DBLink);
			}
			$p['bank_name'] = $bn;
			//echo "bank->".$p['bank_name'];
			$params = $json->encode($p);
			$params = base64_encode($params);
			$header .= '<div id="trans-mon"><div id="frm-date">
			<form id="form1" name="form1" method="post" action="main.php?param='.$_REQUEST['param'].'">
						  <label>Bank : 
							<select name="bank" id="bank">'.$opt.'
							</select>
						  </label>
						  <label>Tanggal Awal : 
							<input type="text" name="start_date" id="start_date" value="'.$this->DEF_START_DATE.'"/>
						  </label><label>Tanggal Akhir : <input type="text" name="end_date" id="end_date" value="'.$this->DEF_END_DATE.'"/>
						  </label><input type="submit" name="button2" id="button2" value="Submit" />
						   <input type="button" name="button" id="button" value="Export to xls" onclick="funcNavigate(\'view/amil/excel.php?q='.$params.'\')"/>
					</form></div>';
			$header .= "<div id='transID'><table cellpadding = '2' cellspacing='1' border='0'><tr align='center' class='tableTitle'><td rowspan='2' width= $width>Tanggal Transaksi</td>";
			$sheader = "<tr align='center' class='tableSubTitle'>";
			foreach ($rows as $row) {
				$header .= "<td width= $width colspan=2  class='tableSubTitle'>".$row['CDM_PR_NAME']."</td>";
				$sheader .= "<td width='10' >QTY</td><td width='40' >Rupiah</td>";
			}
			$sheader .= "</tr>";
			$header .= "<td rowspan='2' width= $width>Jumlah Rupiah Perhari</td></tr> $sheader $body </table></div></div>";
			$header .= "<div id='footer-table'><b>PERHATIAN</b> :<br>
							<ul><li>Informasi yang tercantum hanya bersifat sementara. Informasi final adalah hasil rekonsiliasi yang dilaporkan oleh tim rekonsiliasi VSI kepada pihak BANK dan ".$this->BILLER."</li><li>Tekan tombol SUBMIT untuk menampilkan ulang (update) data !.</li><ul>
						</div>";
			$hd = $header;
			$mOK = true;
		} else {
			$header = "Products biller is empty !";
			$mOK = true;
		}
		return $mOK;
		//echo $header;
	}
	
	public function printData() {
		global $jcol;
		if ($this->getDataTransaction($rows,$this->DEF_START_DATE,$this->DEF_END_DATE,$this->BANK)) {
				$printData='';
				$totAll=count($rows)-1;
				$i=0;
				foreach ($rows as $row ) {
					$col = $row['DATE_TRANS']?$this->formatDate($row['DATE_TRANS']):'TOTAL';
					$style = ($i%2?'class="grayArea"':'');
					if ($i == $totAll) {
						$printData .= "<tr class='tableSubTitle'><td align='center'>".$col ."</td>";
					} else {
						$printData .= "<tr><td align='center'  $style >".$this->formatDate($row['DATE_TRANS'])."</td>";
					}
					if ($this->getProductBiller($prows)) {
						$tot = 0;
						foreach ($prows as $prow) {
							$printData .= "<td align='right'  $style >".number_format($row[$prow['CDM_PR_NAME']]['QTY'], 0, ',', '.')."</td><td align='right'  $style >".number_format($row[$prow['CDM_PR_NAME']]['RUPIAH'], 0, ',', '.')."</td>";
							$tot = $tot + $row[$prow['CDM_PR_NAME']]['RUPIAH'];
						}
					}
					
					$printData .= "<td align='right'  $style >".number_format($tot, 0, ',', '.')."</td></tr>";
					
					$i++;
					//$totAll += $tot; 
				}
				
				//$printData .= "<tr class='tableSubTitle'><td align='right' colspan='".(($jcol*2)+1)."'></td><td align='right'><b>".number_format($totAll, 0, ',', '.')."</b></td></tr>";
		} 
		if ($this->printHeader($header,$printData)) {
			//$printData = $header;
			
			echo $header;
		}
		
	}

}
?>