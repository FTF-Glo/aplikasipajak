<?php

require_once("inc/payment/ctools.php");
require_once("inc/palyja/inc-palyja-config.php");
require_once("inc/palyja/palyja_lookup.php");

class classSearchTrans{
	public $DBLINK;
	public $TBL_TRANSACTION;
	public $LOOKUP_TABLE;
	public $TBL_DATA;
	public $CUSTOMERID;
	
	private $header;
	private $body;
	private $table;
	public $params;
	
	function getDataInfotexts(&$rows) {	
		$OK = false;
		$arrfield = $this->TBL_DATA;
		$arrDBL = LOOKUP_ALL_MONITORING($this->LOOKUP_TABLE,$this->DBLINK);
		$i=0;
		foreach ($arrDBL as $res2) {	
			$arrCol = $arrfield['colTitle'];
			$f = count($arrCol);
			$sQ =  $arrfield['query'];	
			SCANPayment_ConnectToDB($DBLink2, $DBConn2, trim($res2["DB_HOST"]), trim($res2["DB_USER"]), trim($res2["DB_PWD"]), trim($res2["DB_NAME"]));
			$sQ1 = str_replace("{TABLE}",trim($res2["DB_TABLE"]),$sQ);
			if ($res = mysqli_query($DBLink2, $sQ1)) {
				while ($rws = mysqli_fetch_array($res)){
					for ($x=0;$x<$f;$x++) {
						$rows[$i][$arrCol[$x]['field']] = $rws[$arrCol[$x]['field']];
				    }
					$i++;
					$OK = true;
				}
			} else {
				echo "Error : ".mysqli_error($DBLink2);
				SCANPayment_CloseDB($DBLink2);
			}
		}
		SCANPayment_CloseDB($DBLink2);
		return $OK;
	}
	
	function createHeader() {
		$arrData = $this->TBL_DATA;
		$arrCol = $arrData['colTitle'];
		$f = count($arrCol);
		$col = '';
		for ($x=0;$x<$f;$x++) {
			$col .= "<td align='center' width='".$arrCol[$x]['width']."'>".$arrCol[$x]['title']."</td>";
		}
		
		$this->header = "<tr class='tableTitle' ><td align='center' width='5'>No.</td>".$col."</tr>";	
	}
	
	function createBody() {
		if ($this->getDataInfotexts($rows)) {
			$i=0;
			$arrData = $this->TBL_DATA;
			$arrCol = $arrData['colTitle'];
			$f = count($arrCol);
			$col = '';
			foreach ($rows as $row) {
				$this->body .= "<tr><td  align='right'>".($i+1)."</td>";
				for ($x=0;$x<$f;$x++) {
					$this->body .= "<td align='".$arrCol[$x]['align']."' width='".$arrCol[$x]['width']."'>".$row[$arrCol[$x]['field']]."</td>";
				}
				$this->body .= "</tr>";
				$i++;
			}
		}
	}
	
	function createTable() {
		$prmAdd = "&status=add";
		$prmAdd = base64_decode($this->params).$prmAdd;
		$prmAdd = base64_encode($prmAdd);
		$this->createHeader();
		$this->createBody();
		$header = '<br><h1>Penelusuran Transaksi</h1><br><div id="trans-mon"><div id="frm-date">
			<form id="form1" name="form1" method="post" action="main.php?param='.$_REQUEST['param'].'">
						  <label>Id Pelanggan : 
							<input type="text" name="custoid" id="custoid" value="'.$this->CUSTOMERID.'"/>
						  </label><input type="submit" name="button2" id="button2" value="Cari" /></form></div><br>';
						  
		$this->table = '<style type="text/css">
				<!--
				#tbl-infotext {
					background-color: #aaa;
					border-style:solid;
					border-color:#000;
					border-width:0px;
					width:90%;
				}
				-->
				</style>';
			
		$this->table .= $header."<div id='tbl-infotext'><table cellpadding = '4' cellspacing='1' border='0' width='100%'>".$this->header.$this->body."</table></div>";
	}
	public function displayInfotext() {
		 $this->createTable();
		 echo $this->table;
	}
}
?>
