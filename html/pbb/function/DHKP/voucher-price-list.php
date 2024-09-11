<?php
global $data,$appDbLink,$application,$sdata;

$uid = $data->uid;
$uname = $data->uname;
$ppid = $data->ppid;
$cid = $data->cid;

$arAppConfig = $User->GetAppConfig($application);
$arModuleConfig = $User->GetModuleConfig($module);

function curPageURL() {
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function getDataPriceList(&$dataPrice,$ppid){
	global $DBLink;
	$OK = false;
	$query = sprintf(
		"SELECT CSM_VPP_PRODUCT_ID,CSM_VPP_SWPRICE,CSM_VPP_SELLPRICE,CSM_VPP_CSELL,CSM_VPP_PRODUCT_NAME,CSM_VPP_NOMINAL,CSM_VPP_PROFIT
		FROM CSCMOD_VOUCHER_PRICELIST_PP
		WHERE 
			CSM_VPP_PPID = '%s' ", 
		mysql_real_escape_string($ppid)
	);
	//echo $query;
	try {
		$result = mysqli_query($DBLink, $query);
		$i = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$dataPrice[$i]['CSM_VPP_PRODUCT_ID']= $row['CSM_VPP_PRODUCT_ID'];
			$dataPrice[$i]['CSM_VPP_SWPRICE']= $row['CSM_VPP_SWPRICE'];
			$dataPrice[$i]['CSM_VPP_SELLPRICE']= $row['CSM_VPP_SELLPRICE'];
			$dataPrice[$i]['CSM_VPP_PRODUCT_NAME']= $row['CSM_VPP_PRODUCT_NAME'];
			$dataPrice[$i]['CSM_VPP_CSELL']= $row['CSM_VPP_CSELL'];
			$dataPrice[$i]['CSM_VPP_NOMINAL']= $row['CSM_VPP_NOMINAL'];
			$dataPrice[$i]['CSM_VPP_PROFIT']= $row['CSM_VPP_PROFIT'];
			$i++;
		}
		mysqli_free_result($result);
		$OK = true;
		if (count($dataPrice)==0) $OK = false;
		
	} catch (Exception $e) {
		$OK = false;
	}
	return $OK;
}

