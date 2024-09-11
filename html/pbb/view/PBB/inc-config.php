<?php
	define("DBTYPE_MYSQL",0);
	define("DBTYPE_POSTGRESSQL",1);
	define("DBTYPE",DBTYPE_POSTGRESSQL);
	define("DBHOST","10.24.110.3");
	define("DBPORT","5432");
	define("DBNAME","db-pajak-palembangkota");
	define("DBUSER","payment_pbb");
	define("DBPWD","SS26P@ssw0rd");
	define("DBTABLE","PBB.PBB_SPPT");
	define("PBB_MAXPENALTY_MONTH",24);
	define("PBB_ONE_MONTH",30);
	define("PBB_PENALTY_PERCENT",2);

	$ALLOWEDCLIENT=array();
	$ALLOWEDCLIENT["1671"]="n4f37e83a746531.97349520";

	function AllowedClient($client,$area){
		global $ALLOWEDCLIENT;
		$bok=false;
		if(isset($ALLOWEDCLIENT[$area])){
			return $ALLOWEDCLIENT[$area]==$client;
		}
		return $bok;
	}

	function DB_connect($dbhost,$dbport,$dbname,$dbuser,$dbpwd){
		$dbconn=false;
		if(DBTYPE==DBTYPE_POSTGRESSQL){
			$dbconn = pg_connect("host=".$dbhost." port=".$dbport." dbname=".$dbname." user=".$dbuser." password=".$dbpwd);
		}else{
			$dbconn = mysqli_connect($dbhost,$dbuser,$dbpwd,$dbname,$dbport);
			//mysql_select_db($dbname,$dbconn);
		}
		return $dbconn;
	}

	function DB_close($dbconn){
		$dbconn=false;
		if(DBTYPE==DBTYPE_POSTGRESSQL){
			$dbconn = pg_close($dbconn);
		}else{
			$dbconn = mysqli_close($dbconn);			
		}
		return $dbconn;
	}

	function DB_query($query,$dbconn){
		$result=false;
		if(DBTYPE==DBTYPE_POSTGRESSQL){
			$result=pg_query($dbconn, $query);
		}else{
			$result=mysqli_query($dbconn, $query);			
		}
		return $result;
	}

	function DB_fetch_array($result){
		if(DBTYPE==DBTYPE_POSTGRESSQL){
			return pg_fetch_array($result);
		}else{
			return mysqli_fetch_array($result);			
		}
	}

	function DB_escape($str,$dbconn){
		if(DBTYPE==DBTYPE_POSTGRESSQL){
			return pg_escape_string($str);
		}else{
			return mysql_real_escape_string($str,$dbconn);			
		}
	}

	function GetListByNOP($nop){
		$dbconn = DB_connect(DBHOST,DBPORT,DBNAME,DBUSER,DBPWD);
		$nop=DB_escape($nop,$dbconn);		
		$result = DB_query( "SELECT * FROM ".DBTABLE." where NOP='$nop'",$dbconn);
		if (!$result) {
		  exit;
		}		
		echo "<table border='1'>";
		echo "<tr><th>NO</th><th>NAMA WAJIB PAJAK</th>";
		//echo "<th>ALAMAT </th>";
		echo "<th align='center'>TAHUN PAJAK</th><th>PBB</th><th>DENDA (*)</th><th>KURANGBAYAR</th><th>STATUS PEMBAYARAN</th></tr>";
		$i=0;

		while ($row = DB_fetch_array($result)) {
		  echo "<tr>";
		  echo "<td class='tableTitle'>".(++$i)."</td>";
		  echo "<td>".$row["wp_nama"]."</td>";
		  //echo "<td>WAJIB PAJAK : ".$row["wp_alamat"]." RT ". $row["wp_rt"]."/".$row["wp_rw"]." ". $row["wp_kelurahan"]." ". $row["wp_kecamatan"] ." ". $row["wp_kotakab"]." ". $row["wp_kodepos"]."<br/>";	
		  //echo "OBJEK PAJAK : ".$row["op_alamat"]." RT ". $row["op_rt"]."/".$row["op_rw"]." ". $row["op_kelurahan"]." ". $row["op_kecamatan"] ." ". $row["op_kotakab"]." ". $row["op_kodepos"]."</td>";	
		  echo "<td align='center'>".$row["sppt_tahun_pajak"]."</td>";
		  echo "<td align='right'>Rp. ".number_format($row["sppt_pbb_harus_dibayar"],0,",",".")."</td>";
		  //denda
		  $jatuhtempo=$row["sppt_tanggal_jatuh_tempo"];
		  $dtjatuhtempo=mktime(0,0,0,substr($jatuhtempo,5,2),substr($jatuhtempo,8,2),substr($jatuhtempo,0,4));
		  $dtnow=time();
		  $dayinterval=ceil(($dtnow-$dtjatuhtempo)/(24*60*60));
		  $monthinterval=ceil($dayinterval/PBB_ONE_MONTH);
		  if($monthinterval<0) $monthinterval=0;
		  $monthinterval=$monthinterval>=PBB_MAXPENALTY_MONTH?PBB_MAXPENALTY_MONTH:$monthinterval;
		  //echo $monthinterval. " ";
		  $denda=((PBB_PENALTY_PERCENT/100)*$monthinterval*$row["sppt_pbb_harus_dibayar"]);
		  echo "<td align='right'>Rp. ".number_format(floor($denda),0,",",".")."</td>";
		  if($row["payment_flag"]!=1){
			echo "<td align='right'>Rp. ".number_format(floor($denda+floatval($row["sppt_pbb_harus_dibayar"])),0,",",".")."</td>";
			echo "<td>-</td>";
			$total+=floor($denda+floatval($row["sppt_pbb_harus_dibayar"]));
		  }else{
			echo "<td align='right'>Rp. ".number_format(0,0,",",".")."</td>";
			echo "<td><b>LUNAS</b><i> ".$row["payment_paid"]."</i></td>";
		  }
	
		  echo "</tr>";		
		  
		  $i++;
		}
		echo "<tr><th colspan='5' align='right'>TOTAL</th><th align='right'>Rp. ".number_format(floor($total),0,",",".")."</i></th><th>&nbsp;</th></tr></table>";
		echo "<b>* Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.</b><br/>";
		if($i==0){
			echo "<span style='color:red'><b>Data Tidak Ditemukan !</b></span>";
		}
	}

	function displayChecker($place="Palembang",$checker1="..................................",$checker2=".................................."){
	
		echo "<br/><table border='0'>";
		echo "<tr><td width='200'>&nbsp;</td><td width='300'>&nbsp;&nbsp;&nbsp;</td><td align='center' width='200'>$place,".strftime("%d-%m-%Y")."</td></tr>";
		echo "<tr><td align='center'>Mengetahui,</td><td>&nbsp;&nbsp;&nbsp;</td><td align='center'>Petugas,</td></tr>";
		echo "<tr height='50'><td>&nbsp;</td><td>&nbsp;&nbsp;&nbsp;</td><td>&nbsp;</td></tr>";
		echo "<tr><td align='center'>($checker1)</td><td>&nbsp;&nbsp;&nbsp;</td><td align='center'>($checker2)</td></tr>";
		echo "</table>";
	}
