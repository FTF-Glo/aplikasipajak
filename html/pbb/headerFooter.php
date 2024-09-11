<?php

// Print user info, logout, and management link (admin only)
function printUsers()
{
	global $data, $setting, $userProfile;

	if ($data) {
		$uid = $data->uid;
		$uname = $data->uname;

		// Start
		echo "<div id='user-menu'>";

		// User name
		echo " Jam Server : <span id='vpos-clock'>" . (date('r')) . "</span>&nbsp;&nbsp;User: ";
		echo $data->uname . "@" . (isset($data->ppid) ? $data->ppid : "") . "&nbsp;(";
		$url64 = base64_encode("logout=1");
		echo "<a href='main.php?param=$url64'>logout</a>";
		echo ")";

		// User profile 
		if ($userProfile == "") {
			$url64 = base64_encode("userProfile=1");
			echo "	&nbsp;";
			echo "	<b><a href='main.php?param=$url64'>User Profile</a></b>";
		}

		// ADMIN ONLY: Management
		if ((isAdmin($uid) || isSupervisor($uid)) && $setting == "") {
			$url64 = base64_encode("setting=1");
			echo "	&nbsp;";
			echo "	<b><a href='main.php?param=$url64'>Management</a></b>";
		}

		// End
		echo "</div>";
	}
}

function printUser()
{
	global $data, $setting, $userProfile;

	if ($data) {
		$uid = $data->uid;
		$uname = $data->uname;

		// Start
		echo '<div class="navbar-custom-menu">
			<ul class="nav navbar-nav">
				<!-- Notifications: style can be found in dropdown.less -->
				<li class="dropdown notifications-menu">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
					<i class="fa fa-gear text-black"></i>
					</a>
					<ul class="dropdown-menu">
						<li>
							<!-- inner menu: contains the actual data -->
							<ul class="menu">';
		if ($userProfile == "") {
			$url64 = base64_encode("userProfile=1");
			echo '<li>
						<a href="main.php?param=' . $url64 . '">
							<i class="fa fa-user"></i> User Profile
						</a>
					</li>';
		}

		if ((isAdmin($uid) || isSupervisor($uid)) && $setting == "") {
			$url64 = base64_encode("setting=1");
			echo '
			<li>
				<a href="main.php?param=' . $url64 . '">
					<i class="fa fa-users"></i> Management
				</a>
			</li>';
		}

		$url64 = base64_encode("logout=1");

		echo '
								<li>
									<a href="main.php?param=' . $url64 . '">
										<i class="fa fa-arrow-left"></i> Logout
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>
		</div>';
	}
}

function printUsername()
{
	global $data, $setting, $userProfile;

	if ($data) {
		$uid = $data->uid;
		$uname = $data->uname;

		echo '
		<!-- Sidebar user panel -->
			<!--div class="user-panel">
				<div class="pull-left image">&nbsp;</div>
				<div class="pull-left info">
					<p>' . $data->uname . '@' . (isset($data->ppid) ? $data->ppid : "") . '</p><br />
					<p>Jam Server : <span id="vpos-clock">' . (date('r')) . '</span></p>
				</div>
			</div-->';
	}
}

// return Header information
/*function renderHeader()
{
	global $area, $module, $function, $MAINstyle, $setting, $data, $mode, $subMode, $id, $userProfile;

	// Get logo
	$logo = $MAINstyle["littleLogo"];

	// Header
	echo "	<div id='container'>\n";
	echo "		<div id='main' class='clearfix'>\n";
	echo "			\n";
	echo "			<!-- Header -->\n";
	echo "			<div id='header'>\n";

	// User
	printUser();

	// Logo
	echo "				<div id='logo'>\n";
	echo "					<a href='main.php?";
	$url64 = "";
	// NEW: base64 parameter
	if (isset($area) && $area != "") {
		$url64 = "a=$area";
	}
	if (isset($module) && $module != "") {
		$url64 .= "&m=$module";
	}
	$url64 = base64_encode($url64);
	echo "param=$url64";
	echo "'>\n";
	echo "						<img src='$logo' alt='DMS Logo' border='0'></img>\n";
	echo "					</a>\n";
	echo "				</div>\n";

	// End of header
	echo "			</div>\n";
}*/

