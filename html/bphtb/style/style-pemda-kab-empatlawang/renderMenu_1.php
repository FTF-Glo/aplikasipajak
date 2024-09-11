<?php

/*======================*/
/*                      */
/*    Rendering Menu    */
/*                      */
/*======================*/

function renderMenu($appId, $appName, $arApp, $moduleId, $moduleName, $arModule) {
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
	echo "					<select id='module-select' style='display:none; margin-top:-2px; margin-bottom:-2px;' onFocus='focusSelectModule();' onChange='changeModule(\"$appId\");'>\n";
	echo "						<option value='0'>--------</option>\n";
	foreach ($arModule as $iModule) {
		$id = $iModule["id"];
		$name = $iModule["name"];

		echo "						<option value='$id'>$name</option>\n";
	}
	echo "					</select>\n";
	echo "				</div>\n";
	
	// End of menu
	echo "			</div>\n";
}

?>