function syncronizedDate($ppid,$cid="") {
	global $DBLink;
	$OK = false;
	$query = sprintf( "SELECT * FROM CSCMOD_VOUCHER_MARGIN WHERE  CSM_VM_CID = '%s' ", 	mysql_real_escape_string($cid));
	$result = mysqli_query($DBLink, $query);
	$i = 0;
	$margin = 0;
	if ($row = mysqli_fetch_assoc($result)) {
		$margin = $row['CSM_VM_MARGIN'];
	}
	mysqli_free_result($result);	
	$query = sprintf( "SELECT * FROM c_registry WHERE  C_R_KEY = '%s.PP.voucher.PC.other.profit' ", mysql_real_escape_string($ppid));
	$result2 = mysqli_query($DBLink, $query);
	$profit=200;
	if ($row = mysqli_fetch_assoc($result2)) {
		$profit = $row['C_R_VALUE']?$row['C_R_VALUE']:200;
	}
	//delete not exists
	$query = "DELETE FROM CSCMOD_VOUCHER_PRICELIST_PP WHERE CSM_VPP_PPID='".mysql_real_escape_string($ppid)."' AND CSM_VPP_PRODUCT_ID NOT IN(SELECT CSM_PL_PRODUCTID FROM CSCMOD_VOUCHER_PRICELIST)";
	mysqli_query($DBLink, $query);
	$query = "SELECT CSM_PL_PRODUCTID, CSM_PL_SELL_PRICE, CSM_PL_DETAIL_PRODUCT, CSM_PL_NOMINAL FROM CSCMOD_VOUCHER_PRICELIST";
//	echo $query;
	try {
		$result3 = mysqli_query($DBLink, $query);
		while ($row = mysqli_fetch_array($result3,MYSQL_ASSOC)) {
			$query=sprintf("SELECT  CSM_VPP_SELLPRICE FROM CSCMOD_VOUCHER_PRICELIST_PP WHERE CSM_VPP_PPID='%s' AND CSM_VPP_PRODUCT_ID='%s'", 								
								mysql_real_escape_string($ppid),
								mysql_real_escape_string($row['CSM_PL_PRODUCTID']));
			$result4=mysqli_query($DBLink, $query);
			if($row2 = mysqli_fetch_array($result4,MYSQL_ASSOC)){
								//echo $query." ".$row2['CSM_VPP_SELLPRICE']."  ".$row['CSM_PL_SELL_PRICE']." ". $margin."<br>";
								if(($row2['CSM_VPP_SELLPRICE']-($row['CSM_PL_SELL_PRICE']+$margin))<$profit){
									 $query=sprintf("UPDATE CSCMOD_VOUCHER_PRICELIST_PP SET CSM_VPP_SWPRICE=%s,
									CSM_VPP_SELLPRICE=%s,CSM_VPP_PROFIT=%s,CSM_VPP_NOMINAL=%s WHERE CSM_VPP_PPID='%s' AND CSM_VPP_PRODUCT_ID='%s'", 								
									mysql_real_escape_string($row['CSM_PL_SELL_PRICE']+$margin),
									mysql_real_escape_string($row['CSM_PL_SELL_PRICE']+$margin+$profit),
									mysql_real_escape_string($profit),
									mysql_real_escape_string($row['CSM_PL_NOMINAL']),
									mysql_real_escape_string($ppid),
									mysql_real_escape_string($row['CSM_PL_PRODUCTID'])
									);
								}else{
									 $query=sprintf("UPDATE CSCMOD_VOUCHER_PRICELIST_PP SET CSM_VPP_SWPRICE=%s,
									CSM_VPP_PROFIT=CSM_VPP_SELLPRICE-%s,CSM_VPP_NOMINAL=%s WHERE CSM_VPP_PPID='%s' AND CSM_VPP_PRODUCT_ID='%s'", 								
									mysql_real_escape_string($row['CSM_PL_SELL_PRICE']+$margin),
									mysql_real_escape_string($row['CSM_PL_SELL_PRICE']+$margin),
									mysql_real_escape_string($row['CSM_PL_NOMINAL']),
									mysql_real_escape_string($ppid),
									mysql_real_escape_string($row['CSM_PL_PRODUCTID'])
									);
								}
								mysqli_free_result($result4);
			}else{
				$query = sprintf( "INSERT INTO CSCMOD_VOUCHER_PRICELIST_PP (CSM_VPP_PPID,CSM_VPP_PRODUCT_ID,
								CSM_VPP_SWPRICE,CSM_VPP_SELLPRICE,CSM_VPP_PRODUCT_NAME,CSM_VPP_PROFIT,CSM_VPP_NOMINAL) 						
								VALUES ('%s','%s',%s,%s,'%s',%s,%s) ",
								mysql_real_escape_string($ppid),
								mysql_real_escape_string($row['CSM_PL_PRODUCTID']),
								mysql_real_escape_string($row['CSM_PL_SELL_PRICE']+$margin),
								mysql_real_escape_string($row['CSM_PL_SELL_PRICE']+$margin+$profit),
								mysql_real_escape_string($row['CSM_PL_DETAIL_PRODUCT']),
								mysql_real_escape_string($profit),
								mysql_real_escape_string($row['CSM_PL_NOMINAL']));
			}

			
			
			//echo $query;
			$result5 = mysqli_query($DBLink, $query);
			//mysqli_free_result($result4);
		}
		
	} catch (Exception $e) {
		echo $e->getMessage();
		$OK = false;
	}
	mysqli_free_result($result2);
	mysqli_free_result($result3);
	return $OK;
}

function getConfigOther(&$dataConnect,$ppid,$appDbLink) {
	$OK = false;
	$dataConnect = array();
	$i = 0;
	//PP.donasi.kodewilayah
	$configCode = $ppid.".PP.voucher.PC.other.profit";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	while ($row = mysqli_fetch_array($result)) {
		$dataConnect['profit'] = $row['C_R_VALUE'];
		$OK = true;
	}
	return $OK;
}
function updateAllPrice($ppid,$profit) {
	global $DBLink;
	updateOther($ppid,$DBLink,$profit);
	$query = sprintf(
		"UPDATE CSCMOD_VOUCHER_PRICELIST_PP SET CSM_VPP_SELLPRICE = (CSM_VPP_SWPRICE + %s), CSM_VPP_PROFIT = %s WHERE CSM_VPP_PPID = '%s'", 
		mysql_real_escape_string($profit),
		mysql_real_escape_string($profit),
		mysql_real_escape_string($ppid)
	);
	//echo $query;
	$result2 = mysqli_query($DBLink, $query);
}
function updateOther($ppid,$appDbLink,$profit) {
	//querying
	$configCode = $ppid.".PP.voucher.PC.other.profit";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($profit),
		mysql_real_escape_string($profit)
	);
	$result = mysqli_query($appDbLink, $query);
	echo mysqli_error($appDbLink);
	return (mysqli_affected_rows() == 1);
}

