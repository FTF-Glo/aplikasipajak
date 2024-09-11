<style>
	body {
		overflow: hidden;
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
		-webkit-transition-duration: 0.4s;
		/* Safari */
		transition-duration: 0.4s;
		cursor: pointer;
	}



	#pwd,
	#usr {
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
		grid-template-columns: 65% 35%;
		/* Mengatur lebar kolom pertama 70% dan kolom kedua 30% */


	}

	.form-login-auth {

		display: flex;
		flex-direction: column;
		gap: 10px;
		align-items: center;
		justify-content: center;
		width: 100%;
	}

	.item {
		background-color: #f0f0f0;
		border: 1px solid #ddd;
		/* padding: 20px; */
		text-align: center;
		height: 100vh;

	}

	.item .logoo {
		max-width: 100%;
		height: 100%;
	}

	@media (max-width: 1300px) {
		.img-auth {
			display: none;
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
		color: white;
		background: rgb(230, 223, 255);
		background: linear-gradient(22deg, rgba(10, 0, 36, 1) 21%, rgba(0, 223, 198, 1) 73%);
	}

	.btn-login {
		background: rgb(230, 223, 255);
		background: linear-gradient(22deg, rgba(10, 0, 36, 1) 21%, rgba(0, 223, 198, 1) 73%);
		border-radius: 10px;
	}

	.swal2-title {
		border: unset;
		margin-left: -30px;

	}

	.swal2-confirm {

		color: white;
		background: rgb(230, 223, 255);
		background: linear-gradient(22deg, rgba(10, 0, 36, 1) 21%, rgba(0, 223, 198, 1) 73%) !important;
	}
</style>

<?php

// Include file
require_once("inc/central/setting-central.php");
require_once("inc/auth/AuthBase.php");
require_once("inc/payment/db-payment.php");
require_once("inc/payment/inc-payment-db-c.php");
require_once("inc/payment/inc-dms-c.php");

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

//var_dump($_SESSION['_success'], $_SESSION['usertampil'], $_SESSION['passtampil']);
//var_dump($arAuth);exit();

// Login
if (isset($_REQUEST["login"])) {

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
		setcookie("centraldata", $cData, time() + 36000);

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

	// header("Location: main.php?param=YT1hUGF0ZGEmbT1tSG9tZQ==");
	// header("Location: view/PATDA-V1/home/index.php");

	header("Location: main.php?");
}
?>

<!-- Login Page -->

<body class='login-page'>

	<!-- Content -->
	<div id='content'>

		<div class="grid">

			<div class="item img-auth">
				<img class="logoo" src="image/pemda.jpg" alt="pesibar">
			</div>
			<div class="item form-login-auth">
				<form class="login-form-a" style="width:70%" action='plogin.php' id='form' method='POST'>
					<input type='hidden' id='login' name='login' value='1'></input>
					<script>
						;
					</script>



					<div class="">
						<img src="<?php echo $MAINstyle["logo"] ?>" id='logo' alt='<?php echo $MAINtitle ?>' width="70" height="90">
					</div>
					<!--<p style="color:blue;font-size:25px">9Pajak</p>-->
					<p style="font-size: 20px; margin-bottom: 20px">Selamat Datang Di Aplikasi <br />BPHTB<br> Kabupaten Pesisir Barat </p>

					<?php if (isset($_SESSION['suc1'])) : ?>
						<span style="color:green"><b>
								<?= $_SESSION['suc1']; ?></b>
						</span>
						<br>
						<br>
					<?php endif;
					unset($_SESSION["suc1"]);
					?>


					<?php if (isset($_SESSION['suc2'])) : ?>
						<span style="color:green"><b>
								<?= $_SESSION['suc2']; ?></b>
						</span>
						<br>
						<br>
					<?php endif;
					unset($_SESSION["suc2"]);
					?>

					<?php if (isset($_SESSION['usertampil'])) : ?>
						<span style="color:red">
							Data User Wajib Pajak Berhasil Disimpan.<br>
							Tunggu Sampai Akun Anda di verifikasi dan Aktif <br><br>
							Catat Username dan Password yang dibuat sebelum nya untuk Login <br>
							<b>Username : <?= $_SESSION['usertampil']; ?> </b>
							<br>
							<b>Password : <?= $_SESSION['passtampil']; ?></b>
							<br>
							<br>
						</span>
					<?php endif;
					unset($_SESSION["usertampil"]);
					unset($_SESSION["passtampil"]);

					if (isset($_COOKIE["errorLogin"])) {
						// Error message
						$errorLogin = $_COOKIE["errorLogin"];
						// var_dump($);die;
						// echo "					<tr>\n";
						// echo "						<td colspan='3' align='center'><div class='error'>$errorLogin</div></td>\n";
						// echo "					</tr>\n";
						// setcookie("errorLogin", "", time() - 10);



						// Hapus cookie errorLogin
						setcookie("errorLogin", "", time() - 10);
					}
					?>

					<label for="Username" style="text-align:left; font-size:17px"> Username</label>
					<input type="text" id="usr" name="usr" autocomplete="on" placeholder="Username" required>
					<label for="Password" style="text-align:left; font-size:17px"> Password</label>
					<input type="password" name="pwd" id="pwd" autocomplete="off" placeholder="Password" required>
					<button type="submit" class="btn-login">Login</button>
					<p>Belum Memiliki Akun ? </p>
					<a href="registrasi/registrasi.php" class="button_regis" style=""><i class="far fa-address-card"></i>Registrasi User Wajib Pajak</a>

				</form>
			</div>
		</div>




	</div>
	<script type="text/javascript">
		//  getHWMAC();*/
		document.getElementById("login-form").style.display = "inherit";
		document.getElementById("loader").style.display = "none";
		document.getElementById("mac").value = listing;
	</script>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		function alertError(title, msgerror, icon) {
			Swal.fire({
				title: title,
				text: msgerror,
				icon: icon
			});
		}
		<?php
		$errorLogin = $_COOKIE["errorLogin"];
		if (isset($_COOKIE["errorLogin"])) : ?>
			alertError('Error Login', `<?= $errorLogin ?>`, 'error');
		<?php endif; ?>
	</script>


</body>