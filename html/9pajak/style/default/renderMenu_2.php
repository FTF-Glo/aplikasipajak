<?php

/*======================*/
/*                      */
/*    Rendering Menu    */
/*                      */
/*======================*/

//

function initSession(){
	global $DBLink, $User, $uid, $application;
	$uname = $User->GetUserName($uid);
	$role = $User->GetUserRole($uid, $application);
	if($role == 'rmPatdaWp'){
		$query = "SELECT * FROM PATDA_WP WHERE CPM_USER='{$uname}'";
		$res = mysqli_query($DBLink, $query);
		$d = mysqli_fetch_assoc($res);

		$_SESSION['npwpd'] = isset($d['CPM_NPWPD']) ? preg_replace("/[^A-Za-z0-9 ]/", '', $d['CPM_NPWPD']) : "";
		$_SESSION['uname'] = $uname;
	}else{
		$_SESSION['npwpd'] = '';
		$_SESSION['uname'] = $uname;
	}

	$_SESSION['_success'] = isset($_SESSION['_success'])?$_SESSION['_success'] : '';
	$_SESSION['_error'] = isset($_SESSION['_error'])?$_SESSION['_error'] : '';
}

function renderMenu($appId, $appName, $arApp, $moduleId, $moduleName, $arModule) {
	global $User, $uid, $application;

	/*ARD
	 * init session
	 * */
	 initSession($uid);

	// echo "			\n";
	// echo "			<!-- Menu application and module -->\n";

	// Include renderer javascript
	echo "			<script type='text/javascript' src='style/default/renderMenu.js'></script>\n";

	// echo "			<div id='menu'>\n";

	// Print application
	// echo "				<div id='app'>Application: <b>";
	// if (strlen($appName) > 38) {
	// 	$dispAppName = substr($appName, 0, 38);
	// 	echo "<span title='$appName'>$dispAppName..</title>";
	// } else {
	// 	echo "$appName";
	// }
	// echo "</b></div>\n";

	// Print module
	// echo "				<div id='module'>Module: <b>";
	// if (strlen($moduleName) > 38) {
	// 	$dispModuleName = substr($moduleName, 0, 38);
	// 	echo "<span title='$moduleName'>$dispModuleName..</title>";
	// } else {
	// 	echo "$moduleName";
	// }
	// echo "</b></div>\n";

	// // Print application change
	// echo "				\n";
	// echo "				<div id='app-change'>\n";
	// echo "					<a href='#' id='app-link' onClick='showSelectApp();'>Change application</a>\n";
	// echo "					<select id='app-select' style='display:none; margin-top:-2px; margin-bottom:-2px;' onFocus='focusSelectApp();' onChange='changeApp(\"$appId\", \"$moduleId\");'>\n";
	// echo "						<option value='0'>--------</option>\n";
	// foreach ($arApp as $iApp) {
	// 	$id = $iApp["id"];
	// 	$name = $iApp["name"];

	// 	echo "						<option value='$id'>$name</option>\n";
	// }
	// echo "					</select>\n";
	// echo "				</div>\n";

	// // Print module change
	// echo "				\n";
	// echo "				<div id='module-change'>\n";
	// echo "					<a href='#' id='module-link' onClick='showSelectModule();'>Change module</a>\n";

	function getArrPajakAktif($uid){
		global $DBLink, $User;
	 	$uid = $User->GetUserName($uid);
	 	$query = "SELECT * FROM PATDA_WP WHERE CPM_USER = '{$uid}'";
	 	$result = mysqli_query($DBLink, $query);
	 	$data = mysqli_fetch_array($result);
	 	$jenis_pajak = explode(";", $data['CPM_JENIS_PAJAK']);
	 	return $jenis_pajak;
	 }

	/**
	 * ARD+
	 * Untuk Role WP maka diberi akses sesuai dengan jenis pajak yang dimilikinya
	 */
	$role = $User->GetUserRole($uid, $application);

	$arr_pajak_aktif = array();
	if($role == "rmPatdaWp"){
		/*get jenis pajak apa saja yang aktif pada wp*/
		$arr_pajak_aktif = getArrPajakAktif($uid);

	}

	echo "<select id='module-select' style='display:none; margin-top:-2px; margin-bottom:-2px;' onFocus='focusSelectModule();' onChange='changeModule(\"$appId\");'>\n";
	echo "<option value='0'>--------</option>\n";

	if($application == "aPatda"){
		$newMenu = array();
		foreach($arModule as $iModule){

			$id = $iModule["id"];
			$name = $iModule["name"];
			$arrName = explode("-",$name);
			$groupName = $arrName[0];
			$newMenu[$groupName][] = $iModule;
		}

		foreach($newMenu as $group=>$menus){
			echo "<optgroup label=\"{$group}\">";
			foreach($menus as $iModule){
				$id = $iModule["id"];
				$name = $iModule["name"];
				$name = str_replace($group.'-','&#9758;',$name);
				if($role == "rmPatdaWp"){
					$list_pajak = str_replace("mPatdaPelapor","",$id);
					if(in_array($list_pajak, $arr_pajak_aktif))
						echo "<option value='$id'>$name</option>\n";

				}else{
					echo "<option value='$id'>$name</option>\n";
				}
			}
			echo "</optgroup>";
		}
	}else{
		foreach ($arModule as $iModule) {
			$id = $iModule["id"];
			$name = $iModule["name"];
			if($role == "rmPatdaWp"){
				echo $list_pajak = str_replace("mPatdaPelapor","",$id);
				if(in_array($list_pajak, $arr_pajak_aktif))
					echo "<option value='$id'>$name</option>\n";

			}else{
				echo "<option value='$id'>$name</option>\n";
			}

		}
	}
	echo "					</select>\n";
	echo "				</div>\n";

	// End of menu
	echo "</div>\n";


	if($application == "aPatda"){
		$newMenu = array();
		foreach($arModule as $iModule){

			$id = $iModule["id"];
			$name = $iModule["name"];
			$arrName = explode("-",$name);
			$groupName = $arrName[0];
			$newMenu[$groupName][] = $iModule;
		}
		echo '<div data-scroll-to-active="true" class="main-menu menu-fixed menu-dark menu-accordion menu-shadow">
		<div class="main-menu-content menu-accordion">
		  <ul id="main-menu-navigation" data-menu="menu-navigation" class="navigation navigation-main">';


		foreach($newMenu as $group=>$menus){
			echo "<li class='active nav-item'><a href='#'><i class='icon-stack-2'></i><span data-i18n='' class='menu-title'>{$group}</span></a>";
			echo"</span></a><ul class='menu-content'>";
			foreach($menus as $iModule){
				$id = $iModule["id"];
				$name = $iModule["name"];
				$name = str_replace($group.'-','&rsaquo;',$name);
				if($role == "rmPatdaWp"){
					$list_pajak = str_replace("mPatdaPelapor","",$id);
					if(in_array($list_pajak, $arr_pajak_aktif))
					// echo "<option value='$id'>$name</option>\n";
					$encode = base64_encode("a=aPatda&m={$id}");
					$active ='';
					if($moduleName == $iModule["name"]){
						$active = 'active';
					}
					echo "<li class='$active'><a href='main.php?param=$encode' class='menu-item'>$name</option></a></li>";
				}else{
					// echo "<option value='$id'>$name</option>\n";
					$encode = base64_encode("a=aPatda&m={$id}");
					$active ='';

					if($moduleName == $iModule["name"]){
						$active = 'active';
					}
					echo "<li class='$active'><a href='main.php?param=$encode' class='menu-item'>$name</option></a></li>";
				}
			}
			echo"</ul>";
		}
		echo"</li>";
	}else{

			echo '<div data-scroll-to-active="true" class="main-menu menu-fixed menu-dark menu-accordion menu-shadow">
  <div class="main-menu-content menu-accordion">
    <ul id="main-menu-navigation" data-menu="menu-navigation" class="navigation navigation-main">



    </ul>
  </div>
</div>
';

	}
	echo "					</select>\n";
	echo "				</div>\n";

	// End of menu
	echo "</div>\n";
	echo"</ul>";
	echo "</div>
  </div>";

}


?>
