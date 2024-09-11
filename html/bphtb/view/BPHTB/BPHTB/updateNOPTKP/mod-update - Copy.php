<?php ini_set("display_errors",1);
if ($data) {
		$uid = $data->uid;		
		$bOk = $User->GetModuleInArea($uid, $area, $moduleIds);
		if (!$bOK) {
			return false;
		}
		//$fungsi=$func[0][id];
		//$fungsi2=$func[1][id];
		$sRootPath = str_replace('\\', '/', str_replace('view/BPHTB/updateNOPTKP', '', dirname(__FILE__)));
		require_once($sRootPath . "function/BPHTB/updateNOPTKP/func-update.php");
		require_once($sRootPath . "function/BPHTB/updateNOPTKP/ps_pagination.php");
?>
<script type="text/javascript" src="function/BPHTB/updateNOPTKP/javascript-update.js"></script>
<style type="text/css">
.red_color {
	color:#FF0000;
	background-color:#c5ddfe;
	font-size:11px;
	font-family:Tahoma, Geneva, sans-serif;
}
.black_color {
	color:#000000;
	font-size:11px;
	font-family:Tahoma, Geneva, sans-serif;
}
.grey_bg {
	color:#000000;
	font-size:11px;
	font-family:Tahoma, Geneva, sans-serif;
	background-color:#efefef;
}
.clsbtn {
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf) );
	background:-moz-linear-gradient( center top, #ededed 5%, #dfdfdf 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf');
	background-color:#ededed;
	-moz-border-radius:3px;
	-webkit-border-radius:3px;
	border-radius:3px;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#000;
	font-family:Tahoma, Geneva, sans-serif;
	font-size:10px;
	font-weight:bold;
	padding:1px 8px;
	text-decoration:none;
	text-shadow:1px 1px 0px #ffffff;
}.clsbtn:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #dfdfdf), color-stop(1, #ededed) );
	background:-moz-linear-gradient( center top, #dfdfdf 5%, #ededed 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#dfdfdf', endColorstr='#ededed');
	background-color:#dfdfdf;
}.clsbtn:active {
	position:relative;
	top:1px;
}

