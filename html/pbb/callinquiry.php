<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$arr_RC = array(
"00"=>"SUKSES, INQUIRY / PEMBAYARAN BERHASIL",
"14"=>"GAGAL, TAGIHAN TIDAK DITEMUKAN",
"88"=>"GAGAL, TAGIGAN SUDAH LUNAS",
"89"=>"GAGAL, TOTAL BAYAR TIDAK SESUAI DENGAN TAGIHAN",
"04"=>"GAGAL, KODE BANK TIDAK DITEMUKAN"
);
	
if(isset($_POST['ajax'])){
	$GW_DB = "GW_PBB_SUKABUMI";
	require_once("inc/payment/comm-central.php");
	require_once("inc/payment/db-payment.php");
	require_once("inc/payment/uuid.php");
	require_once("inc/payment/inc-payment-c.php");
	require_once("inc/payment/inc-payment-db-c.php");
	require_once("inc/payment/json.php");
	
	$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
	if ($iErrCode != 0) {
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}
	
	
	$arr = array('result'=>'<pre>Maaf, format parameter salah!</pre>');
	
	if($_POST['type'] == 'inquiry'){
		if(!empty($_POST['str'])){
			$param = explode(";", $_POST['str']);
			if(count($param) == 2){
				$nop = $param[0];
				$tahun = $param[1];
				$query = sprintf("CALL {$GW_DB}.PUBLIC_INQUIRY_PBB('%s','%s')",$nop, $tahun);
				$res = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
				
				if($res){
					$data = mysqli_fetch_assoc($res);
					$html = "<pre>";
					foreach($data as $key=>$val){
						if($key === "RC"){ 
							$val = "{$val} [$arr_RC[$val]]";
						}
							
						$html.= "{$key} = {$val}<br/>";
					}
					$arr['result'] = $html."</pre>";
				}
			}
			
		}		
	}elseif($_POST['type'] == "payment"){
		if(!empty($_POST['str'])){
			$param = explode(";", $_POST['str']);
			if(count($param) == 4){
				$nop = $param[0];
				$tahun = $param[1];
				$total_bayar = $param[2];
				$kode_bank = $param[3];
				$query = sprintf("CALL {$GW_DB}.PUBLIC_PAYMENT_PBB('%s','%s','%s','%s')",$nop, $tahun, $total_bayar, $kode_bank);
				$res = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
				
				if($res){
					$data = mysqli_fetch_assoc($res);
					$html = "<pre>";
					foreach($data as $key=>$val){
						$html.= "{$key} = {$val}<br/>";
					}
					$arr['result'] = $html."</pre>";
				}
			}			
		}		
	}
	$result = $json->encode($arr);
	echo $result;
	exit;
}
?>
<link href="/inc/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="/inc/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">

<div class="container">
	<form class="form-horizontal col-lg-12">
	  <fieldset>
		<legend><code>TESTING TOOL (update at 07-10-2016 14:05:20)</code></legend>
		<legend>Inquiry</legend>
		<div class="form-group">
		  <label for="inquiry" class="col-lg-2 control-label">Masukan parameter</label>
		  <div class="col-lg-10">
			<div class="input-group">
				<input type="text" id="inquiry" class="form-control">				
				<span class="input-group-btn">
				  <button class="btn btn-default" id="btn-inquiry" type="button">Inquiry</button>
				</span>
			  </div>
			  <p class="text-danger">Parameter NOP; TAHUN</p>
			  <p>contoh : 320406000100020867;2016</p>
		  </div>
		</div>
		<div class="form-group">
		   <div class="col-lg-12 result-inquiry">
		   </div>
		</div>
	  </fieldset>
	</form>
	<form class="form-horizontal col-lg-12">
	  <fieldset>
		<legend>Payment</legend>
		<div class="form-group">
		  <label for="paymemt" class="col-lg-2 control-label">Masukan disini</label>
		  <div class="col-lg-10">
			<div class="input-group">
				<input type="text" id="payment" class="form-control">
				<span class="input-group-btn">
				  <button class="btn btn-default" id="btn-payment" type="button">Cari</button>
				</span>
			  </div>
			  <p class="text-danger">Parameter NOP;TAHUN;TOTAL_BAYAR;KODE_BANK</p>
			  <p>contoh : 320406000100020867;2016;123;3204110</p>
		  </div>
		</div>
		<div class="form-group">
		   <div class="col-lg-12 result-payment">
		   </div>
		</div>
		<ul>
			<?php
			foreach($arr_RC as $key=>$val){
				echo "<li>RC {$key} = {$val}</li>";
			}
			?>
		</ul>
	  </fieldset>
	</form>	
</div>
<script src="/inc/bootstrap/js/jquery.js"></script>
<script src="/inc/bootstrap/js/bootstrap.min.js"></script>
<script>
	$('#btn-inquiry').click(function(){
		var $btn = $(this).button('loading');
		$.ajax({
			type: 'post',
			url : '',
			data : {ajax : 'true', type : 'inquiry', str : $('#inquiry').val()},
			dataType:'json',
			success : function(res){
				$('.result-inquiry').html(res.result);
				$btn.button('reset');
			}
		})
	})
	
	$('#btn-payment').click(function(){
		var $btn = $(this).button('loading');
		$.ajax({
			type: 'post',
			url : '',
			data : {ajax : 'true', type : 'payment', str : $('#payment').val()},
			dataType:'json',
			success : function(res){
				$('.result-payment').html(res.result);
				$btn.button('reset');
			}
		})
	});
</script>
