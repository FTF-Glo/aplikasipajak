<?php
// Print user info, logout, and management link (admin only)
function printUser()
{
  global $data, $setting, $userProfile;

  if ($data) {
    $uid = $data->uid;
    $uname = $data->uname;


    //tamabahan session
    global $DBLink;
    $qry = "select * from central_user_to_app where CTR_APP_ID = 'aBPHTB' and CTR_USER_ID = '" . $uid . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      echo $qry . "<br>";
      echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
      $save = $row['CTR_RM_ID'];
    }
    $save = str_replace(' ', '', $save);
    $_SESSION["role"] = $save;
    $_SESSION["uname"] = $uname;
    //echo $save;
    //var_dump($_SESSION["role"]);die;



    // Start
    echo "<div id='user-menu'>";

    // User name
    //   echo " Jam Server : <span id='vpos-clock'>".(date('r'))."</span>&nbsp;&nbsp;User: ";
    //   echo $data->uname . "@". (isset($data->ppid)?$data->ppid:"")."&nbsp;(";
    $url64 = base64_encode("logout=1");
    // echo "<a href='main.php?param=$url64'>logout</a>";
    // echo ")";
    //new desain
    echo "<ul class='nav navbar-nav float-xs-right'>
            <li class=\"dropdown dropdown-user nav-item\">
              <a href=\"#\" aria-expanded=\"false\" data-toggle=\"\" class=\"toggle nav-link\">
                <span id=\"tanggal-berjalan\">" . date('D, d M Y') . "</span>
                <span id=\"waktu-berjalan\">" . date('H:i:s') . "</span>
                <input type='hidden' id='date_' value='" . date("Y-m-d H:i:s") . "'>
              </a>
            </li>
            <li class='dropdown dropdown-user nav-item'>
              <a class='dropdown-toggle nav-link' href='#' data-toggle='dropdown'>
                <span class='user-name'>$data->uname</span>
              </a>
              <div class='dropdown-menu dropdown-menu-right'>
              <a class='dropdown-item' href='main.php?param=$url64'>Logout</a>";
    // User profile
    if ($userProfile == "") {
      $url64 = base64_encode("userProfile=1");
      echo "<a class='dropdown-item' href='main.php?param=$url64'>User Profile</a>";
    }
    // ADMIN ONLY: Management
    if ((isAdmin($uid) || isSupervisor($uid)) && $setting == "") {
      $url64 = base64_encode("setting=1");
      echo "<a class='dropdown-item' href='main.php?param=$url64'>Management</a>";
    }
    echo "</div>";
  }
}

// return Header information
function renderHeader()
{
  global $area, $module, $function, $MAINstyle, $setting, $data, $mode, $subMode, $id, $userProfile;

  // Get logo
  $logo = $MAINstyle["littleLogo"];

  echo "<head>
	<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
	<meta http-equiv='X-UA-Compatible' content='IE=edge'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui'>
	<meta name='description'
	  content='Robust admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.'>
	<meta name='keywords'
	  content='admin template, robust admin template, dashboard template, flat admin template, responsive admin template, web app'>
	<meta name='author' content='PIXINVENT'>
	<title>BPHTB ONLINE</title>
	  <link rel='shortcut icon' type='image/png' href='style/default/alfalogo.png'>

	<meta name='apple-mobile-web-app-capable' content='yes'>
	<meta name='apple-touch-fullscreen' content='yes'>
	<meta name='apple-mobile-web-app-status-bar-style' content='default'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/css/bootstrap.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/fonts/icomoon.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/fonts/flag-icon-css/css/flag-icon.min.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/vendors/css/extensions/pace.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/css/bootstrap-extended.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/css/app.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/css/colors.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/css/core/menu/menu-types/vertical-menu.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/css/core/menu/menu-types/vertical-overlay-menu.css'>
	<link rel='stylesheet' type='text/css' href='style/app-assets/vendors/css/documentation.css'>
	<link rel='stylesheet' type='text/css' href='style/assets/css/style.css'>
  <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css\">
  </head>";
}

// return Footer information
function renderFooter()
{
  $footer = ""; //"Power By -";
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

    $footer .= "<div id='fo_content'>" . '<table class="transparent"><tr><td><a href="#" onclick="displayMessages()"><img src="image/icon/feed.png" width="20" height="20" style="margin-right:8px;display:inline" align="absmiddle"/></a></td><td><span id="marquee-container">	<div style="position:relative;width:2000px;height:30px;overflow:hidden"><div style="position:absolute;width:2000px;height:30px;" onMouseover="COPYSPEED=PAUSESPEED" onMouseout="COPYSPEED=MARQUEE_SPEED"><div id="iemarquee" style="position:absolute;left:0px;top:0px;font-family:verdana;font-size:11pt;font-weight:bold"></div></div></div></span></td></tr></table><span id="temp" style="visibility:hidden;position:absolute;top:-100px;left:-9000px;font-weight:bold;font-size:10pt;"></span>' . "</div><div>Copyright &copy; PT. ValueStream International, 2010<br />Executed in {\$durasiRender} ms</div>
	  
	  ";
  }
  return '