.class_header {
	color:#fff;
	font-family:Tahoma, Geneva, sans-serif;
	font-size:12px;
	font-weight:bold;
	background-color:#4794fd;
}
</style>
<div align="left">
	<form method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m")?>">
    <!--<h3>Form pencarian</h3>-->
		<table class='transparent'>
			<tr>
				<td>NO KTP / NOP / (NO KTP, NOP)</td>
                <td><input name="noKTP_NOP" type="text" id="noKTP_NOP" autocomplete="off" value="0951050910500364, 320403101001702220" size="40" maxlength="40" /></td>
				<td><input type="Submit" id="submit" name="submit" value="Cari" /></td>
		    </tr>
		</table>
        <font size="-2">
        Agar dapat meng update data masukan NO KTP dan NOP dengan format &lt;NO KTP&gt;&lt;koma&gt;&lt;NOP&gt; contoh: 0951050910500XXX, 320403101001702XXX 
        </font>
        
        <?php
        //echo "<pre>"; print_r($_REQUEST); echo "</pre>";
			//req_noKTP_NOP($_REQUEST['noKTP_NOP'],$_REQUEST['no_ktp'],$_REQUEST['nop']);
		echo "<div id='ajaxDiv'>"; if(isset($_REQUEST['bt_submit'])){ prosesUpdate($_REQUEST);	} echo"</div>";
		if(isset($_REQUEST['noKTP_NOP'])){
			req_noKTP_NOP($_REQUEST['noKTP_NOP'],$_REQUEST['no_ktp'],$_REQUEST['nop']);
		?><br />
        <table width="100%" border="0" cellspacing="1" cellpadding="4">
          <tr>
            <td width="1%" scope="col" align="center" class="class_header">No</th>
            <td width="10%" scope="col" align="center" class="class_header">No KTP</th>
            <td width="20%" scope="col" align="center" class="class_header">Nama WP</th>
            <td width="6%" scope="col" align="center" class="class_header">NOP</th>
            <td width="7%" scope="col" align="center" class="class_header">BPHTB</th>
            <td width="7%" scope="col" align="center" class="class_header">Nilai Pajak</th>
            <td width="7%" scope="col" align="center" class="class_header">NOPTKP</th>
            <td width="5%" scope="col" align="center" class="class_header">Tgl Lapor</th>
            <td width="5%" scope="col" align="center" class="class_header">Tgl Setuju</th>
            <td width="5%" scope="col" align="center" class="class_header">Tgl Exp</th>
            <!--<th width="12%" scope="col">Ket</th>-->
            <td width="3%" scope="col" align="center" class="class_header">Update</th>
          </tr>
          <?php
		  $DbHost="192.168.30.2:7306";  $DbUser="root"; $DbPwd="rahasia"; $DbName="bphtb"; $btUp=false;
		  SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName);

			$conn = mysql_connect($DbHost,$DbUser,$DbPwd);
			if(!$conn) die("Failed to connect to database!");
			$status = mysql_select_db($DbName, $conn);
			if(!$status) die("Failed to select database!");
			$sql = "SELECT * FROM VSI_SWITCHER_DEVEL.cppmod_ssb_doc WHERE CPM_WP_NOKTP='$_REQUEST[no_ktp]' or  CPM_OP_NOMOR='$_REQUEST[nop]' ORDER BY  CPM_WP_NOKTP, CPM_SSB_CREATED, CPM_OP_NOMOR ASC";
			$per_page=50;
			$pager = new PS_Pagination($conn, $sql, $per_page, 5, "a=$a&m=$m&no_ktp=$_REQUEST[no_ktp]&nop=$_REQUEST[nop]&noKTP_NOP=$_REQUEST[noKTP_NOP]");
			$pager->setDebug(true);
			$rs = $pager->paginate();
			if(!$rs) die(mysqli_error());

			$page =(isset($_REQUEST['page']))?$_REQUEST['page']:1;
			$no=(($page-1)*$per_page);
			$exp_data_ar=array();
			$exp_data_ar[$no]="";
			$tgl_lapor_ar=array();
			$tgl_lapor_ar[$no]="";
			$i=0;
			while($row=mysqli_fetch_array($rs)) {
			  $no++;
			  if($row['CPM_OP_NOMOR']===$_REQUEST['nop'] and $_REQUEST['no_ktp']!=$_REQUEST['nop']){
				$c_color="red_color";
				$btUp=true;
			  }else{ 
			  	$c_color = $i%2==0 ? "black_color":"grey_bg";
			  	//$c_color="black_color"; 
				$btUp=false;
			  }
			$i++;
			
			$sql="SELECT * FROM bphtb.ssb where wp_noktp = '$row[CPM_WP_NOKTP]' and op_nomor='$row[CPM_OP_NOMOR]'";
			$qu=mysqli_query($LDBLink, $sql) or die("#er 02".mysqli_error($LDBLink));
			$r=mysqli_fetch_assoc($qu);
			$tgl_setuju = $r['saved_date'];
			$tgl_exp = $r['expired_date'];
			$tgl_lapor=$row['CPM_SSB_CREATED'];

			if(!empty($tgl_setuju)){
			$ts = explode(" ",$tgl_setuju);
			$_ts = explode("-",$ts[0]);
			$tgl_setuju = $_ts[2]."-".$_ts[1]."-".$_ts[0];
			}
			
			if(!empty($tgl_exp)){
			$te = explode("-",$tgl_exp);
			$tgl_exp = $te[2]."-".$te[1]."-".$te[0];
			}
			
			$tl = explode(" ",$tgl_lapor);
			$_tl = explode("-",$tl[0]);
			$tgl_lapor = $_tl[2]."-".$_tl[1]."-".$_tl[0];

			$no2=$no-1;
			$exp_data_ar[$no]=$tgl_exp; 
			$tgl_lapor_ar[$no]=$tl[0];
			
			if(!empty($exp_data_ar[$no2])){
			  $eda = explode("-",$exp_data_ar[$no2]);
			  //print_r($eda); echo"<br>";
			  $day_limit = mktime(0,0,0,$eda[1],$eda[0]+7,$eda[2]);
			  $exp_data_ar_no2 = date("Y-m-d",$day_limit);
			}else{
			  $exp_data_ar_no2 = "";
			}
			//echo"<br>||".$exp_data_ar_no2;
		  ?>
          <tr valign="top">
            <td class="<?php echo $c_color; ?>"><div align="center"><?php echo $no; ?></div></td>
            <td class="<?php echo $c_color; ?>"><div align="center"><?php echo $row['CPM_WP_NOKTP']; ?></div></td>
            <td class="<?php echo $c_color; ?>"><?php echo $row['CPM_WP_NAMA']; ?></td>
            <td class="<?php echo $c_color; ?>"><div align="center"><?php echo $row['CPM_OP_NOMOR']; ?></div></td>
            <td class="<?php echo $c_color; ?>"><div align="right"><?php echo number_format($row['CPM_OP_BPHTB_TU'],0,",","."); ?></div></td>
            <td class="<?php echo $c_color; ?>"><div align="right"><?php echo number_format($row['CPM_SSB_AKUMULASI'],0,",","."); ?></div></td>
            <td class="<?php echo $c_color; ?>"><div align="right"><?php echo number_format($row['CPM_OP_NPOPTKP'],0,",","."); ?></div></td>
            <td class="<?php echo $c_color; ?>"><div align="center"><?php echo $tgl_lapor; ?></div></td>
            <td class="<?php echo $c_color; ?>"><div align="center"><?php echo (!empty($tgl_setuju))?$tgl_setuju:"____"; ?></div></td>
            <td class="<?php echo $c_color; ?>"><div align="center"><?php echo (!empty($tgl_exp))?$tgl_exp:"____"; ?></div></td>
            <!--<td class="<?php echo $c_color; ?>">____</td>-->
            <td width="50" class="<?php echo $c_color; ?>">
			<?php 
			$q = array();
			$q['up_no_ktp'] = $_REQUEST['no_ktp'];
			$q['up_nop'] = $_REQUEST['nop'];
			$q['up_noptk'] = $row['CPM_OP_NPOPTKP'];
			$q['up_akumulasi'] = $row['CPM_SSB_AKUMULASI'];
			$q['up_jenis_hak'] = $row['CPM_OP_JENIS_HAK'];
			$q['tgl_skr'] = date("Y-m-d");
			$q['exp_data_sebelumnya'] = $exp_data_ar_no2;
			$q['tgl_lapor_skr'] = $tgl_lapor_ar[$no];
			$q['tgl_lapor_sebelumnya'] = $tgl_lapor_ar[$no2];
			$json = new Services_JSON();
			$str = $json->encode($q);
			$str = base64_encode($str); //echo $str;

			echo($btUp==true)?"
			<input type=\"hidden\" value=\"".$_REQUEST['no_ktp']."\" name=\"up_no_ktp\" /> 
			<input type=\"hidden\" value=\"".$_REQUEST['nop']."\" name=\"up_nop\" />
			<input type=\"hidden\" value=\"".$row['CPM_OP_NPOPTKP']."\" name=\"up_noptk\" />
			<input type=\"hidden\" value=\"".$row['CPM_SSB_AKUMULASI']."\" name=\"up_akumulasi\" />
			<input type=\"hidden\" value=\"".$row['CPM_OP_JENIS_HAK']."\" name=\"up_jenis_hak\" />
			<input type=\"hidden\" value=\"".date("Y-m-d")."\" name=\"tgl_skr\" />
			<input type=\"hidden\" value=\"".$exp_data_ar_no2."\" name=\"exp_data_sebelumnya\" />
			<input type=\"hidden\" value=\"".$tgl_lapor_ar[$no]."\" name=\"tgl_lapor_skr\" />
			<input type=\"hidden\" value=\"".$tgl_lapor_ar[$no2]."\" name=\"tgl_lapor_sebelumnya\" />
			
			<input type=\"button\" name=\"bt_update\" id=\"button\" value=\"Update\" onclick=\"getAjax('$str')\" class=\"clsbtn\" />
			":""; ?> 
            </td>
          </tr>
          <?php 
		  	} 

			echo "<tr><td colspan='12'  align='center'>";
			echo $pager->renderFullNav(); 
			echo "</td></tr>";
		  ?>
        </table>
        <?php } 
		?>
  </form>
</div>
<?php } ?>