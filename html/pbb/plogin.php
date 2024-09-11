<style>

	body {
		overflow :hidden;
		background-image: linear-gradient(to right bottom, #e6ebd8, #c9e3ce, #a6dbce, #84d0d8, #71c2e4, #6cc1eb, #6ac0f1, #69bff8, #52cffe, #41dfff, #46eefa, #5ffbf1);
		background-attachment: fixed;
		
	}

	.button_regis {

		border: 1px solid black;
		border-radius: 10px;
		color: gray;
		padding: 10px 10px;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-size: 12px;
		margin: 4px 2px;
		-webkit-transition-duration: 0.4s; /* Safari */
		transition-duration: 0.4s;
		cursor: pointer;
	}



	#pwd, #usr {
		font-family: "Roboto", sans-serif;
		outline: 0;
		background: white;
		width: 100%;
		border: 1px solid #D3D3D3;
		/* margin: 0 0 15px; */
		padding: 15px;
		box-sizing: border-box;
		font-size: 14px;
		border-radius: 5px;
	}



	/* coba form baru */

    .grid {
      display: grid;
	  grid-template-columns: 65% 35%; /* Mengatur lebar kolom pertama 70% dan kolom kedua 30% */

     
    }
	.form-login-auth{
		display:flex;
		flex-direction: column;
		gap:10px;
		align-items:center;
		justify-content:center;
		width:100%;
		background: linear-gradient(108deg, rgb(247, 237, 238) 0%, rgb(221, 251, 225) 55%, rgb(202, 202, 202) 100%);
	}
	@media (max-width: 640px) { 
		.form-login-auth {
			padding-top: 50px;
			justify-content:unset;
		}
	}

    .item {
      background-color: #f0f0f0;
      border: 1px solid #ddd;
      /* padding: 20px; */
      text-align: center;
	  height:100vh;
	 
    }

	.item .logoo {
      max-width: 100%;
      height: 100%;
    }

	@media (max-width: 1300px) {
		.img-auth {
			display:none;	
		}

		.grid {
	  		grid-template-columns: 100%; 
   		}
	}

	.login-form-a {
      display: flex;
      flex-direction: column;
      gap: 10px;
	  margin: 10;
	  width:70%
    }
	@media (max-width: 640px) { 
		.login-form-a {
			width:90%
		}
	}
    .login-form-a input {
      padding: 10px;
      font-size: 16px;
    }
    .login-form-a button {
      padding: 10px;
      font-size: 16px;
      background-color: #007bff;
      color: white;
      border: none;
      cursor: pointer;
    }

	.button {
		display: inline-block;
		padding: 10px 20px;
		font-size: 16px;
		font-weight: bold;
		text-align: center;
		text-decoration: none;
		color: #fff;
		background-color: #007bff;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		transition: background-color 0.3s;
	}

	.button_regis:hover {
		color:white; background: rgb(230,223,255);background: linear-gradient(22deg, rgba(10, 0, 36, 1) 21%, rgba(0, 223, 198, 1) 73%);
		}
	.btn-login {
		margin-top:15px;
		background:linear-gradient(22deg, rgb(77, 47, 45) 21%, rgb(43, 130, 80) 73%); 
		border-radius:6px;
	}

	.swal2-title{
		border :unset;
		margin-left :-30px;
		
	}
	.swal2-confirm {

		color:white; background: rgb(230,223,255);background: linear-gradient(22deg, rgba(10, 0, 36, 1) 21%, rgba(0, 223, 198, 1) 73%) !important;
	}

</style>

<?php
// Include file
require_once("inc/central/setting-central.php");
require_once("inc/auth/AuthBase.php");
require_once("inc/payment/db-payment.php");
require_once("inc/payment/inc-payment-db-c.php");
require_once("inc/payment/inc-dms-c.php");
date_default_timezone_set('Asia/Jakarta');

// Database
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	exit(1);
}
$Setting = new SCANCentralSetting(DEBUG, LOG_DMS_FILENAME, $DBLink);

// == Custom Auth ==
$arAuth = $Setting->GetAllAuth();

// get default auth, if no authenticator is active
if ($arAuth == null) {
	$auth = $Setting->GetAuth(1);
	if ($auth != null) {
		$arAuth[0] = $auth;
	}
}

// var_dump($arAuth);

$browser = $_SERVER['HTTP_USER_AGENT'];
if($browser=='') die;
$tgglx = date('Ymd');
$sha1browser = strtoupper(sha1($browser.$tgglx.$browser)) . substr(uniqid(),-5);
$inputJam = date('H:i:s');
$sha1Jam = sha1($browser.$inputJam);

