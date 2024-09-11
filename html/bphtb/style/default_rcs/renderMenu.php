<?php

/*======================*/
/*                      */
/*    Rendering Menu    */
/*                      */
/*======================*/

function renderMenu($appId, $appName, $arApp, $moduleId, $moduleName, $arModule) {
	echo "<script type='text/javascript' src='style/default/renderMenu.js'></script>";
	
	echo "<div id='menu'>";

	// Print application
	echo "<div id='app'>Aplikasi: <b>";
	if (strlen($appName) > 38) {
		$dispAppName = substr($appName, 0, 38);
		echo "<span title='$appName'>$dispAppName..</title>";
	} else {
		echo "$appName";
	}
	echo "</b></div>";

	// Print module
	echo "	<div id='module'>Modul: <b>";
	if (strlen($moduleName) > 38) {
		$dispModuleName = substr($moduleName, 0, 38);
		echo "<span title='$moduleName'>$dispModuleName..</title>";
	} else {
		echo "$moduleName";
	}
	echo "</b></div>";

	// Print application change
	echo "<div id='app-change'>";
	echo "<a href='#' id='app-link' onClick='showSelectApp();'>Ganti Aplikasi</a>";
	echo "<select id='app-select' style='display:none; margin-top:-2px; margin-bottom:-2px;' onFocus='focusSelectApp();' onChange='changeApp(\"$appId\", \"$moduleId\");'>";
	echo "<option value='0'>--------</option>";
	foreach ($arApp as $iApp) {
		$id = $iApp["id"];
		$name = $iApp["name"];

		echo "<option value='$id'>$name</option>";
	}
	echo "</select>";
	echo "</div>";

	// Print module change
	echo "<div id='module-change'>";
	echo "<a href='#' id='module-link' onClick='showSelectModule();'>Ganti modul</a>";
	echo "<select id='module-select' style='display:none; margin-top:-2px; margin-bottom:-2px;' onFocus='focusSelectModule();' onChange='changeModule(\"$appId\");'>";
	echo "<option value='0'>--------</option>";
	foreach ($arModule as $iModule) {
		$id = $iModule["id"];
		$name = $iModule["name"];

		echo "<option value='$id'>$name</option>";
	}
	echo "</select>";
	echo "</div>";
	
	// End of menu
	echo "</div>";
}

?>