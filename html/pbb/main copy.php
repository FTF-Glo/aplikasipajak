<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_start();
/* === PHP environment settings === */
set_time_limit(0);
// date_default_timezone_set("Asia/Jakarta");
ob_start();
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');
/* === File includes === */
// $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'pc'.DIRECTORY_SEPARATOR.'svr'.DIRECTORY_SEPARATOR.'central', '', dirname(__FILE__))).'/';
$sRootPath = "";
require_once("inc/payment/constant.php");
require_once("inc/payment/db-payment.php");
require_once("inc/payment/ctools.php");
require_once("inc/payment/json.php");
require_once("inc/payment/inc-dms-c.php");
require_once("inc/payment/inc-payment-c.php");
require_once("inc/payment/inc-payment-db-c.php");
require_once("inc/central/session-central.php");
require_once("inc/central/user-central.php");
require_once("inc/central/setting-central.php");
require_once("inc/central/dbspec-central.php");
// NEW: for header & footer
require_once("headerFooter.php");

/* === Start time === */
$MAINstartRender = microtime(1);
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)) {
	$iStart = microtime(true);
}

/* === Global variables === */
$activeTime = ONPAYS_SESSION_INTERVAL; // seconds of active time before expired...
$errorLogin = "";
$iCentralTS = time();
$iErrCode = 0;
$sErrMsg = '';
$sResponse = '';
$DBLink = NULL;
$DBConn = NULL;
// Module
$userModuleView = null;
$moduleOnly1 = "";
$grantedModule = false;
// Function
$userFuncPage = null;
$MAINparam = null;
// Style path
$MAINtitle = null;
$MAINsubTitle = null;
$MAINstylePath = null;
$MAINfooterText = null; 
$MAINstyle = null;

/* === Database Central Connection === */
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	exit(1);
}