// Login
if (isset($_REQUEST["login"])) {

	$browserkey = isset($_POST['needkey']) ? $_POST['needkey'] : false;
	$needjam = isset($_POST['needjam']) ? $_POST['needjam'] : false;
	$jamkey = isset($_POST['jamkey']) ? $_POST['jamkey'] : false;

	if(!$browserkey || !$needjam || !$jamkey) die;

	$browserkey = substr($browserkey,0,40);
	if(substr($sha1browser,0,40)!==$browserkey) die;

	$jamkey2 = sha1($browser.$needjam);

	if($jamkey!==$jamkey2) die;

	$xdatex = date('Y-m-d') . ' ' . $needjam;
	$ydatey = date('Y-m-d H:i:s');

	$xdatexx = new DateTime($xdatex);
	$ydateyy = new DateTime($ydatey);

	$diffInSeconds = $ydateyy->getTimestamp() - $xdatexx->getTimestamp();
	if($diffInSeconds>30) { header("Location: main.php"); exit; }

	$arDataLogin = array();
	$login = true;
	$arInput = array();
	$arTempCookie = array();
	foreach ($arAuth as $key => $value) {
		// class name
		$className = $value["class"];
		$fileName = "inc/auth/" . $className . ".php";

		// exist?
		if (file_exists($fileName)) {
			include_once($fileName);

			$authClass = new $className();

			// get render input
			$input = $authClass->element();

			foreach ($input as $key2 => $value2) {
				$id = $value2["id"];
				if ($id != null) {
					$arInput[$id] = isset($_REQUEST[$id]) ? $_REQUEST[$id] : "";

					// NEW: set to cookie, for later display
					setcookie("inputLogin" . $id, $_REQUEST[$id], time() + 36000);
					$arTempCookie["inputLogin" . $id] = $_REQUEST[$id];
				}
			}
		}
	}

	foreach ($arAuth as $key => $value) {
		// class name
		$className = $value["class"];
		$fileName = "inc/auth/" . $className . ".php";

		// exist?
		if (file_exists($fileName)) {
			include_once($fileName);

			$authClass = new $className();

			if (!$login) {
				continue;
			}

			// auth
			$arResponse = array();
			$bOK = $authClass->auth($arInput, $arResponse);

			if (!$bOK) {
				// login failed, display error message
				// echo "error = " . $arResponse["error"];
				setcookie("errorLogin", $arResponse["error"], time() + 36000);
				$login = false;
			} else {
				// login succeed, gather response
				if ($arResponse != null) {
					foreach ($arResponse as $key2 => $value2) {
						if (isset($arDataLogin[$key2])) {
							// attempt to re-write a previous response, error
							// echo "error = " . $arDataLogin["error"];
							$arDataLogin["error"] = "Authentication error";
							$login = false;
						}

						// write response
						$arDataLogin[$key2] = $value2;
					}
				}
			}
		}
	}
	//var_dump($arDataLogin);
	if ($login) {
		// var_dump($arDataLogin);

		// store login information
		$jsonResp = $json->encode($arDataLogin);
		$cData = base64_encode($jsonResp);

		// set cookie
		setcookie("centraldata", $cData, time() + 3600);

		// NEW: auto-load
		$uid = $arDataLogin["uid"];
		$autoLoad = $User->GetAutoLoad($uid);
		//var_dump($autoLoad);

		// NEW: delete previous info login cookie
		foreach ($arTempCookie as $key => $value) {
			setcookie($key, "", time() - 10);
		}

		if ($autoLoad != null) {
			$ar = explode(",", $autoLoad);

			$granted = false;
			$count = count($ar);
			$appId = $ar[0];
			if ($count > 1) {
				$moduleId = $ar[1];
				//echo "$appId,$moduleId";
				$url64 = base64_encode("a=$appId&m=$moduleId");
				header("Location: main.php?param=$url64");
				die();
			} else {
				$url64 = base64_encode("a=$appId");
				header("Location: main.php?param=$url64");
				die();
			}
		}
	}

	header("Location: main.php");
}
?>

