<?php

/*======================*/
/*                      */
/*    Rendering Menu    */
/*                      */
/*======================*/

function renderMenu($appId, $appName, $arApp, $moduleId, $moduleName, $arModule)
{

	global $User, $uid, $application;


	// var_dump($arModule);
	// die;
	echo "			\n";
	echo "			<!-- Menu application and module -->\n";

	// var_dump($arApp);
	// die;

	// Include renderer javascript
	echo "			<script type='text/javascript' src='style/default/renderMenu.js'></script>\n";



	// End of menu
	// $active = "";

	foreach ($arModule as $iModule) {
		if ($moduleName ==  $iModule["name"] ) {
			$open = "open";
		}
	}

     // var_dump($moduleName);
	echo  "<li class='active nav-item {$open}'>
			<a href='main.php?param=$encodes'>
				<i class='icon-stack-2'></i>
				<span data-i18n='' class='menu-title'>$appName</span>	
			</a>";


	echo '<ul class="menu-content">';
	// var_dump($arModule);
	foreach ($arModule as $iModule) {

		$id = $iModule["id"];
		$name = $iModule["name"];
		$icon = $iModule["icon"];
		// var_dump($iModule["icon"]);
		if ($appId == "aAdmPajakKabSKB") {
			$encode = base64_encode("a=aAdmPajakKabSKB&m={$id}");
		} else {
			$encode = base64_encode("a=aBPHTB&m={$id}");
		}

		$active = '';

		if ($moduleName == $iModule["name"]) {
			$active = 'active';
			$application = $appId;
		}

		echo "<li class='$active'><a href='main.php?param=$encode' class='menu-item'><i class=\"{$icon}\" aria-hidden=\"true\"></i></i>$name</a>
	  </li>
	  
        ";
		// var_dump($active);
	}
	echo '</ul></li>';
}