$Session = new SCANCentralDBSession(DEBUG, LOG_DMS_FILENAME, $DBLink, $activeTime);
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$Setting = new SCANCentralSetting(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

/* === Get cookie data === */
$cData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
$data = null;
if (!isEmpty($cData)) {
	$decData = base64_decode($cData);
	if ($decData) {
		$data = $json->decode($decData);
	}
}

/* === Parameter read === */
// NEW: parameters are able called directly with $<key>
$a = "";
$m = "";
$f = "";
$p = "";
// Management
$setting = "";
$m = "";
$sm = "";
$a = "";
$i = "";
// NEW: profile
$userProfile = "";
$param = (isset($_REQUEST['param']) ? trim($_REQUEST['param']) : '');
if ($param != "") {
	// NEW: parameter base64
	$decParam = base64_decode($param);
	$arParam = explode("&", $decParam);

	foreach ($arParam as $iParam) {
		$indexEqual = strpos($iParam, "=");

		$MAINkey = substr($iParam, 0, $indexEqual);
		$MAINvalue = substr($iParam, $indexEqual + 1);

		// NEW: masukkan ke request
		$_REQUEST[$MAINkey] = $MAINvalue;
	}
	// print_r($_REQUEST);
} else {
	// aldes set default param is PBB - Dashboard
	$_REQUEST['param'] = 'YT1hUEJCJm09bURhc2hib2FyZFBCQg==';
	$_REQUEST['a'] = 'aPBB';
	$_REQUEST['m'] = 'mDashboardPBB';
}
// Take all parameter to variables
foreach ($_REQUEST as $MAINkey => $MAINvalue) {
	$$MAINkey = $MAINvalue;
}

// Parameter
$application = $a;
$area = $a;
$module = $m;
$function = $f;
$mode = $m;
$subMode = $sm;

// Preserve backward compatibility
if (!isset($action)) {
	$action = $a;
}
if (!isset($ppid)) {
	$ppid = $p;
}
if (!isset($id)) {
	$id = $i;
}

/* === Local Function === */
// Check emptiness of a string variable
function isEmpty($st)
{
	return (strlen(trim($st)) == 0);
}

// Check admin privilege from a user
function isAdmin($uid)
{
	global $User;

	return $User->IsAdmin($uid);
}

// Check supervisor privilege from a user
function isSupervisor($uid)
{
	global $User;

	return $User->IsSupervisor($uid);
}

// Check session data
function stillInSession()
{
	global $Session, $cData, $data, $activeTime, $errorLogin;
	$inSession = -2;

	if ($data) {
		$uid = $data->uid;
		$sid = $data->session;
		// var_dump($uid, $sid);
		$inSession = $Session->CheckSession($uid, $sid);
		// $inSession = -1;
		if ($inSession == 0) {
			// update session
			$Session->UpdateSessionInDB($uid, $sid);
			setcookie("centraldata", $cData, time() + $activeTime);
		} else if ($inSession == -1) {
			// expired session
			$Session->DeleteSessionFromDB($uid, $sid);

			// delete data & cookies
			setcookie("centraldata", "", time() - 10);
			$data = null;

			// set error message
			// $errorLogin = "The session has expired. Please login again.";
			setcookie("errorLogin", "The session has expired. Please login again.", time() + $activeTime);
			header("Location: main.php");
		} else {
			if (isset($cData)) {
				// pre-empted by other login

				// delete data & cookies
				setcookie("centraldata", "", time() - 10);
				$data = null;

				$errorLogin = "Forced login from other computer";

				setcookie("errorLogin", "The session has expired. Please login again.", time() + $activeTime);
				header("Location: main.php");
			}
		}
	}

	return ($inSession == 0);
}

// Print user, app, and module menu
function printBody()
{
	global $application, $module, $function, $MAINstyle, $setting, $data, $mode, $subMode, $id, $userProfile;
	
	// NEW: render header from external file
	renderHeader();
	

	if ($setting) {
		// SETTING
		echo '<!-- sidebar menu: : style can be found in sidebar.less -->
		<ul class="sidebar-menu" data-widget="tree">
			<!--li class="header">NAVIGATION</li-->
			<li>
				<a href="main.php">
					<i class="fa fa-dashboard"></i> <span>Halaman Utama</span>
				</a>
		  	</li>';

		$uid = $data->uid;
		if (isAdmin($uid)) {
			if ($mode) {
				$url64 = base64_encode("setting=1");
				echo '<li>
					<a href="main.php?param=' . $url64 . '">
						<i class="fa fa-users"></i> <span>Management</span>
					</a>
				</li>';
				if ($mode == "s") {
					$url64 = base64_encode("setting=1&m=s");
					echo '<li>
						<a href="main.php?param=' . $url64 . '">
							<i class="fa fa-gear"></i> <span>Setting</span>
						</a>
					</li>';
				} else if ($mode == "a") {
					if (substr($subMode, 1, 1) == "c") {
						$url64 = base64_encode("setting=1&m=a");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-file-text"></i> <span>Application</span>
							</a>
						</li>';

						$url64 = base64_encode("setting=1&m=a&sm=lc&i=$id");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-file-text-o"></i> <span>Configuration</span>
							</a>
						</li>';
					} else {
						$url64 = base64_encode("setting=1&m=a");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-file-text"></i> <span>Application</span>
							</a>
						</li>';
					}
				} else if ($mode == "m") {
					if (substr($subMode, 1, 1) == "c") {
						$url64 = base64_encode("setting=1&m=m");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-book"></i> <span>Module</span>
							</a>
						</li>';

						$url64 = base64_encode("setting=1&m=m&sm=lc&i=$id");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-file-text-o"></i> <span>Configuration</span>
							</a>
						</li>';
					} else {
						$url64 = base64_encode("setting=1&m=m");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-book"></i> <span>Module</span>
							</a>
						</li>';
					}
				} else if ($mode == "l") {
					if (substr($subMode, 1, 1) == "k") {
						$url64 = base64_encode("setting=1&m=l");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-book"></i> <span>PP Module</span>
							</a>
						</li>';

						$url64 = base64_encode("setting=1&m=l&sm=lk");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-key"></i> <span>Key</span>
							</a>
						</li>';
					} else {
						$url64 = base64_encode("setting=1&m=l");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-book"></i> <span>PP Module</span>
							</a>
						</li>';
					}
				} else if ($mode == "u") {
					$url64 = base64_encode("setting=1&m=u");
					echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-user"></i> <span>User</span>
							</a>
						</li>';
				} else if ($mode == "d") {
					if (substr($subMode, 1, 1) == "c") {
						$url64 = base64_encode("setting=1&m=d");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-database"></i> <span>Database</span>
							</a>
						</li>';

						$url64 = base64_encode("setting=1&m=d&sm=lc&i=$id");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-file-text-o"></i> <span>Configuration</span>
							</a>
						</li>';
					} else {
						$url64 = base64_encode("setting=1&m=d");

						echo '<li>
							<a href="main.php?param=' . $url64 . '">
								<i class="fa fa-database"></i> <span>Database</span>
							</a>
						</li>';
					}
				} else if ($mode == "r") {
					$url64 = base64_encode("setting=1&m=r");

					echo '<li>
						<a href="main.php?param=' . $url64 . '">
							<i class="fa fa-bookmark"></i> <span>Role</span>
						</a>
					</li>';
				} else if ($mode == "f") {
					$url64 = base64_encode("setting=1&m=f");

					echo '<li>
						<a href="main.php?param=' . $url64 . '">
							<i class="fa fa-code-o"></i> <span>Function</span>
						</a>
					</li>';
				} else if ($mode == "t") {
					$url64 = base64_encode("setting=1&m=t");

					echo '<li>
						<a href="main.php?param=' . $url64 . '">
							<i class="fa fa-gear"></i> <span>Auth</span>
						</a>
					</li>';
				} else if ($mode == "h") {
					$url64 = base64_encode("setting=1&m=h");

					echo '<li>
						<a href="main.php?param=' . $url64 . '">
							<i class="fa fa-question-circle-o"></i> <span>Help</span>
						</a>
					</li>';
				}
			} else {
				$url64 = base64_encode("setting=1");

				echo '<li>
					<a href="main.php?param=' . $url64 . '">
						<i class="fa fa-users"></i> <span>Management</span>
					</a>
				</li>';
			}

			// NEW: Supervisor
		} else if (isSupervisor($uid)) {
			if ($mode) {
				$url64 = base64_encode("setting=1");

				echo '<li>
					<a href="main.php?param=' . $url64 . '">
						<i class="fa fa-users"></i> <span>Management</span>
					</a>
				</li>';
				if ($mode == "u") {
					$url64 = base64_encode("setting=1&m=u");

					echo '<li>
						<a href="main.php?param=' . $url64 . '">
							<i class="fa fa-user"></i> <span>User</span>
						</a>
					</li>';
				} else if ($mode == "h") {
					$url64 = base64_encode("setting=1&m=h");

					echo '<li>
						<a href="main.php?param=' . $url64 . '">
							<i class="fa fa-question-circle-o"></i> <span>Help</span>
						</a>
					</li>';
				}
			} else {
				$url64 = base64_encode("setting=1");

				echo '<li>
					<a href="main.php?param=' . $url64 . '">
						<i class="fa fa-users"></i> <span>Management</span>
					</a>
				</li>';
			}
		} else {
			echo "<li><div class='error'>Illegal access!</div></li>";
		}

		echo '</ul>
			</section>
			<!-- /.sidebar -->
		</aside>';
	} else if ($userProfile == "1") {
		// NEW: Do nothing :|
		// Any body content will be printed in below
		// var_dump($function,$setting,isAdmin($uid),$mode, $userProfile);exit();
		
		echo '</ul>
			</section>
			<!-- /.sidebar -->
		</aside>';

	} else {
		// Menu application and module
		$bOK = printAppAndModule();
		if (!$bOK) {
			return 's';
		}
	}

	/*echo '
		<!-- =============================================== -->
	
		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			<!-- Content Header (Page header) -->
			<section class="content-header">
				<?php echo $maintitle;?>
				<ol class="breadcrumb">
					<?php echo $breadcrumbs;?>
				</ol>
			</section>
			<!-- Main content -->
			<section class="content">';*/

	// Loading text
	echo "<div id='loadingText' style='display:none; padding:20px'>Loading... &nbsp;<img src='image/icon/wait.gif' alt='..'></img></div>";
}



// Print user info, logout, and management link (admin only)
function printAppAndModule()
{
	global $data, $User, $application, $module, $grantedModule, $userModuleView, $moduleOnly1, $MAINstyle;
	$grantedApp = false;

	
	if ($data) {
		$uid = $data->uid;
		// App
		if (!isEmpty($application) && $User->IsAppGranted($uid, $application)) {
			$appName = $User->GetAppName($application);
			$grantedApp = true;

			// NEW: to render
			$appName = $appName;
		} else {
			$appName = "-";
		}

		// Module
		if (!isEmpty($module) && $User->IsModuleGranted($uid, $application, $module)) {
			$moduleName = $User->GetModuleName($module);
			$grantedModule = true;
		} else {
			$moduleName = "-";
		}

		// Option app
		$arApp = null;
		$arModule = null;
		$bOK = $User->GetApp($uid, $appIds);
		if ($bOK && $appIds != null) {
			if (count($appIds) == 1) {
				if ($application == "") {
					// Redirect ke app
					$appId = $appIds[0]["id"];
					$url64 = base64_encode("a=$appId");

					header("Location: main.php?param=$url64");
					// echo "	<meta http-equiv='REFRESH' content='0;url=main.php?param=$url64'>";
				}
			}

			// to render
			$arApp = $appIds;
			$arModule = array();

			foreach ($appIds as $appI) {
				$bOK = $User->GetModuleInApp($uid, $appI["id"], $moduleIds);
				if ($bOK && $moduleIds != null) {
					$nModule = 0;

					// to render
					$i = 0;
					$arModule[$appI["id"]] = array();

					foreach ($moduleIds as $moduleId) {
						$id = $moduleId["id"];
						$name = $moduleId["name"];
						$submenu = $moduleId["submenu"];
						$icon = $moduleId["icon"];
						if ($User->IsModuleGranted($uid, $appI["id"], $id)) {
							if ($nModule == 0) {
								$moduleOnly1 = $id;
							}
							$nModule++;

							// to render
							$arModule[$appI["id"]][$i]["id"] = $id;
							$arModule[$appI["id"]][$i]["name"] = $name;
							$arModule[$appI["id"]][$i]["submenu"] = $submenu;
							$arModule[$appI["id"]][$i]["icon"] = $icon;
							$i++;
						}
					}

					if ($nModule == 1 && $module != $moduleOnly1) {
						// Redirect ke module
						$url64 = base64_encode("a=$application&m=$moduleOnly1");
						// echo "	<meta http-equiv='REFRESH' content='0;url=main.php?param=$url64'>";
						header("Location: main.php?param=$url64");
					}
				}
			}
		}

		// Option module
		/*$arModule = null;
		$bOK = $User->GetModuleInApp($uid, $application, $moduleIds);
		if ($bOK && $moduleIds != null) {
			$nModule = 0;

			// to render
			$i = 0;
			$arModule = array();

			foreach ($moduleIds as $moduleId) {
				$id = $moduleId["id"];
				$name = $moduleId["name"];
				if ($User->IsModuleGranted($uid, $application, $id)) {
					if ($nModule == 0) {
						$moduleOnly1 = $id;
					}
					$nModule++;

					// to render
					$arModule[$i]["id"] = $id;
					$arModule[$i]["name"] = $name;
					$i++;
				}
			}

			if ($nModule == 1 && $module != $moduleOnly1) {
				// Redirect ke module
				$url64 = base64_encode("a=$application&m=$moduleOnly1");
				// echo "	<meta http-equiv='REFRESH' content='0;url=main.php?param=$url64'>";
				header("Location: main.php?param=$url64");
			}
		}*/

		// Get View module page
		if ($grantedModule) {
			$userModuleView = $User->GetModuleView($module);
			//var_dump($userModuleView);
			$userModuleView = "view/" . $userModuleView;
		}

		// Get render menu
		$renderMenu = $MAINstyle["renderMenu"];
		include($renderMenu);

		//echo "		";
		renderMenu($application, $appName, $arApp, $module, $moduleName, $arModule);
	}
	return ($grantedApp && $grantedModule);
}

// Get function page filename
function getFunctionPage()
{
	global $data, $User, $application, $function, $userFuncPage;
	$bOK = false;

	if ($data) {
		$uid = $data->uid;
		$arFunc = array();

		if ($User->GetFunctionName($function, $arFunc)) {
			$module = $arFunc["CTR_FUNC_MID"];
			$grantedFunc = $User->IsFunctionGranted($uid, $application, $module, $function);

			if ($grantedFunc) {
				// $funcName = $arFunc["CTR_FUNC_NAME"];
				$funcPage = $arFunc["CTR_FUNC_PAGE"];
				$funcPage = "function/" . trim($funcPage);

				if (!isEmpty($funcPage) && file_exists($funcPage)) {
					$bOK = true;
					$userFuncPage = $funcPage;
				}
			}
		}
	}

	return $bOK;
}

// Print image menu
function printOption($arParam = null)
{
	global $arFunction;

	if ($arFunction == null) {
		echo ".";
		return;
	}

	$first = true;
	$result = "";
	foreach ($arFunction as $iLink) {
		$urlParam = $iLink["urlParam"];
		$image = $iLink["image"];
		$name = $iLink["name"];

		if ($arParam != null) {
			foreach ($arParam as $iKey => $iValue) {
				$urlParam .= "&" . $iKey . "=" . $iValue;
			}
		}

		if ($first) {
			echo "&nbsp;";
			$first = false;
		}

		$url64 = base64_encode($urlParam);
		$result .= "<a href='main.php?param=$url64'><img class='imageHref' src='$image' title='$name' alt='$name' border='0' /></a>&nbsp;&nbsp;";
	}

	return $result;
}

function readSetting()
{
	global $User, $Setting, $data, $MAINtitle, $MAINfooterText, $MAINstyle;

	/*
	// DEPRECATED: PauL - 14102010
	// Read setting
	$arSetting = $Setting->GetSettingDetail();
	if ($arSetting != null) {
		$MAINtitle = $arSetting["title"];
		$MAINfooterText = $arSetting["footer"];
	}
	
	// Use default
	if ($MAINtitle == null || trim($MAINtitle) == "") {
		$MAINtitle = DEFAULT_TITLE;
		$MAINfooterText = DEFAULT_FOOTER_TEXT;
	}
	*/

	// NEW: render footer from external file
	$MAINfooterText = renderFooter();
	// NEW: render title also
	$MAINtitle = renderTitle();

	// Read style
	$styleFolder = null;
	if ($data != null) {
		$uid = $data->uid;
		$styleFolder = $User->GetLayoutUser($uid);
	}
	if ($styleFolder == null) {
		$styleFolder = "default";
	}
	$useDefault = false;
	if ($styleFolder == null) {
		// Style not set, use default
		$useDefault = true;
	} else {
		if (!file_exists("style/$styleFolder")) {
			// Style path not exist, use default
			$useDefault = true;
		}
	}

	// Parse XML
	$arDefault = null;
	if (!$useDefault) {
		$MAINstyle = $User->GetLayoutFiles($styleFolder);
		$arDefault = $User->GetLayoutFiles(DEFAULT_STYLE_PATH);
	}

	// No XML Declaration found
	if ($useDefault || $MAINstyle == null) {
		$MAINstyle = $User->GetLayoutFiles(DEFAULT_STYLE_PATH);

		$styleFolder = DEFAULT_STYLE_PATH;
	}

	// Add style path to all component
	foreach ($MAINstyle as $iKey => $iValue) {
		$MAINstyle[$iKey] = "style/$styleFolder/$iValue";
	}
	

	// Check existence of style component
	// If not exist, use default
	if (!@isset($MAINstyle["renderMenu"])) {
		$MAINstyle["renderMenu"] = DEFAULT_STYLE_PATH . RENDER_MENU;
	}
	

	// Set style path
	$MAINstyle["path"] = "style/$styleFolder";
}

echo "<html><head>";
// Read setting
readSetting();
// NEW: title with added sub-title
if ($MAINsubTitle && trim($MAINsubTitle) != "") {
	echo "<title>$MAINtitle :: $MAINsubTitle</title>";
} else {
	echo "<title>$MAINtitle</title>";
}

// Get style
$style = $MAINstyle["style"];
$icon = $MAINstyle["icon"];

echo "<link rel='stylesheet' href='$style?v.0.0.0.2' type='text/css'/>";
echo "<link rel='stylesheet' href='" . $MAINstyle['path'] . "/jquery/jquery-ui-1.8.18.custom.css?v.0.0.0.1' type='text/css'/>";

echo '<link rel="stylesheet" href="' . $MAINstyle['path'] . '/lib/bootstrap/dist/css/bootstrap.min.css">';
echo '<link rel="stylesheet" href="' . $MAINstyle['path'] . '/lib/font-awesome/css/font-awesome.min.css">';
echo '<link rel="stylesheet" href="' . $MAINstyle['path'] . '/AdminLTE.min.css?v.0.0.2">';
if (stillInSession()) {
	echo '<link rel="stylesheet" href="' . $MAINstyle['path'] . '/_all-skins.min.css">';
}
echo '<link rel="stylesheet" href="' . $MAINstyle['path'] . '/admin-style.css?v0004">';
// echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
echo "<link rel='shortcut icon' href='$icon'/>";
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';


echo "<script src='ext-core.js'></script>";
echo "<script src='c-tools.js'></script>";
echo "<script src='dialog_box.js'></script>";
echo "<script src='func.js'></script>";
echo "<script src='base64.js'></script>";
echo "<script src='scrollmessage.js'></script>";
echo "<script src='disableSelection.js'></script>";
echo '<script src="' . $MAINstyle['path'] . '/lib/jquery/dist/jquery.min.js"></script>';
echo '<script src="' . $MAINstyle['path'] . '/lib/bootstrap/dist/js/bootstrap.min.js"></script>';
echo '<script src="' . $MAINstyle['path'] . '/lib/jquery-slimscroll/jquery.slimscroll.min.js"></script>';
if (stillInSession()) {
	echo '<script src="' . $MAINstyle['path'] . '/js/adminlte.min.js"></script>';
	echo '<script src="' . $MAINstyle['path'] . '/js/demo.js"></script>';
	
	echo '<link rel="stylesheet" href="jquery-confirm.min.css">';
	echo '<script src="jquery-confirm.min.js"></script>';
}
// aldes, add qz.tray.js
echo '<script src="qz.tray.js"></script>';
echo "</head>";

if (@isset($logout) && $logout == 1) {
	if (@isset($data)) {
		// Delete from session central
		$uid = $data->uid;
		$Session->DeleteSessionFromDB($uid);

		// Delete cookies
		setcookie("centraldata", "", time() - 3600);
		$centralData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
	}

	// Redirect
	// echo "<meta http-equiv='REFRESH' content='0;url=main.php'>";
	header("Location: main.php");
} else if (stillInSession()) {
	// NEW: 30 Nov 2010
	// jika ada parameter 'etc', re-direct ke page tersebut
	if (isset($etc)) {
		if (file_exists("view/etc/$etc")) {
			echo "<body class='home-page'>\n";
			require_once("view/etc/$etc");
			echo "</body>\n";
		} else {
			echo "Page not exists";
		}
	} else {
		// Body start
		echo "<body onload='init();applyDefaultEnable()' class='home-page hold-transition skin-white fixed sidebar-mini'>";
		printBody();

		$uid = null;
		$uname = null;
		if ($data) {
			$uid = $data->uid;
			$uname = $User->GetUserName($uid);
		}

		// echo '
		// <div class="content-wrapper">
		// 	<!-- Content Header (Page header) -->
		// 	<section class="content-header">
		// 		'.$maintitle.'
		// 		<ol class="breadcrumb">
		// 			'.$breadcrumbs.'
		// 		</ol>
		// 	</section>
		// 	<!-- Main content -->
		// 	<section class="content">
		// ';
		echo '
		<div class="content-wrapper">
			<section class="content">
		';

		// var_dump($function,$setting,isAdmin($uid),$mode, $userProfile);exit();

		if ($setting == "1") {
			echo "		<div id='management'>";
			echo "<div class='spacer10'></div>";
			if (isAdmin($uid)) {
				if ($mode == "a") {
					include_once("view/adm/adm-app.php");
				} else if ($mode == "s") {
					include_once("view/adm/adm-setting.php");
				} else if ($mode == "m") {
					include_once("view/adm/adm-module.php");
				} else if ($mode == "l") {
					global $arConfigKey;
					include_once("view/adm/adm-locket.php");
				} else if ($mode == "u") {
					include_once("view/adm/adm-user.php");
				} else if ($mode == "d") {
					include_once("view/adm/adm-database.php");
				} else if ($mode == "r") {
					include_once("view/adm/adm-role.php");
				} else if ($mode == "f") {
					include_once("view/adm/adm-function.php");
				} else if ($mode == "h") {
					include_once("view/adm/adm-help.php");
				} else if ($mode == "t") {
					include_once("view/adm/adm-auth.php");
				} else if ($mode == "n") {
					include_once("view/adm/adm-session.php");
				} else {
					echo "<div class='subTitle'>Management Tools</div>";
					echo "<div class='spacer10'></div>";

					// Array of management menu
					$arManagementMenu = array(
						// array("urlParam" => "setting=1&m=s", "imageMenu" => "tools64.png",  "header" => "Setting"),
						array("urlParam" => "setting=1&m=a", "imageMenu" => "target64.png", "header" => "Application"),
						array("urlParam" => "setting=1&m=m", "imageMenu" => "box64.png",    "header" => "Module"),
						array("urlParam" => "setting=1&m=u", "imageMenu" => "purba-64.png", "header" => "User"),
						array("urlParam" => "setting=1&m=d", "imageMenu" => "save64.png",   "header" => "Database"),
						array("urlParam" => "setting=1&m=r", "imageMenu" => "key64.png",    "header" => "Role"),
						array("urlParam" => "setting=1&m=f", "imageMenu" => "wired64.png",  "header" => "Function"),
						array("urlParam" => "setting=1&m=t", "imageMenu" => "map64.png",    "header" => "Auth"),
						array("urlParam" => "setting=1&m=n", "imageMenu" => "tag64.png",    "header" => "Session"),
						array("urlParam" => "setting=1&m=h", "imageMenu" => "help64.png",   "header" => "Help")
					);

					echo "<table cellpadding='0px' border='0' class='transparent'>";
					echo "<tr>";
					// Print icon
					foreach ($arManagementMenu as $iMenu) {
						$url64 = base64_encode($iMenu["urlParam"]);
						$imageMenu = $iMenu["imageMenu"];
						$header = $iMenu["header"];

						echo "<td>";
						echo "<a href='main.php?param=$url64'><img src='image/menu/$imageMenu' alt='$header' border='0' class='imageHrefIcon'></img></a>";
						echo "</td>";
						echo "<td></td>";
					}
					echo "</tr>";
					echo "<tr>";
					// Print icon menu
					foreach ($arManagementMenu as $iMenu) {
						$url64 = base64_encode($iMenu["urlParam"]);
						$header = $iMenu["header"];

						echo "<td align='center'>";
						echo "<b><a href='main.php?param=$url64'>$header</a></b>";
						echo "</td>";
						echo "<td width='10px'></td>";
					}
					echo "</tr>";
					echo "</table>";
				}
			} else if (isSupervisor($uid)) {
				if ($mode == "u") {
					include_once("view/adm/adm-user.php");
				} else if ($mode == "h") {
					include_once("view/adm/adm-help.php");
				} else {
					echo "<div class='subTitle'>Management Tools</div>";
					echo "<div class='spacer10'></div>";

					// Array of management menu
					$arManagementMenu = array(
						array("urlParam" => "setting=1&m=u", "imageMenu" => "purba-64.png", "header" => "User"),
						array("urlParam" => "setting=1&m=h", "imageMenu" => "help64.png",   "header" => "Help")
					);

					echo "<table cellpadding='0px' border='0' class='transparent'>";
					echo "<tr>";
					// Print icon
					foreach ($arManagementMenu as $iMenu) {
						$url64 = base64_encode($iMenu["urlParam"]);
						$imageMenu = $iMenu["imageMenu"];
						$header = $iMenu["header"];

						echo "<td>";
						echo "<a href='main.php?param=$url64'><img src='image/menu/$imageMenu' alt='$header' border='0' class='imageHrefIcon'></img></a>";
						echo "</td>";
						echo "<td></td>";
					}
					echo "</tr>";
					echo "<tr>";
					// Print icon menu
					foreach ($arManagementMenu as $iMenu) {
						$url64 = base64_encode($iMenu["urlParam"]);
						$header = $iMenu["header"];

						echo "<td align='center'>";
						echo "<b><a href='main.php?param=$url64'>$header</a></b>";
						echo "</td>";
						echo "<td width='10px'></td>";
					}
					echo "</tr>";
					echo "</table>";
				}
			}
			echo "</div>";
		} else if ($userProfile == "1") {
			// die('test');
			echo "		";

			// Sub menu
			echo "<div id='subMenu'>";
			echo "<a href='main.php'>&laquo;&nbsp;&nbsp;Halaman Utama</a>";
			echo "&nbsp;&nbsp;";

			// View profile
			if ($mode != "") {
				$url64 = base64_encode("userProfile=1");
				echo "<a href='main.php?param=$url64'>User profile</a>";
			} else {
				echo "<b>User profile</b>";
			}
			echo "&nbsp;&nbsp;";

			// Change password
			if ($mode != "chPass") {
				$url64 = base64_encode("userProfile=1&m=chPass");
				echo "<a href='main.php?param=$url64'>Change Password</a>";
			} else {
				echo "<b>Change Password</b>";
			}
			echo "&nbsp;&nbsp;";
			/*
			// Change layout
			if ($mode != "chLayout") {
				$url64 = base64_encode("userProfile=1&m=chLayout");
				echo "<a href='main.php?param=$url64'>Change Layout</a>";
			} else {
				echo "<b>Change Layout</b>";
			}
			echo "&nbsp;&nbsp;";		
			*/
			// End
			echo "</div>";
			echo "<div id='user-profile'>";

			include("view/profile/userProfile.php");

			echo "</div>";
		} else if ($function != "") {
			$bOK = getFunctionPage();

			if ($bOK && $userFuncPage != "") {
				// Content
				// NOTE: Don't print content in local function
				echo "<div class=\"row\"><div class='col-md-12'>";

				// Submenu navigasi main page
				if ($function != "") {
					echo "<div id='subMenu'>";
					// NEW: use $MAINparam for function
					if ($MAINparam == null) {
						$url64 = base64_encode("a=$application&m=$module");
					} else {
						$decParam = base64_decode($MAINparam);
						$url64 = base64_encode("a=$application&m=$module&$decParam");
					}
					echo "<a href='main.php?param=" . $url64 . "'>&laquo;&nbsp;&nbsp;Halaman Utama</a>";

					// print function
					$func = null;
					$bOK = $User->GetFunction($module, $func);
					if ($bOK) {
						$i = 0;
						echo "<ul>";
						foreach ($func as $funcValue) {
							$funcId = $funcValue["id"];
							$funcName = $funcValue["name"];
							$funcPos = $funcValue["pos"];
							$funcImage = $funcValue["image"];

							if ($User->IsFunctionGranted($uid, $area, $module, $funcId)) {
								if ($funcPos == 1) {
									if ($funcId == $function) {
										echo "<li><b>$funcName</b></li>";
									} else {
										$url64 = base64_encode("a=$area&m=$module&f=$funcId");
										echo "<li><a href='main.php?param=$url64'>$funcName</a></li>";
									}
								}
							}
						}
						echo "</ul>";
					}

					echo "</div>";
					echo "<div class='spacer10'></div></div>";
				}

				$appDbLink = $User->GetDbConnectionFromApp($application);
				$areaDbLink = $appDbLink;
				$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

				// NEW: Read arFunction
				$func = null;
				$bOK = $User->GetFunction($module, $func);
				if ($bOK) {
					$i = 0;
					foreach ($func as $funcValue) {
						$funcId = $funcValue["id"];
						$funcName = $funcValue["name"];
						$funcPos = $funcValue["pos"];
						$funcImage = $funcValue["image"];

						if ($User->IsFunctionGranted($uid, $application, $module, $funcId)) {
							if ($funcPos == 0) {
								// NEW: print 'per terminal' function
								$arFunction[$i] = array("urlParam" => "a=$application&m=$module&f=$funcId", "image" => "image/icon/$funcImage", "name" => "$funcName");
								$i++;
							}
						}
					}
				}
				// echo $userFuncPage;
				//var_dump($userFuncPage);exit();
				include($userFuncPage);
				// End of content

				echo "</div>";
			} else {
				echo "<div class='spacer10'></div>";
				echo "<div class='error'>No view</div>";
			}
		} else if ($module != "" && !isEmpty($userModuleView)) {
			//var_dump($userModuleView);exit();
			if (file_exists($userModuleView)) {
				// Content
				// NOTE: Don't print content in local function
				echo '<div class="row">';

				// Connect to switcher database
				$appDbLink = $User->GetDbConnectionFromApp($application);
				$areaDbLink = $appDbLink;
				$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

				// print function
				$func = null;
				$bOK = $User->GetFunction($module, $func);
				if ($bOK) {
					$i = 0;
					$writeSubMenu = false;
					if ($func != null && count($func) > 0) {
						foreach ($func as $funcValue) {
							$funcId = $funcValue["id"];
							$funcName = $funcValue["name"];
							$funcPos = $funcValue["pos"];
							$funcImage = $funcValue["image"];

							if ($User->IsFunctionGranted($uid, $application, $module, $funcId)) {
								if ($funcPos == 0) {
									// NEW: print 'per terminal' function
									$arFunction[$i] = array("urlParam" => "a=$application&m=$module&f=$funcId", "image" => "image/icon/$funcImage", "name" => "$funcName");
									$i++;
								} else if ($funcPos == 1) {
									// NEW: print 'per module' function
									$url64 = base64_encode("a=$application&m=$module&f=$funcId");
									if (!$writeSubMenu) {
										echo "<div class='col-md-12'><div id='subMenu'><ul>";
										$writeSubMenu = true;
									}
									echo "<li><a href='main.php?param=$url64'>$funcName</a></li>";
								}
							}
						}
					}
					if ($writeSubMenu) {
						echo "</ul></div></div>";
					}
				}
				// echo $userModuleView;
				include($userModuleView);

				// End of content
				echo "</div>";
			} else {
				echo "<div class='spacer10'></div>";
				echo "<div>Error</div>";
			}
		}
		// Footer
		$MAINendRender = microtime(1);
		$MAINdurasiRender = ($MAINendRender - $MAINstartRender);
		$MAINdurasiRender = substr($MAINdurasiRender, 0, 7);
		$MAINfooterText = str_replace("{\$durasiRender}", $MAINdurasiRender, $MAINfooterText);
		echo "</section>";
		echo "	<div id='footer'>" . ($MAINfooterText) . "</div>\n";
		echo "</div>";
		//echo "</section>";
		echo "</body>";
	}
} else {
	include("plogin.php");
}

if (isset($appDBLink)) {
	if ($appDBLink) SCANPayment_CloseDB($appDBLink);
}
SCANPayment_CloseDB($DBLink);
ob_end_flush();
echo "<script>$(function(){ DisplayClock();})</script></html>";
