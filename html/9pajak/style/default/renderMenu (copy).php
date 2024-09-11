<?

/*======================*/
/*                      */
/*    Rendering Menu    */
/*                      */
/*======================*/

//

function initSession(){
	global $DBLink, $User, $uid;
	$uname = $User->GetUserName($uid);
	
	
	$_SESSION['_success'] = isset($_SESSION['_success'])?$_SESSION['_success'] : '';
	$_SESSION['_error'] = isset($_SESSION['_error'])?$_SESSION['_error'] : '';
}

function renderMenu($appId, $appName, $arApp, $moduleId, $moduleName, $arModule) {
	global $User, $uid, $application;
	
	/*ARD
	 * init session
	 * */
	 initSession($uid);
	 
	echo "			\n";
	echo "			<!-- Menu application and module -->\n";
	
	// Include renderer javascript
	echo "			<script type='text/javascript' src='style/default/renderMenu.js'></script>\n";
	
	echo "			<div id='menu'>\n";

	// Print application
	echo "				<div id='app'>Application: <b>";
	if (strlen($appName) > 38) {
		$dispAppName = substr($appName, 0, 38);
		echo "<span title='$appName'>$dispAppName..</title>";
	} else {
		echo "$appName";
	}
	echo "</b></div>\n";

	// Print module
	echo "				<div id='module'>Module: <b>";
	if (strlen($moduleName) > 38) {
		$dispModuleName = substr($moduleName, 0, 38);
		echo "<span title='$moduleName'>$dispModuleName..</title>";
	} else {
		echo "$moduleName";
	}
	echo "</b></div>\n";

	// Print application change
	echo "				\n";
	echo "				<div id='app-change'>\n";
	echo "					<a href='#' id='app-link' onClick='showSelectApp();'>Change application</a>\n";
	echo "					<select id='app-select' style='display:none; margin-top:-2px; margin-bottom:-2px;' onFocus='focusSelectApp();' onChange='changeApp(\"$appId\", \"$moduleId\");'>\n";
	echo "						<option value='0'>--------</option>\n";
	foreach ($arApp as $iApp) {
		$id = $iApp["id"];
		$name = $iApp["name"];

		echo "						<option value='$id'>$name</option>\n";
	}
	echo "					</select>\n";
	echo "				</div>\n";

	// Print module change
	echo "				\n";
	echo "				<div id='module-change'>\n";
	echo "					<a href='#' id='module-link' onClick='showSelectModule();'>Change module</a>\n";
	
	function getArrPajakAktif($uid){
		global $DBLink, $User;
		$uid = $User->GetUserName($uid);
		$query = "SELECT * FROM PATDA_WP WHERE CPM_USER = '{$uid}'";
		$result = mysql_query($query, $DBLink);
		$data = mysql_fetch_array($result);
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
	echo "			</div>\n";
}

?>
