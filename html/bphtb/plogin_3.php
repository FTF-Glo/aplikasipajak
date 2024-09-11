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
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
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
				//var_dump($id);
				if ($id != null) {
					$arInput[$id] = isset($_REQUEST[$id])?$_REQUEST[$id]:"";
					
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
	if ($login) {
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
	
	header("Location: main.php");
}
?>

<!-- Login Page -->
<style>
	body {
  background: #808080;
    
}

	.button {
  background-color: #4CAF50; /* Green */
  border: none;
  border-radius: 4px;
  color: white;
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

.button1 {
  background-color: white; 
  color: black; 
  border: 2px solid #f62a00;
}

.button1:hover {
  background-color: #ddeb02;
  color: white;
}
.button2 {
  background-color: white; 
  color: black; 
  border: 2px solid #1be02f;
}

.button2:hover {
  background-color: #1a85d6;
  color: white;
}

#button-login{
  font-family: "Roboto", sans-serif;
  text-transform: uppercase;
  outline: 0;
  background: #45aba6;
  width: 100%;
  border: 0;
  padding: 15px;
  color: #FFFFFF;
  font-size: 14px;
  -webkit-transition: all 0.3 ease;
  transition: all 0.3 ease;
  cursor: pointer;
  border-radius: 10px;
}

#button-login:hover,#button-login:active,#button-login:focus {
  background: #2f7471;
}
#pwd, #usr {
  font-family: "Roboto", sans-serif;
  outline: 0;
  background: white;
  width: 100%;
  border: 1px solid #D3D3D3;
  margin: 0 0 15px;
  padding: 15px;
  box-sizing: border-box;
  font-size: 14px;
  border-radius: 5px;
}

table{
margin : 0 auto 0;
}
</style>
<body class='login-page'>

	<!-- Logo header -->
	<div id='header'><span style='font-weight:bold;font-size:20pt'>Pajak Online BPHTB</span><br/>
	</div>
	<!--<img src='<?php echo $MAINstyle["logo"] ?>' id='logo' alt='<?php echo $MAINtitle?>' width="134" height="59"/>-->
	<img src='<?php echo $MAINstyle["logo"] ?>' id='logo' alt='<?php echo $MAINtitle?>' width="134" height="155"/>
	<div id='loader'>
		Memuat Java Applet ....
	</div>
	
	<!-- Content -->
	<div id='content'>
		<div id='login-form' style="display:none">
			<p style="font-size:30px" ><b>Login</b></p>
			<!-- Form -->
			<form action='plogin.php' id='form' method='POST'>
				<input type='hidden' id='login' name='login' value='1'></input>
				<table cellpadding='3' align='center' border='0'>
				
<?php
if (isset($_COOKIE["errorLogin"])) {
	// Error message
	$errorLogin = $_COOKIE["errorLogin"];
	echo "					<tr>\n";
	echo "						<td colspan='3' align='center'><div class='error'>$errorLogin</div></td>\n";
	echo "					</tr>\n";
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
								//$label = $value2["label"];
								$id = $value2["id"];
								if (isset($value2["input"])) {
									$element = $value2["input"];
								}
								$td = "";
								if (isset($value2["td"])) {
									$td = $value2["td"];
								}
								
								// NEW: input dipecah
								$type = isset($value2["type"])?$value2["type"]:"text";
								$autocomplete = isset($value2["autocomplete"])?$value2["autocomplete"]:"off";
								if (isset($value2["maxlength"])) {
									$maxlength = $value2["maxlength"];
								}
								
								echo "					<tr>\n";
								
								if (isset($element)) {
									echo "						<td>$element</td>\n";
								} else {
									$value = "";
									if (isset($_COOKIE["inputLogin" . $id])) {
										if ($autocomplete == 'on') {
											$value = $_COOKIE["inputLogin" . $id];
										}
										
										// delete cookie
										setcookie("inputLogin" . $id, "", time() - 10);
									}
								
									echo "						<td><input type='$type' name='$id' id='$id' value='$value' autocomplete='$autocomplete' ";
									if (isset($maxlength)) {
										echo "maxlength='$maxlength'";
									}
									echo "/></td>\n";
								}
								echo "					</tr>\n";
							}
							echo "					<tr>\n";
							echo "						<td colspan='3' height='5px'></td>\n";
							echo "					</tr>\n";

						}
					}
?>
					<tr>
						<td colspan='3' align='center'><input type='submit' value='Login' id='button-login'></input></td>
					</tr>
<?php
				} else {
?>
					<tr>
						<td colspan='3' align='center'><div class='error'>ERROR: No authenticator installed</div></td>
					</tr>
<?php
				}
?>
					<tr>
						<td colspan='3' height='10px'></td>
					</tr>
					
				</table>
			</form>
		</div>
	</div>
	<div><br><a href='registrasi/registrasi.php'><button type="button" class="button button2"><i class='far fa-address-card'></i>Registrasi User Notaris Baru</button></a>
	<br><!--a href='http://36.92.151.83:8080/portlet/portlet.php' target="_blank"><button type="button" class="button button1"><i class='far fa-address-card'></i> Lihat Daftar Tagihan</button></a> <br/></br>
	<span>Powered By</span><br/>
	<img src='style/default/-id='logo' alt='logo -' width="134" height="59"/-->
	
	
	</div>
	<!--<applet name="jZebra" id="jZebra" code="jzebra.PrintApplet.class" alt="jZebra did not load properly" archive="inc/jzebra/jzebra.jar" width="0" height="0">
         <param name="printer" value="zebra">
      </applet>-->
	  <script type="text/javascript">
	  /*_CEKAPPLET=0;
      function getHWMAC() {
         var applet = document.jZebra;
         if (applet != null) {
            applet.findMAC();
			monitorFindingMAC();
         }else{
			 _CEKAPPLET++;
			 if(_CEKAPPLET<=30){
				window.setTimeout('getHWMAC()', 100);
			 }else{
				alert("Java Applet tidak berjalan semestinya\n Coba Pastikan Java Runtime terinstall dan Applet diijinkan untuk berjalan");
			 }
		 }
         
      }
          
      function monitorFindingMAC() {
		var applet = document.jZebra;
		if (applet != null) {
		   if (!applet.isDoneFindingMAC()) {
			  document.getElementById("loader").innerText="Cek Sistem........";
			  window.setTimeout('monitorFindingMAC()', 100);
		   } else {
			   var listing = applet.getHWMAC();
			   //console.log(listing);
			   document.getElementById("login-form").style.display="inherit";
			   document.getElementById("loader").style.display="none";
			   document.getElementById("mac").value=listing;
		   }
		} else {
				alert("Applet not loaded!");
			}
	 }
      
     getHWMAC();*/
	 	document.getElementById("login-form").style.display="inherit";
		document.getElementById("loader").style.display="none";
		document.getElementById("mac").value=listing;
   </script>
</body>
