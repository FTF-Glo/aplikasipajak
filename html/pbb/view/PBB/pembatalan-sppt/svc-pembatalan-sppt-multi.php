<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembatalan-sppt', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once("classPembatalan.php");
require_once("CSVReader.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

function getNopStringFormCSV($name = 'file')
{
	$nop_string = '';
	if (isset($_FILES[$name])) {
		$csv = new CSVReader($_FILES[$name]['tmp_name'], ';');
		
		foreach ($csv as $value) {
			if (empty($value[0])) continue; // skip first line
			$nop_string .= "'". trim($value[0]) ."',";
		}

		$nop_string = rtrim($nop_string, ',');
	}
	return $nop_string;
}

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$q 			= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$thn_filter = @isset($_REQUEST['tahun']) ? (int)$_REQUEST['tahun'] : false;
$status 	= @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

$kel 	= @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$blok 	= @isset($_REQUEST['blok']) ? $_REQUEST['blok'] : "";
$urut 	= @isset($_REQUEST['urut']) ? $_REQUEST['urut'] : "";

$arrKode = [];

$k = explode(',',$urut);
if(count($k)>0 && $blok!='' && $urut!=''){
	foreach ($k as $urut){
		$kode 	= (int)(trim($kel) . trim($blok) . trim($urut));
		$arrKode[] = "'".$kode."0'";
		$arrKode[] = "'".$kode."1'";
		$arrKode[] = "'".$kode."2'";
		$arrKode[] = "'".$kode."3'";
		$arrKode[] = "'".$kode."4'";
	}
	$kode 	= false;
}else{
	$kode 	= (int)(trim($kel) . trim($blok));
}

if ($q=="") exit(1);

$q 			= base64_decode($q);
$j 			= $json->decode($q);
$uid 		= $j->uid;
$area 		= $j->a;
$moduleIds 	= $j->m;

$arConfig 	= $User->GetModuleConfig($moduleIds);
$appConfig 	= $User->GetAppConfig($area);
$tahun		= $appConfig['tahun_tagihan'];


$host 	= $_REQUEST['GW_DBHOST'];
$port 	= $_REQUEST['GW_DBPORT'];
$user 	= $_REQUEST['GW_DBUSER'];
$pass 	= $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME'];

$svcPembatalan = new SvcPembatalanSPPT($dbSpec);

$svcPembatalan->C_HOST_PORT = $host;
$svcPembatalan->C_USER 		= $user;
$svcPembatalan->C_PWD 		= $pass;
$svcPembatalan->C_DB 		= $dbname;
$svcPembatalan->C_PORT 		= $port;

if(stillInSession($DBLink,$json,$sdata)){
    $htmlData = "
	<style>
		#box1-multi, #box2-multi{
			display:none;
			position:fixed;
			height:100%;
			width:100%;
			top:0;
			left:0
		}
		#box1-multi{
			background-color:#000000;
			filter:alpha(opacity=70);
			opacity:0.7;
			z-index:1;
		}
		#box2-multi{
			z-index: 2;
		}
		#closednomor{cursor: pointer;}
		.table th {
			color: #fff;
			font-weight:bold;
			padding:5px !important;
			text-align:center;
			vertical-align:middle !important;
			border:solid 1px #CCCCCC;
			background-color:#107138;
			background-image:linear-gradient(to bottom right, #107138, #209550);
		}
	</style>
	<script>
	function getSelectedNOPChunk() {
		let chunk_data = [],
			chunk_size = 200,
			counter = 0,
			index = 0;

		$('.cek').each(function(e){
			if ($(this).is(':checked')){
				if (counter == chunk_size) {
					index++;
					counter = 0;
				}

				if (typeof chunk_data[index] == 'undefined') {
					chunk_data[index] = {
						nop: [],
						tahun: [],
						tahun_tagihan: []
					};
				}

				chunk_data[index].nop.push($(this).attr('data-nop'));
				chunk_data[index].tahun.push($(this).attr('data-tahun'));
				chunk_data[index].tahun_tagihan.push('". $tahun ."');

				counter++;
			}

		});

		return chunk_data;
	}

	function getSelectedNOP() {
	    let arr_nop = [];
	    let arr_tahun = [];
	    let arr_tahun_tagihan = [];
	    
	    $('.cek').each(function(e){
            if ($(this).is(':checked')){                 
                arr_nop.push($(this).attr('data-nop'));
                arr_tahun.push($(this).attr('data-tahun'));
                arr_tahun_tagihan.push('". $tahun ."');
            }

        });
	    
	    return {
	        nop: arr_nop,
	        tahun: arr_tahun,
	        tahun_tagihan: arr_tahun_tagihan
	    };
	}
	
	$(document).ready(function() {
		$(\".btn-pembatalan\").click(function(){
			//alert('test');
            $(\"#box1-multi\").css(\"display\",\"block\");
            $(\"#box2-multi\").css(\"display\",\"block\");
//            var wp = $(this).attr(\"id\");
//            var v_wp = wp.split(\"+\");
//			
//			$(\"#nop\").attr(\"value\",v_wp[0]);
//			$(\"#thn\").attr(\"value\",v_wp[1]);
//			$(\"#uid\").attr(\"value\",v_wp[2]);
        });
        $(\"#closednomor\").click(function(){
            $(\"#box2-multi\").css(\"display\",\"none\");
            $(\"#box1-multi\").css(\"display\",\"none\");
        });
		$(\"#btn-batal-multi\").click(function(){
            $(\"#box2-multi\").css(\"display\",\"none\");
            $(\"#box1-multi\").css(\"display\",\"none\");
        });
        $(\"#btn-ya-multi\").click(function(){
				
			var proses = 1;
			var no_sk = $('#batal-nomor-sk').val();
			var alasan = $('#batal-alasan').val();
			if (no_sk==''){
				alert('Silahkan isi NO SK');
				exit;
			}	
			if (alasan==''){
				alert('Silahkan isi Alasan Penolakan');
				exit;
			}
			/*var string_nop = '';
	    	var string_tahun = '';

			$('.cek').each(function(e){
  		  		if ($(this).is(':checked')){
		    		 string_nop += '&data[nop][]='+$(this).attr('data-nop') ;	
		    		 string_tahun += '&data[tahun][]='+$(this).attr('data-tahun') ;	
		    		 
		    		 arr_nop.push($(this).attr('data-nop'));
                     arr_tahun.push($(this).attr('data-tahun'));
  		  		}

		  	});*/

            $.ajax({
               type: \"POST\",
               url: \"view/PBB/pembatalan-sppt/svc-proses-pembatalan-multi.php\",
			   /* data: \"uid=\"+$(\"#uid\").val()+\"&alasan=\"+alasan+\"&no_sk=\"+no_sk+\"&proses=\"+proses+\"&tahun=\"+$(\"#thn\").val()+\"&GW_DBHOST=\"+GW_DBHOST+\"&GW_DBNAME=\"+GW_DBNAME+\"&GW_DBUSER=\"+GW_DBUSER+\"&GW_DBPWD=\"+GW_DBPWD+\"&GW_DBPORT=\"+GW_DBPORT+\"&USER_LOGIN=\"+USER_LOGIN+string_nop+string_tahun, */
			   data: {
                    uid: $('#uid').val(),
                    alasan: alasan,
                    no_sk: no_sk,
                    proses: proses,
                    tahun: $('#thn').val(),
                    GW_DBHOST: GW_DBHOST,
                    GW_DBNAME: GW_DBNAME,
                    GW_DBUSER: GW_DBUSER,
                    GW_DBPWD: GW_DBPWD,
                    GW_DBPORT: GW_DBPORT,
                    USER_LOGIN: USER_LOGIN,
                    data: getSelectedNOP()
                },
               dataType : \"json\",
			   success: function(data){
			   
			       $(\"#box2-multi\").hide();
                   $(\"#box1-multi\").hide();
				   console.log(data.message)
				   if(data.respon==true){
					   alert(\"Pembatalan SPPT Sukses!\");
					   $('#monitoring-content-10').html('');
					   $('#multi').trigger('click');
					   $('#daftarNOP').html();
				   } else alert('Pembatalan SPPT Gagal!');
               },
			   error : function(data){
				   console.log(data)
			   }
             });
        });
		$(\"#btn-tidak-multi\").click(function(){
			var proses = 2;
			var no_sk = $('#batal-nomor-sk').val();
			var alasan = $('#batal-alasan').val();
			if (no_sk==''){
				alert('Silahkan isi NO SK');
				exit;
			}	
			if (alasan==''){
				alert('Silahkan isi Alasan Penolakan');
				exit;
			}
//			var string_nop = '';
//	    	var string_tahun = '';
//
//			$('.cek').each(function(e){
//  		  		if ($(this).is(':checked')){
//		    		 string_nop += '&data[nop][]='+$(this).attr('data-nop') ;	
//		    		 string_tahun += '&data[tahun][]='+$(this).attr('data-tahun') ;	
//  		  		}
//
//		  	});

			let selectedChunks = getSelectedNOPChunk();

			selectedChunks.forEach(data => {
				$.ajax({
					type: \"POST\",
					url: \"view/PBB/pembatalan-sppt/svc-proses-pembatalan-multi.php\",
						data: {
						 uid: $('#uid').val(),
						 alasan: alasan,
						 no_sk: no_sk,
						 proses: proses,
						 tahun: $('#thn').val(),
						 GW_DBHOST: GW_DBHOST,
						 GW_DBNAME: GW_DBNAME,
						 GW_DBUSER: GW_DBUSER,
						 GW_DBPWD: GW_DBPWD,
						 GW_DBPORT: GW_DBPORT,
						 USER_LOGIN: USER_LOGIN,
						 data: data
					 },
					 dataType : \"json\",
					 success: function(data){
						 $(\"#box2-multi\").hide();
						 $(\"#box1-multi\").hide();
						 console.log(data.message)
						 if(data.respon==true){
							 alert(\"Pembatalan SPPT Sukses!\");
							 $('#monitoring-content-10').html('');
							 $('#multi').trigger('click');
							 $('#daftarNOP').html();
	 
						 } else alert('Pembatalan SPPT Gagal!');
					 },
					 error : function(data){
						 console.log(data)
					 }
				 });
			});
			
            /*$.ajax({
               type: \"POST\",
               url: \"view/PBB/pembatalan-sppt/svc-proses-pembatalan-multi.php\",
               	data: {
					uid: $('#uid').val(),
					alasan: alasan,
					no_sk: no_sk,
					proses: proses,
					tahun: $('#thn').val(),
					GW_DBHOST: GW_DBHOST,
					GW_DBNAME: GW_DBNAME,
					GW_DBUSER: GW_DBUSER,
					GW_DBPWD: GW_DBPWD,
					GW_DBPORT: GW_DBPORT,
					USER_LOGIN: USER_LOGIN,
					data: getSelectedNOP()
                },
				dataType : \"json\",
				success: function(data){
					$(\"#box2-multi\").hide();
					$(\"#box1-multi\").hide();
					console.log(data.message)
					if(data.respon==true){
						alert(\"Pembatalan SPPT Sukses!\");
						$('#monitoring-content-10').html('');
						$('#multi').trigger('click');
						$('#daftarNOP').html();

					} else alert('Pembatalan SPPT Gagal!');
				},
				error : function(data){
					console.log(data)
				}
			});*/
        });
		
	});
	</script>
	<div id=\"box2-multi\">
		
		<div align=\"center\" id=\"setnomor\" style=\"width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;\">
		<div style=\"width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;\"><div id=\"closednomor\" style=\"float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;\">X</div></div>
					<br>
					<br>
					<div class='wadah' style='margin-left:15px;margin-bottom:50px;'>
						<div style='float:left;width:100px;text-align:left'>
							Nomor SK :
						</div>
						<div style='float:left;width:100px;text-align:left'>
							<input type=\"text\" id=\"batal-nomor-sk\" placeholder=\"Nomor SK\" />
						</div>
						<br>
						<br>
						<br>
						<div style='float:left;width:100px;'>
						Alasan :
						</div>
						<div style='float:left;width:100px;'>
						<textarea row='2' type=\"text\" id=\"batal-alasan\" placeholder=\"Alasan Pembatalan\" ></textarea>
						</div>
					</div>
					
		
		
		<div style=\"margin: 10px;margin-left: 10px;\">

		
		Ubah data NOP menjadi Fasilitas Umum?<br><br>

		<form action=\"\">
			<input type=\"hidden\" id=\"nop\"/>
			<input type=\"hidden\" id=\"thn\"/>
			<input type=\"hidden\" id=\"uid\"/>
			<input type=\"button\" name=\"btn-ya-multi\" id=\"btn-ya-multi\" value=\"Ya\">
			<input type=\"button\" name=\"btn-tidak-multi\" id=\"btn-tidak-multi\" value=\"Tidak\">
			<input type=\"button\" name=\"btn-batal-multi\" id=\"btn-batal-multi\" value=\"Batal\">
		</form>
        </div>
    </div>
	</div>
	<div id=\"box1-multi\"></div>";

    $htmlData1 = $htmlData;
    $htmlData = '';
	$i = 0;
    $res = $svcPembatalan->getGateWayPBBSPPTMultiV2($thn_filter, $kode, $arrKode);
	
    $rowCount = mysqli_num_rows($res);
    while ($row = mysqli_fetch_assoc($res)){
        $htmlData .= "
			<tr>
				<td><input data-nop='".$row['NOP']."' data-tahun='".$row['SPPT_TAHUN_PAJAK']."' type='checkbox' name='cek' class='cek'> </td>
				<td align=right>". (++$i) ."</td>
				<td align=center>".$row['SPPT_TAHUN_PAJAK']."</td>
				<td>" . $row['NOP'] ."</td>
				<td>".$row['WP_NAMA']."</td>
				<td>".$row['WP_ALAMAT']."</td>
				<td>".$row['OP_ALAMAT']."</td>
				<td align=right>".number_format($row['SPPT_PBB_HARUS_DIBAYAR'],0,',','.')."</td>
			</tr>";
    }

    if($rowCount>0){
        echo "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\" style=\"margin-top:2em\"> ". $htmlData1 ."
                
                <div style='margin-bottom: 1em'>
                    <button class='btn btn-danger btn-pembatalan'>Batalkan SPPT</button>
                </div>
                
                <table class=\"table table-bordered table-striped table-hover\">
                    <tr>
                        <th width=5><input type='checkbox' name='cek-all' id='all-check-button' ></th>
                        <th width=5>NO</th>
                        <th width=5>TAHUN PAJAK</th>
                        <th width=5>NOP</th>
                        <th>NAMA WP</th>
                        <th>ALAMAT WP</th>
                        <th>ALAMAT OP</th>
                        <th width=70>TAGIHAN</th>
                    </tr>
                    ".$htmlData."
                </table>
                
                <div style='margin-top: 1em'>
                    <button class='btn btn-danger btn-pembatalan'>Batalkan SPPT</button>
                </div>
                </div>";
    }else{
        echo  "Data tidak ditemukan !\n";
    }
}else{
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