<body class='login-page' >
	<div id='content'>
		<div class="grid">
			<div class="item img-auth">
				<img class="logoo" src="image/bg_pesibar.jpg" alt="pesibar">
			</div>
			<div class="item form-login-auth">
				<form class="login-form-a" action='plogin.php' id='form' method='POST'>
					<input type='hidden' id='login' name='login' value='1'/>
					<div class="text-center">
						<img src="<?php echo $MAINstyle["logo"] ?>" id='logo' alt='PBB-P2 Pesibar' width="70" height="90">
					</div>
					<p style="color:blue;font-size:25px;margin-bottom:0"><?=renderTitle()?><br>Kabupaten Pesisir Barat</p>
					
					<?php
						if (isset($_COOKIE["errorLogin"])) {
							// Error message
							$errorLogin = $_COOKIE["errorLogin"];
						?>
							<div class="alert alert-danger">
								<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
								<p><?php echo $errorLogin; ?></p>
							</div>
							<?php
							setcookie("errorLogin", "", time() - 10);
						}

						// get default auth, if no authenticator is active
						if ($arAuth == null) {
							$auth = $Setting->GetAuth(1);
							if ($auth != null) {
								$arAuth[0] = $auth;
							}
						}

						if ($arAuth != null) {
							foreach ($arAuth as $auth) {
								// class name
								$className = $auth["class"];
								$fileName = "inc/auth/" . $className . ".php";

								// exist?
								if (file_exists($fileName)) {
									include($fileName);

									$authClass = new $className();

									// get render input
									$input = $authClass->element();
									//var_dump($_REQUEST);
									foreach ($input as $key2 => $value2) {
										$label = $value2["label"];
										$id = $value2["id"];
										if (isset($value2["input"])) {
											$element = $value2["input"];
										}
										$td = "";
										if (isset($value2["td"])) {
											$td = $value2["td"];
										}

										// NEW: input dipecah
										$type = isset($value2["type"]) ? $value2["type"] : "text";
										$autocomplete = isset($value2["autocomplete"]) ? $value2["autocomplete"] : "off";
										if (isset($value2["maxlength"])) {
											$maxlength = $value2["maxlength"];
										}

										if ($label == "LOGIN") {
							?>
											<div class="text-center">
												<label for="Username" style="text-align:left; font-size:20px"><?=$label?></label>
											</div>
											<?php
											if (isset($element)) {
												echo $element;
											} else {
												$value = "";
												if (isset($_COOKIE["inputLogin" . $id])) {
													if ($autocomplete == 'on') {
														$value = $_COOKIE["inputLogin" . $id];
													}

													// delete cookie
													setcookie("inputLogin" . $id, "", time() - 10);
												}
											?>
												<input type="<?=$type?>" name="<?=$id?>" id="<?=$id?>" value="<?=$value?>" autocomplete="<?=$autocomplete?>" <?php echo isset($maxlength) ? $maxlength : ''; ?>>
											<?php
											}
											?>
										<?php
										} else {
										?>
												<label for="<?=$id?>" style="text-align:left;font-size:17px"><?=$label?></label>
												<?php
												if (isset($element)) {
													echo $element;
												} else {
													$value = "";
													if (isset($_COOKIE["inputLogin" . $id])) {
														if ($autocomplete == 'on') {
															$value = $_COOKIE["inputLogin" . $id];
														}

														// delete cookie
														setcookie("inputLogin" . $id, "", time() - 10);
													}
												?>
													<input type="<?=$type?>" name="<?=$id?>" id="<?=$id?>" value="<?=$value?>" autocomplete="<?=$autocomplete?>" <?php echo isset($maxlength) ? $maxlength : ''; ?>  placeholder="<?=$label?>" required>
												<?php
												}
												?>
							<?php
										}
									}
								}
							}
							?>
							<button type="submit" class="btn btn-block btn-login">Login</button>
							<input type="hidden" name="needkey" value="<?=$sha1browser?>" />
							<input type="hidden" name="needjam" value="<?=$inputJam?>" />
							<input type="hidden" name="jamkey" value="<?=$sha1Jam?>" />
						<?php
						} else {
						?>
							<div class="alert alert-danger alert-danger-form">
								<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
								<p>ERROR: No authenticator installed</p>
							</div>
						<?php
						}
					?>

					<div style="margin:15px 0 10px 0">
						<a href='/portlet/portlet.php' target="_blank">
							<button type="button" class="btn btn-block"><i class='fa fa-address-card'></i> Lihat Daftar Tagihan</button>
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		document.getElementById("login-form").style.display="inherit";
		document.getElementById("loader").style.display="none";
		document.getElementById("mac").value=listing;
	</script>
	  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
	  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
	function alertError(title,msgerror,icon){
		Swal.fire({
			title: title,
			text: msgerror,
			icon:icon
		});
	}
	</script>
</body>