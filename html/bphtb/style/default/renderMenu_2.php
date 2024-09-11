<?php

/*======================*/
/*                      */
/*    Rendering Menu    */
/*                      */
/*======================*/

function renderMenu($appId, $appName, $arApp, $moduleId, $moduleName, $arModule) {
global $User, $uid, $application;

	echo "			\n";
	echo "			<!-- Menu application and module -->\n";

	//var_dump($arApp);
	//die;

	// Include renderer javascript
	echo "			<script type='text/javascript' src='style/default/renderMenu.js'></script>\n";
	

	
	// End of menu
		

     echo  "<li class='active nav-item'><a href='main.php?param=$encodes'><i class='icon-stack-2'></i><span data-i18n='' class='menu-title'>$appName</span></a>";
      
          
			echo '<ul class="menu-content">';
		foreach ($arModule as $iModule) {
		
		$id = $iModule["id"];
		$name = $iModule["name"];
		if($appId == "aAdmPajakKabSKB"){
			$encode = base64_encode("a=aAdmPajakKabSKB&m={$id}");
		}else{
			$encode = base64_encode("a=aBPHTB&m={$id}");
		}
		
		$active ='';
		
		if($moduleName == $iModule["name"]){
						$active = 'active';
						$application = $appId;
						
		}
		
		echo "<li class='$active'><a href='main.php?param=$encode' class='menu-item'>$name</a>
	  </li>
	  
        ";
	
	}
	echo '</ul></li>';
        
}


?>