<style>
	th.jtable-column-header, th.jtable-command-column-header {
    background: #8cbc7b;
}
.filtering {
	background: #7bbc8b;
}
.main-menu.menu-dark .navigation > li.active > a {
	color: #000;
    background-color: #FFF;
    font-size:10pt;
}
.header-navbar.navbar-semi-dark .navbar-header {
	color: #FFFFE0;
    background: #FFF;
}
.main-menu.menu-dark {
    color: #FFFFE0;
    background: #FFF;
}

.main-menu.menu-dark .navigation > li ul .active > a {
  background: rgb(230, 223, 255);
  background: linear-gradient(22deg, rgba(30, 120, 150, 1) 0%, rgba(30, 120, 150, 0.5) 100%) !important;
  color: black;
  border-radius: 30px;
  font-size: 10pt;
  font-weight: 700;

}
.main-menu.menu-dark .navigation > li ul li {
	color: #000;
    background: #fff;
    font-size:10pt;
}

.main-menu.menu-dark .navigation > li ul .active {
  background: white !important;
}
 
.main-menu.menu-dark .navigation > li  ul li > a:hover {	 
    background-color: #e3e1e1 !important;
    color : black;
}
.main-menu.menu-dark .navigation li a {
	color: #000;
}
footer.footer-dark {
	background-color: #2c917f;
	color: #FFFFE0;
}
.main-menu.menu-dark .navigation > li.open .hover > a {
    background: #1a564c;
}


body.vertical-layout.vertical-menu.menu-collapsed .main-menu .main-menu-content > span.menu-title, body.vertical-layout.vertical-menu.menu-collapsed .main-menu .main-menu-content a.menu-title{
	background-color:#267e6e;
}
.main-menu.menu-dark ul-menu-popout li.hover > a, .main-menu.menu-dark ul.menu-popout
li:hover > a, .main-menu.menu-dark ul.menu-popout li.open >a{
	background:#2c917f;
}
.main-menu.menu-dark ul-menu-popout li.hover > a, .main-menu.menu-dark ul.menu-popout
li > a, .main-menu.menu-dark ul.menu-popout li.open >a{
	background:#1a564c;
}

.main-menu.menu-dark ul.menu-popout{
	background:#267e6e;
}

.main-menu.menu-dark ul.menu-popout .active > a{
	background-color:#2c917f;
}



#notaris-main-content-footer {
    background: linear-gradient(22deg, rgba(30, 120, 150, 1) 0%, rgba(30, 120, 150, 0.5) 100%) !important;;
    color: black;
    font-weight: bold;
}


.ui-widget-header {
  border: 1px solid #d4ccb0;
  background: #ece8da url(images/ui-bg_gloss-wave_100_ece8da_500x100.png) 50% 50% repeat-x;
  color: #433f38;
  font-weight: 500;
}

</style>
  <script src="style/app-assets/vendors/js/ui/tether.min.js" type="text/javascript"></script>
  <script src="style/app-assets/vendors/js/ui/perfect-scrollbar.jquery.min.js" type="text/javascript"></script>
  <script src="style/app-assets/vendors/js/ui/unison.min.js" type="text/javascript"></script>
  <script src="style/app-assets/vendors/js/ui/blockUI.min.js" type="text/javascript"></script>
  <script src="style/app-assets/vendors/js/ui/jquery.matchHeight-min.js" type="text/javascript"></script>
  <script src="style/app-assets/vendors/js/ui/screenfull.min.js" type="text/javascript"></script>
  <script src="style/app-assets/vendors/js/extensions/pace.min.js" type="text/javascript"></script>
  <script src="style/app-assets/js/core/app-menu.js" type="text/javascript"></script>
  <script src="style/app-assets/js/core/app.js" type="text/javascript"></script>
  <script src="style/app-assets/vendors/js/ui/affix.js" type="text/javascript"></script>
  <script src="waktu_berjalan.js" type="text/javascript"></script>
</html><!-- <span>' . $footer . '</span> -->';
}

function renderTitle()
{
  return "BPHTB ONLINE";
}
function _waktu_berjalan()
{
  $return = "";
  return $return;
}