//Updated by Daniel
function renderHeader()
{
	global $area, $module, $function, $MAINstyle, $setting, $data, $mode, $subMode, $id, $userProfile;

	// Get logo
	$logo = $MAINstyle["littleLogo"];
	$logo_kotak = 'style/default/logo.png';

	$url64 = "";
	// NEW: base64 parameter
	if (isset($area) && $area != "") {
		$url64 = "a=$area";
	}
	if (isset($module) && $module != "") {
		$url64 .= "&m=$module";
	}
	$url64 = base64_encode($url64);

	//Menu
	echo '<!-- Site wrapper -->
	<div class="wrapper">
		<header class="main-header">
			<!-- Logo -->
			<a href="main.php?param=' . $url64 . '" class="logo" style="background-color:#fff">
				<!-- mini logo for sidebar mini 50x50 pixels -->
				<span class="logo-mini"><img src="' . $logo_kotak . '" alt="Logo" height="40" /></span>
				<!-- logo for regular state and mobile devices -->
				<span class="logo-lg"><img src="' . $logo . '" alt="Logo" height="50" /></span>
			</a>
			<!-- Header Navbar: style can be found in header.less -->
			<nav class="navbar navbar-static-top">
				<!-- Sidebar toggle button-->
				<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<span id="vpos-clock" style="margin-top:15px;float:left;color:#fff">' . (date('r')) . '</span>
				';
	printUser();

	echo '
			</nav>
		</header>
	
		<!-- =============================================== -->
	
		<!-- Left side column. contains the sidebar -->
		<aside class="main-sidebar" style="background-image:linear-gradient(to right, #236840, #429163) !important">
		<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">';

	printUserName();
}

// return Footer information
function renderFooter()
{
	$footer = "Power By AlfaTax";
	global $User, $data;
	if (isset($data->ppid)) {
		$footer = "<script language='javascript'>";
		$msglist = $User->GetBroadcastMessageList($data->ppid);
		$nMsg = count($msglist);
		for ($i = 0; $i < $nMsg; $i++) {
			$footer .= "BROADCASTMSGS[$i]='" . $msglist[$i]["CPC_BM_MSG"] . "';";
		}
		if (!isset($_SESSION["centraldata_afterlogin"])) {
			$_SESSION["centraldata_afterlogin"] = 1;
			$footer .= "window.setTimeout(\"displayMessages()\",10000);";
		} else if ($_SESSION["centraldata_afterlogin"] != 1) {
			$_SESSION["centraldata_afterlogin"] = 1;
			$footer .= "window.setTimeout(\"displayMessages()\",10000);";
		}
		$footer .= "</script>";

		$footer .= "<div id='fo_content'>" . '<table class="transparent"><tr><td><a href="#" onclick="displayMessages()"><img src="image/icon/feed.png" width="20" height="20" style="margin-right:8px;display:inline" align="absmiddle"/></a></td><td><span id="marquee-container">	<div style="position:relative;width:2000px;height:30px;overflow:hidden"><div style="position:absolute;width:2000px;height:30px;" onMouseover="COPYSPEED=PAUSESPEED" onMouseout="COPYSPEED=MARQUEE_SPEED"><div id="iemarquee" style="position:absolute;left:0px;top:0px;font-family:verdana;font-size:11pt;font-weight:bold"></div></div></div></span></td></tr></table><span id="temp" style="visibility:hidden;position:absolute;top:-100px;left:-9000px;font-weight:bold;font-size:10pt;"></span>' . "</div><div>Copyright &copy; PT. ValueStream International, 2010<br />Executed in {\$durasiRender} ms</div>";
	}
	return $footer;
}

function renderTitle()
{
	return "PBB-P2 PESIBAR";
}
