<?php
/* 
	class monitoring palyja
	
*/
require_once('inc/payment/json.php');
require_once("inc/payment/ctools.php");
require_once("inc/mini-monitoring/monitoring_lookup.php");
require_once("inc/payment/db-payment.php");

class mini_monitoring {
	public $AREA;
	public $MODULE;
	public $MODULE_NAME;
	public $DATA;
	public $BILLER;
	public $TBL_TRANSACTION;
	public $TBL_BANK;
	public $DBLINK;
	public $DEF_START_DATE;
	public $DEF_END_DATE;
	public $LOOKUP_TABLE;
	public $HEADER_TYPE=null;
	public $OPTION_TYPE=null;
	public $TABLE_WIDTH=800;
	public $EXCELFILE;
	private $header_all;
	private $body;
	private $table;
	
	function createStrDate($sd) {
		if ($sd != '') {
			$date = explode("/",$sd);
			$dt = $date[2].$date[1].$date[0];
			//$dt = str_replace("/","",$sd);
			return $dt;
		} else {
			return $sd;
		}
	}
	
	public function getDataTransaction(&$rw,$detail=null) {
		$mOK = false;
		$arrData = $this->DATA;
		if ($detail==null) {
			$arrCol = $arrData['colTitle_All'];
			$sQ =  $arrData['query_all'];	
		} else {
			$arrCol = $arrData['colTitle_Detail'];
			$sQ =  $arrData['query_detail']." AND ".$arrData['query_Detail_Key']."='".$detail."'";	
		}
		$i=0; $dataRow = array(); $col =''; $where='';
		$f = count($arrCol);
		$tot = array();
		for ($x=0;$x<$f;$x++) {
			if ((@isset($arrCol[$x]['field']) != '') && (@isset($arrCol[$x]['field'])))	{
				switch ($arrCol[$x]['type']) {
					case 'int':
						$tot[$arrCol[$x]['field']] = 0;
						break;
					case 'currency';
						$tot[$arrCol[$x]['field']] = 0;
						break;
				}
			} else if (@isset($arrCol[$x]["subTitle"]))	{
				$arrSb = $arrCol[$x]["subTitle"];
				for ($z=0;$z<count($arrSb);$z++) {
					$tot[$arrSb[$z]['field']] = 0;
				}
			}
		}
		
		$arrDBL = @LOOKUP_ALL_MONITORING($this->LOOKUP_TABLE,$this->DBLINK);
		if ($arrDBL){
			foreach ($arrDBL as $res2) {	
				SCANPayment_ConnectToDB($DBLink2, $DBConn2, trim($res2["DB_HOST"]), trim($res2["DB_USER"]), trim($res2["DB_PWD"]), trim($res2["DB_NAME"]));
				$sQ1 = str_replace("{TABLE}",trim($res2["DB_TABLE"]),$sQ);
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
					echo "Error : ".mysqli_error($DBLink)."<br>".$sQ1;
					SCANPayment_CloseDB($DBLink2);
				}
				SCANPayment_CloseDB($DBLink2);
			}
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
	
	private function createHeader($arrData) {
		$arrCol = $arrData;
		$f = count($arrCol);
		$col = '';
		$col2= '';
		$titRow=0;
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
	}
	private function createBodyDetail() {
		$prm = base64_decode($_REQUEST['param']);
		$dateTrs = @isset($_REQUEST['dateTrs'])?$_REQUEST['dateTrs']:'';
		$tmp = explode("/",$dateTrs);
		$dateTrs = $tmp[2].$tmp[1].$tmp[0];
		if ($this->getDataTransaction($rows,$dateTrs)) {
			$i=0;
			$arrData = $this->DATA;
			$arrCol = $arrData['colTitle_Detail'];
			$f = count($arrCol);
			$col = '';
			$pos = strrpos($prm, "start_date");
			$nrows = count($rows);
			
			foreach ($rows as $row) {
				$no = $i < ($nrows-1) ? ($i+1) : ''; 
				$style = ($i%2?'class="grayArea"':'');
				$style = $i < ($nrows-1) ? $style :'class = "totalArea"'; 
				$this->body .= "<tr><td  align='right' $style >".$no."</td>";
				for ($x=0;$x<$f;$x++) {
					$this->body .= "<td align='".$arrCol[$x]['align']."' width='".$arrCol[$x]['width']."' $style>".$row[$arrCol[$x]['field']]."</td>";
				}
				$this->body .= "</tr>";
				$i++;
			}
		}
	}
	private function createBodyAll() {
		$prm = base64_decode($_REQUEST['param']);
		if ($this->getDataTransaction($rows)) {
			$i=0;
			$arrData = $this->DATA;
			$arrCol = $arrData['colTitle_All'];
			$f = count($arrCol);
			$col = '';
			$pos = strrpos($prm, "start_date");
			$nrows = count($rows);
			$json = new Services_JSON();
			foreach ($rows as $row) {
				if ($pos === false) { // note: three equal signs
					$tprm = $prm."&start_date=".$this->DEF_START_DATE."&end_date=".$this->DEF_END_DATE."&bank=".$this->BANK."&dateTrs=".$row[$arrData['query_All_Key']];
				} else {
					$pos = strrpos($prm, "dateTrs");
					if ($pos === false) { 
						$tprm = $prm."&dateTrs=".$row[$arrData['query_All_Key']];
					}else {
						$tprm = $prm;
					}
				}
				$dtrs = $row[$arrData['query_All_Key']];
				$param_view = $tprm.'&exe=view';
				$param_view = base64_encode($param_view);
								
				$paramsxls = $json->encode($this->DATA);
				$paramsxls = base64_encode($paramsxls);
				
				$opt = "<div id='divOpt' class='aview'><a href='main.php?param=".$param_view."'>
				         <img border='0' src='image/icon/cancelpayment.gif' alt='View' title='View'></img></a>
						<a href=''.$this->EXCELFILE.'?dateTrs=".$row[$arrData['query_All_Key']].
						"&module=".strtolower($this->MODULE_NAME)."&area=".$this->AREA."&bank=".$this->BANK."'>
						<img border='0' src='image/icon/table.png' 
						alt='Export to xls' title='Export to xls'></img></a>
					    </form></div>";
				$style = ($i%2?'class="grayArea"':'');
				$opt = $row[$arrData['query_All_Key']]<>''? $opt:'';
				$no = $i < ($nrows-1) ? ($i+1) : ''; 
				$style = $i < ($nrows-1) ? $style :'class = "totalArea"'; 
				$this->body .= "<tr><td  align='right' $style >".$no."</td>";
				for ($x=0;$x<$f;$x++) {
					$cl =@isset($arrCol[$x]['field']);
					if (@isset($arrCol[$x]['field'])) {
						if ($cl == $arrData['query_All_Key']) {
							$row[$arrCol[$x]['field']] = $row[$arrData['query_All_Key']]<>''?$row[$arrCol[$x]['field']]:'TOTAL';
						}				
						
						$this->body .= "<td align='".$arrCol[$x]['align']."' width='".$arrCol[$x]['width']."' $style>".$row[$arrCol[$x]['field']]."</td>";
					} else if (@isset($arrCol[$x]["subTitle"]))	{
						$arrSb = $arrCol[$x]["subTitle"];
						for ($z=0;$z<count($arrSb);$z++) {
							$this->body .= "<td align='".$arrSb[$z]['align']."' width='".$arrSb[$z]['width']."' $style>".$row[$arrSb[$z]['field']]."</td>";
						}
					}
				}
				if ($this->OPTION_TYPE==NULL) $this->body .= "<td align='center' $style width='%'> $opt </td></tr>";
				$i++;
			}
		}
	}
	
	public function printHeader(&$hd) {
		$mOK = false;
		$header = '<style type="text/css">
					<!--
					#transID {
						float:inherit;
						background-color: #aaa;
						border-style:solid;
						border-color:#000;
						border-width:0px;
						width:'.$this->TABLE_WIDTH.'px;
					}
					#trans-mon{
						margin:auto;
						width:'.$this->TABLE_WIDTH.'px;
						padding-top : 8px;
						padding-bottom : 8px;
						padding-right : 8px;
					}
					#empt{
						float:none;
					}
					#frm-date{
						float:none;
						padding-top : 8px;
						padding-bottom : 8px;
					}
					#xls-file{
						position:absolute;
						padding-top : 8px;
						padding-bottom : 8px;
						left: 740px;
						top: 182px;
					}
					form{
						float:left;	
					}
					.aview {
						dispay:bloked;
						width:80px;
					}
					.frmclass {
						float:right;
					}
					.grayArea {
						background-color: #cfcfcf;
					}
					.totalArea {
						background-color: #993333;
						color: #fff;
					}
					-->
					</style>
					<script language="JavaScript">
						function funcNavigate(address) {
							window.location.href=address;
						}
					</script>
					';
		
			$params='';
			$width='15%';
			
			$p['area'] = $this->AREA;
			$p['tbl_transaction'] = $this->TBL_TRANSACTION;
			$p['start_date'] = $this->DEF_START_DATE;
			$p['end_date'] = $this->DEF_END_DATE;
			$p['bank'] = $this->BANK;
			$json = new Services_JSON();
			$bn='';
			if ($this->HEADER_TYPE==null) {
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
			}

			$p['bank_name'] = $bn;
			//echo "bank->".$p['bank_name'];
			$params = $json->encode($p);
			$params = base64_encode($params);
			$arrData = $this->DATA;
			$arrfield = $arrData['colTitle_All'];
			$this->createHeader($arrfield);
			$this->createBodyAll();
			$sbmparams = "a=".$this->AREA."&m=".$this->MODULE;
			$sbmparamsxls = $sbmparams."&importToXls=true";
			$sbmparams = base64_encode($sbmparams);
			$sbmparamsxls = base64_encode($sbmparamsxls);
			$paramsxls = $json->encode($this->DATA);
			$paramsxls = base64_encode($paramsxls);
			if ($this->HEADER_TYPE==null) {
				$header .= '<br><h1>Monitoring Transaksi '.$this->MODULE_NAME.'</h1><br><div id="trans-mon"><div id="frm-date">
				<form id="form1" name="form1" method="post" action="main.php?param='.$sbmparams.'">
							  <label>Bank : 
								<select name="bank" id="bank">'.$opt.'
								</select>
							  </label>
							  <label>Tanggal Awal : 
								<input type="text" name="start_date" id="start_date" value="'.$this->DEF_START_DATE.'"/>
							  </label><label>Tanggal Akhir : <input type="text" name="end_date" id="end_date" value="'.$this->DEF_END_DATE.'"/>
							  <input type="submit" name="button2" id="button2" value="Submit" /></label>
						</form>
						<form id="form2" name="form2" method="post" action="'.$this->EXCELFILE.'">
							  <input type="hidden" name="dataXls" id="dataXls" value="'.$paramsxls.'" />
							  <input type="submit" name="button" id="button" value="Export to xls"/>
						</form></div><div id="empt">&nbsp;</div>';
			} else {
				$header .= '<br><h1>Monitoring Transaksi '.$this->MODULE_NAME.'</h1><br><div id="trans-mon"><div id="frm-date">
				<form id="form1" name="form1" method="post" action="main.php?param='.$sbmparams.'">
							  <label>Tanggal Awal : 
								<input type="text" name="start_date" id="start_date" value="'.$this->DEF_START_DATE.'"/>
							  </label><label>Tanggal Akhir : <input type="text" name="end_date" id="end_date" value="'.$this->DEF_END_DATE.'"/>
							  <input type="submit" name="button2" id="button2" value="Submit" /></label>
						</form>
						<form id="form2" name="form2" method="post" action="'.$this->EXCELFILE.'">
							  <input type="hidden" name="dataXls" id="dataXls" value="'.$paramsxls.'" />
							  <input type="submit" name="button" id="button" value="Export to xls"/>
						</form></div><div id="empt">&nbsp;</div>';
			}
			$header .= "<div id='transID'><table cellpadding = '2' cellspacing='1' border='0' width='100%'>".$this->header_all;
			$header .= $this->body." </table></div></div>";
			$header .= "<div id='footer-table'><b>PERHATIAN</b> :<br>
							<ul><li>Informasi yang tercantum hanya bersifat sementara. Informasi final adalah hasil rekonsiliasi yang dilaporkan oleh tim rekonsiliasi VSI kepada pihak BANK dan ".$this->MODULE_NAME."</li><li>Tekan tombol SUBMIT untuk menampilkan ulang (update) data !.</li><ul>
						</div>";
					
			$hd = $header;
			$mOK = true;
		
		return $mOK;
		//echo $header;
	}
	
	function printHeaderDetail(&$hd,$body,$dtrs) {
		$mOK = false;
		$header = '<style type="text/css">
					<!--
					#transID {
						background-color: #aaa;
						border-style:solid;
						border-color:#000;
						border-width:0px;
						width:'.$this->TABLE_WIDTH.'px;
					}
					#trans-mon{
						margin:auto;
						width:'.$this->TABLE_WIDTH.'px;
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
						left: 740px;
						top: 182px;
					}
					form {
						float:left;
					}
					#empt{
						float:none;
					}
					.grayArea {
						background-color: #cfcfcf;
					}
					.totalArea {
						background-color: #993333;
						color: #fff;
					}
					-->
					</style>';
		
			$params='';
			$width='15%';
			
			$prm = base64_decode($_REQUEST['param']);
			$p = explode("&",$prm);
			array_pop($p);
			array_pop($p);
			$prm = implode("&",$p);
			//echo "bank->".$p['bank_name'];
			$json = new Services_JSON();
			$params = $json->encode($p);
			$params = base64_encode($prm);
			
			$pxls['area'] = $this->AREA;
			$pxls['tbl_transaction'] = $this->TBL_TRANSACTION;
			$pxls['start_date'] = $this->DEF_START_DATE;
			$pxls['end_date'] = $this->DEF_END_DATE;
			$pxls['bank'] = $this->BANK;
			$json = new Services_JSON();
			$sQ = "SELECT * FROM ".$this->TBL_BANK;
			$bn='';
			if ($resx = mysqli_query($this->DBLINK, $sQ)) {
				
				while($rowx = mysqli_fetch_array($resx))
				{	
					if ($this->BANK == $rowx['CDC_B_ID']) {
						$bn = $rowx['CDC_B_NAME'];
					}
				}
			
			} else {
				echo "error -> $this->TBL_BANK ".mysqli_error($DBLink);
			}
			$pxls['bank_name'] = $bn;
			$pxls['dateTrs'] = $dtrs;
			$arrData = $this->DATA;
			$arrfield = $arrData['colTitle_Detail'];
			$paramsxls = $json->encode($this->DATA);
			$paramsxls = base64_encode($paramsxls);
			$this->createBodyDetail();
			$this->createHeader($arrfield);
			
			$header .= '<br><h1>Monitoring Transaksi '.$this->MODULE_NAME.'</h1><br><div id="trans-mon"><div id="frm-date">
						<a href="main.php?param='.$params.'"><img border="0" src="image/icon/exit.gif" alt="Kembali" title="Kembali"></img> Kembali</a>
						&nbsp;&nbsp;<a href="'.$this->EXCELFILE.'?dateTrs='.$dtrs.
						'&module='.strtolower($this->MODULE_NAME).'&area='.$this->AREA.'&bank='.$this->BANK.'"><img border="0" src="image/icon/table.png" alt="Export to xls" title="Export to xls"></img></a>
						</div>';
			$header .= "<div id='transID'><table cellpadding = '2' cellspacing='1' border='0' width='100%'>".$this->header_all;
			$header .= $this->body."</table></div></div>";
			$header .= "<div id='footer-table'><b>PERHATIAN</b> :<br>
							<ul><li>Informasi yang tercantum hanya bersifat sementara. Informasi final adalah hasil rekonsiliasi yang dilaporkan oleh tim rekonsiliasi VSI kepada pihak BANK dan ".$this->MODULE_NAME."</li><li>Tekan tombol SUBMIT untuk menampilkan ulang (update) data !.</li><ul>
						</div>";
			$hd = $header;
			$mOK = true;
		
		return $mOK;
	}
	
	public function printData() {
		global $jcol;
		$printData ='';
		$i=0;$opt='';
		$prm = base64_decode($_REQUEST['param']);
		$dtl = @isset($_REQUEST['exe'])?$_REQUEST['exe']:'';
		if ($dtl=='') {
			if ($this->printHeader($header)) {
				//$printData = $header;
				echo $header;
			}
		} else {
			$dateTrs = @isset($_REQUEST['dateTrs'])?$_REQUEST['dateTrs']:'';
			$this -> printHeaderDetail($header,$printData,$dateTrs);
			echo $header;
		}
		//mysqli_close($this->DBLINK);
	}
	private function HeaderingExcel($filename){
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=$filename");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Pragma: public");
	}

}
?>