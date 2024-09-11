<?php


error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

if(isset($_POST['ajax'])){
	
	require_once("../inc/payment/json.php");
	$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
	
	if($_POST['type'] == 'decode'){
		$arr = array('result'=>'','id'=>'');
		
		$result = base64_decode($_POST['encode']);
		if($result){
			$arr['result'] = $result;
			
			parse_str($result, $out);
			if(isset($out['f'])) 
				$arr['id'] = $out['f'];
			elseif(isset($out['m'])) 
				$arr['id'] = $out['m'];
			
		}
		$result = $json->encode($arr);
		
	}elseif($_POST['type'] == "detail"){
	
		require_once("../inc/payment/comm-central.php");
		require_once("../inc/payment/db-payment.php");
		require_once("../inc/payment/uuid.php");
		require_once("../inc/payment/inc-payment-c.php");
		require_once("../inc/payment/inc-payment-db-c.php");
		
		SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
		if ($iErrCode != 0) {
		  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		  exit(1);
		}
		
		$data = array();
		
		$query = sprintf("SELECT * FROM central_module WHERE CTR_M_ID = '%s'",$_POST['id']);
		$res = mysqli_query($DBLink, $query);
		if(mysqli_num_rows($res)>0){
			$data = mysqli_fetch_assoc($res);
		}
		
		$query = sprintf("SELECT * FROM central_function WHERE CTR_FUNC_ID = '%s'",$_POST['id']);
		$res = mysqli_query($DBLink, $query);
		if(mysqli_num_rows($res)>0){
			$data_function = mysqli_fetch_assoc($res);
			
			if(isset($data['CTR_M_ID'])){
				$data = array($data, $data_function);
			}else{
				$data = $data_function;
			}
		}
		
		$result = "<pre>".print_r($data, true)."</pre>";
		$result.= isset($data['CTR_FUNC_PAGE'])?
		"<br/><pre>function/".$data['CTR_FUNC_PAGE']."</pre>" :
		"<br/><pre>view/".$data['CTR_M_VIEW']."</pre>";
		
	}
	
	echo $result; exit;
}
?>
<!-- Bootstrap core CSS -->
<link href="../inc/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap theme -->
<link href="../inc/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">

<div class="container">
	<form class="form-horizontal col-lg-12">
	  <fieldset>
		<legend><code>DEV TOOL (update at 05-01-2017 11:48:00)</code></legend>
		<div class="form-group">
		  <label for="inputEmail" class="col-lg-2 control-label">Encrypted</label>
		  <div class="col-lg-10">
			<div class="input-group">
				<input type="text" id="encode" class="form-control">
				<span class="input-group-btn">
				  <button class="btn btn-default" id="search-decode" type="button">Decode</button>
				</span>
			  </div>
		  </div>
		</div>
		<div class="form-group">
		   <div class="col-lg-12 result-decode well">
		   </div>
		</div>
	  </fieldset>
	</form>
	<form class="form-horizontal col-lg-12">
	  <fieldset>
		<legend>Search Module or Function</legend>
		<div class="form-group">
		  <label for="inputEmail" class="col-lg-2 control-label">Masukan f or m</label>
		  <div class="col-lg-10">
			<div class="input-group">
				<input type="text" id="id" class="form-control">
				<span class="input-group-btn">
				  <button class="btn btn-default" id="search-detail" type="button">Cari</button>
				</span>
			  </div>
		  </div>
		</div>
		<div class="form-group">
		   <div class="col-lg-12 result-detail well">
		   </div>
		</div>
	  </fieldset>
	</form>	
</div>
<script src="../inc/bootstrap/js/jquery.js"></script>
<script>
	$('#search-decode').click(function(){
		$.ajax({
			type: 'post',
			url : '',
			data : {ajax : 'true', type : 'decode', encode : $('#encode').val()},
			dataType:'json',
			success : function(res){
				$('.result-decode').html(res.result);
				if(res.id != ""){
					$('#id').val(res.id);
					getDetail();
				}
			}
		})
	})
	
	$('#search-detail').click(function(){
		getDetail();
	})
	
	function getDetail(){
		$.ajax({
			type: 'post',
			url : '',
			data : {ajax : 'true', type : 'detail', id : $('#id').val()},
			success : function(res){
				$('.result-detail').html(res);
			}
		})
	}
</script>