function displayTable ($ppid,$prms) {
	global $DBLink;
	$margin = 200;
	if (getConfigother($margin,$ppid,$DBLink) ) {
	   $margin = $margin;
	}
	
	$p=base64_encode($prms.'&sync=sok');
		
	$display = "Untuk melakukan sinkronisasi data silahkan klik <a href='main.php?param=".$p."'>disini</a> ! <br><br>";
	$display .= "<form name='priceList' method='post' action=''>
	            <table width='700' cellpadding='4'><tr><td><h3>Daftar Harga Voucher</h3></td></tr>
				<tr><td>Set Margin/laba Harga secara keseluruhan senilai &nbsp;&nbsp;<input type='text' value='".$margin['profit']."' size='10' name='umargin' id='umargin'>
				&nbsp;&nbsp;<input type='submit' value='Terapkan'></td></tr>
				<tr><td><table cellpadding='2' cellspacing='1' border='0' width='100%'>";
	$display .="<tr><th width='105'>ID</th>
	               <th width='110'>Detail</th>
				   <th width='100'>Nominan</th>
				   <th width='100'>Harga Switcher</th>
				   <th width='100'>Harga Jual</th>
				   <th width='100'>Laba</th>
				   </tr>";
	if (getDataPriceList($dataPrice,$ppid)) {
		$i=0;
		foreach($dataPrice as $row){
			$color = ($i%2 ? "#cfcfcf" : "#eee");
			$display .="<tr><td width='105' style='background-color:$color'>".$row['CSM_VPP_PRODUCT_ID'].
						"</td><td width='105' style='background-color:$color'>".$row['CSM_VPP_PRODUCT_NAME'].
						"</td><td width='110' align='right' style='background-color:$color'>". number_format ( $row['CSM_VPP_NOMINAL'] ,0 , "," , "." ).
						"</td><td width='100' align='right' style='background-color:$color'>". number_format ( $row['CSM_VPP_SWPRICE'] ,0 , "," , "." ).
						"</td><td width='100' align='right' style='background-color:$color'> <input type='text' name='profit-".$row['CSM_VPP_PRODUCT_ID'].
						"' id='profit-".$row['CSM_VPP_PRODUCT_ID']."' value=".
						number_format ( $row['CSM_VPP_SELLPRICE'] ,0 , "," , "." )."  
						maxlength='8' size='8' onKeyPress=\"return numbersonly(this, event)\" STYLE=\"text-align:right\"
						onKeyPress=\"return(currencyFormatI(this,'.',event))\" 
					    onSelect=\"return(onSelectClearFormat(this, '.'))\" 
					 	onBlur=\"return(currencyFormatIC(this,'.'))\"/> 
						</td><td width='200' align='right' style='background-color:$color'><label id='lbl-".$row['CSM_VPP_PRODUCT_ID']."'>". number_format ($row['CSM_VPP_PROFIT'] ,0 , "," , "." ).
						"</label>
						<a href='#'><img src='image/icon/table_save.png' onclick='saveItem(\"".$row['CSM_VPP_PRODUCT_ID']."\");' border='0' title='Save'></a>
						</td></tr>";
			$i++;
		}
	} else {
		$display .= "<BR><BR><BR><BR><br><h2>Terjadi Kesalahan !</h2> Data kosong atau tidak bisa di tampilkan.";
	}
	$display .= "</table></td></tr></table></form>";
	echo $display;
}
//main program
$getReq = (@isset($_REQUEST['param']) ? $_REQUEST['param'] : '');
$prms = base64_decode($getReq);

?>
<script language='javascript' src='inc/js/RTTFormat.js'></script>
<script language="javascript" src="function/voucher/voucher-reprint.js"></script>
<?php

if(isset($data)){
	if ($_REQUEST['umargin']) {
		//updateOther($ppid,$DBLink,$_REQUEST['umargin']);
		updateAllPrice($ppid,$_REQUEST['umargin']);
		displayTable ($ppid,$prms);
	}else {
		$pos = strpos($prms,'&sync=sok');
		if ($pos !== false) {
			syncronizedDate($ppid,$cid);
			displayTable($ppid,$prms);
			$p=explode("&",$prms);
			$prms = $p[0].'&'.$p[1].'&'.$p[2];	
		} else {
			displayTable ($ppid,$prms);
		}
	}
	echo "<input type=\"hidden\" id=\"ppid\" name=\"ppid\" value=\"".$ppid."\">";
}else{
	echo "rekues tidak diperkenankan";
}

?>

