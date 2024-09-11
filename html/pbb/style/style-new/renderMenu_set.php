<?php

/*======================*/
/*                      */
/*    Rendering Menu    */
/*                      */
/*======================*/

function renderMenu_ori($appId, $appName, $arApp, $moduleId, $moduleName, $arModule)
{
	$par = null;
	if (isset($_GET["param"])) {
		$par = $_GET["param"];
	}
	//echo "<script type='text/javascript' src='style/style-new/renderMenu.js'></script>";
	echo '<!-- sidebar menu: : style can be found in sidebar.less -->
		<ul class="sidebar-menu" data-widget="tree">
			<!-- li class="header">NAVIGATION</li -->
			<li ' . ($par == null ? 'class="active"' : '') . '>
				<a href="main.php">
					<i class="fa fa-dashboard"></i> <span>DASHBOARD</span>
				</a>
			</li>';
    // Tambahkan menu baru dengan 3 tingkat submenu di sini
    echo '<li class="treeview">
				<a href="#">
					<i class="fa fa-cogs"></i>
					<span>Menu Baru</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				</a>
        <ul class="treeview-menu">
            <li class="treeview">
                <a href="#"><i class="fa fa-circle-o"></i> Submenu Level 2
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-dot-circle-o"></i> Submenu Level 3.1</a></li>
                    <li><a href="#"><i class="fa fa-dot-circle-o"></i> Submenu Level 3.2</a></li>
                </ul>
            </li>
            <li><a href="#"><i class="fa fa-circle-o"></i> Submenu Level 2 Lainnya</a></li>
        </ul>
    </li>';
	foreach ($arApp as $iApp) {
		$id = $iApp["id"];
		$name = $iApp["name"];

		// aldes add,if get "param" not exists, then not active 
		echo '<li class="treeview ' . ($par && $appId == $id ? 'active' : '') . '">
				<a href="#">
					<i class="fa ' . ($par && $appId == $id ? 'fa-folder-open' : 'fa-folder') . '"></i>
					<span>' . $name . '</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				</a>
				
		';



		if (isset($arModule[$id]) && count($arModule[$id]) > 0 && $arModule[$id] != null) {
			echo '<ul class="treeview-menu">';
			foreach ($arModule[$id] as $iModule) {
				$moduleid = $iModule["id"];
				$modulename = $iModule["name"];
				if ($modulename != 'Dashboard') {
					$url64 = base64_encode("a=" . $id . "&m=" . $moduleid);
					// aldes add,if get "param" not exists, then not active 
					$isMenuActive = ($par && $appId == $id && $moduleid == $moduleId);
					echo '<li ' . ($isMenuActive ? 'class="active"' : '') . '><a href="main.php?param=' . $url64 . '"><i class="fa ' . ($isMenuActive ? 'fa-file' : 'fa-file-o') . '"></i> ' . $modulename . '</a></li>';
				}
			}

			echo '</ul>';
		}

		echo '</li>';
	}

	echo '</ul>
		</section>
		<!-- /.sidebar -->
	</aside>';
}

function renderMenu($appId, $appName, $arApp, $moduleId, $moduleName, $arModule)
{
	$par = null;
	if (isset($_GET["param"])) {
		$par = $_GET["param"];
	}
	//echo "<script type='text/javascript' src='style/style-new/renderMenu.js'></script>";
	echo '<!-- sidebar menu: : style can be found in sidebar.less -->
		<ul class="sidebar-menu" data-widget="tree">
			<!-- li class="header">NAVIGATION</li -->
			<li ' . ($par == null ? 'class="active"' : '') . '>
				<a href="main.php">
					<i class="fa fa-dashboard"></i> <span>DASHBOARD</span>
				</a>
			</li>';
   
   
	foreach ($arApp as $iApp) {
		$id = $iApp["id"];
		$name = $iApp["name"];

		// aldes add,if get "param" not exists, then not active 
		echo '<li class="treeview ' . ($par && $appId == $id ? 'active' : '') . '">
				<a href="#">
					<i class="fa ' . ($par && $appId == $id ? 'fa-folder-open' : 'fa-folder') . '"></i>
					<span>' . $name . '</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				</a>
		';
		if ($name == "MENU UTAMA") {
				
			if (isset($arModule[$id]) && count($arModule[$id]) > 0 && $arModule[$id] != null) {
				echo ' <ul class="treeview-menu">';
				$submenus = [];
				foreach ($arModule[$id] as $iModule) {
					$submenu = $iModule["submenu"];
					if (!isset($submenus[$submenu])) {
						$submenus[$submenu] = [];
					}
					$submenus[$submenu][] = $iModule;
				}
				foreach ($submenus as $submenu => $modules) {
					$isSubmenuActive = false;
					foreach ($modules as $iModule) {
						if ($par && $appId == $id && $iModule["id"] == $moduleId) {
							$isSubmenuActive = true;
							break;
						}
					}
				echo '	<li class="treeview ' . ($isSubmenuActive ? 'active menu-open' : '') . '">
							<a href="#"><i class="fa fa-circle-o"></i> '. $submenu .'
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu submenu">';
							foreach ($modules as $iModule) {
								$moduleid = $iModule["id"];
								$modulename = $iModule["name"];
								$icon = $iModule["icon"];
								
								if ($modulename != 'Dashboard') {
									$url64 = base64_encode("a=" . $id . "&m=" . $moduleid);
									// aldes add,if get "param" not exists, then not active 
									$isMenuActive = ($par && $appId == $id && $moduleid == $moduleId);
									echo '<li ' . ($isMenuActive ? 'class="active"' : '') . '><a href="main.php?param=' . $url64 . '"><i class="fa ' . ($isMenuActive ? $icon : $icon) . '"></i> ' . $modulename . '</a></li>';
								}
							}
						echo '</ul> 
						</li> ';
				}
				echo '</ul>';
			}
		}else{

			if (isset($arModule[$id]) && count($arModule[$id]) > 0 && $arModule[$id] != null) {
				echo '<ul class="treeview-menu">';
				foreach ($arModule[$id] as $iModule) {
					$moduleid = $iModule["id"];
					$modulename = $iModule["name"];
					$icon = $iModule["icon"];
					if ($modulename != 'Dashboard') {
						$url64 = base64_encode("a=" . $id . "&m=" . $moduleid);
						// aldes add,if get "param" not exists, then not active 
						$isMenuActive = ($par && $appId == $id && $moduleid == $moduleId);
						echo '<li ' . ($isMenuActive ? 'class="active"' : '') . '><a href="main.php?param=' . $url64 . '"><i class="fa ' . ($isMenuActive ?  $icon :  $icon) . '"></i> ' . $modulename . '</a></li>';
					}
				}

				echo '</ul>';
			}
		}

		echo '</li>';
	}

	echo '</ul>
		</section>
		<!-- /.sidebar -->
	</aside>';
}

/*echo "<div id='menu'>";

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
	echo " <div id='module'>Modul: <b>";
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
}*/
