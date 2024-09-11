<?php

/*======================*/
/*                      */
/*    Rendering Menu    */
/*                      */
/*======================*/

//

function initSession()
{
	global $DBLink, $User, $uid, $application;
	$uname = $User->GetUserName($uid);
	$role = $User->GetUserRole($uid, $application);

	if ($role == 'rmPatdaWp') {
		$query2 = "SELECT * FROM CENTRAL_USER WHERE CTR_U_UID='{$uname}' OR CTR_U_ID='{$uname}'";
		$res2 = mysqli_query($DBLink, $query2);
		$d2 = mysqli_fetch_assoc($res2);
		$uname = $d2['CTR_U_ID'];
		$uid = $d2['CTR_U_ID'];

		$query = "SELECT * FROM PATDA_WP WHERE CPM_USER='{$uname}'";
		$res = mysqli_query($DBLink, $query);
		$d = mysqli_fetch_assoc($res);

		$_SESSION['npwpd'] = isset($d['CPM_NPWPD']) ? preg_replace("/[^A-Za-z0-9 ]/", '', $d['CPM_NPWPD']) : "";
		$_SESSION['uname'] = $uname;
		$_SESSION['role'] = $role;
	} else {
		$_SESSION['npwpd'] = '';
		$_SESSION['uname'] = $uname;
		$_SESSION['role'] = $role;
	}

	$_SESSION['_success'] = isset($_SESSION['_success']) ? $_SESSION['_success'] : '';
	$_SESSION['_error'] = isset($_SESSION['_error']) ? $_SESSION['_error'] : '';
}

function renderMenu($appId, $appName, $arApp, $moduleId, $moduleName, $arModule)
{
	global $User, $uid, $application;
	initSession($uid);

	// Include renderer javascript
	echo "			<script type='text/javascript' src='style/default/renderMenu.js'></script>\n";

	function decodeString() {
		// Decode the base64 encoded string
		$requestUri = $_SERVER['REQUEST_URI'];
		$queryStart = strpos($requestUri, 'main.php');

			if ($queryStart !== false) {
			return substr($requestUri, $queryStart);
		}

	}
	// var_dump(decodeString());die;
	function getArrPajakAktif($uid)
	{
		global $DBLink, $User;
		//$uid = $User->GetUserName($uid);
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
	if ($role == "rmPatdaWp") {
		/*get jenis pajak apa saja yang aktif pada wp*/
		$arr_pajak_aktif = getArrPajakAktif($uid);
	}


	if ($application == "aPatda") {
		$newMenu = array();
		foreach ($arModule as $iModule) {

			$id = $iModule["id"];
			$name = $iModule["name"];
			$icon = $iModule["icon"];
			$arrName = explode("-", $name);
			$groupName = $arrName[0];
			$newMenu[$groupName][] = $iModule;
			// $newMenu[$groupName][] = $iModule;
		}
		// var_dump($newMenu[$groupName]);die;

		echo '<div data-scroll-to-active="true" class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" style="color:black;">
		<div class="main-menu-content menu-accordion">
		  <ul id="main-menu-navigation" data-menu="menu-navigation" class="navigation navigation-main">';
		//   $group = array();
		foreach ($newMenu as $group => $menus) {

		// foreach ($newMenu as $key => $icon) {

			// print_r($newMenu[$group]['0']['icon']);die;
			// $group = array($group);
			
			echo "<li  class='active nav-item'><a href='#'>{$newMenu[$group]['0']['icon']}<span data-i18n='' class='menu-title' style='color:#787878 !important;'>{$group}</span></a>";
		// }
			echo "</span></a><ul class='menu-content'>";
			foreach ($menus as $key => $iModule) {
				// var_dump($key);
				$id = $iModule["id"];
				$name = $iModule["name"];
				$encode = '';
				$name = str_replace($group . '-', '&rsaquo;', $name);
				// var_dump($group);
				// echo "<option value='$id'>$name</option>\n";
				$encode = base64_encode("a=aPatda&m={$id}");
				$active = '';

				if ($moduleName == $iModule["name"]) {
					$active = 'active';
				}

				$rest = substr($id, -1);
				$rest2 = substr($id, 0, -1);

				if ($role == "rmPatdaWp" && $rest2 == "mPatdaPelapor") {
					foreach ($arr_pajak_aktif as $jps) {


						if ($rest == $jps) {
							echo "<li class='$active'><a href='main.php?param=$encode' class='menu-item'>$name</option></a></li>";
						}
					}
				} else {
					// var_dump($name );
	
					
					if ($group === 'Pelayanan Pajak ') {
						if ($key < 1) {
							echo '
									<li class=" nav-item has-sub hover">
										  <a href="#">
										  <i class="icon-stack-2"></i><span data-i18n="" class="menu-title">PBJT</span></a>
										  <ul class="menu-content" style="background-color: #e7e7e7;">';

										  $a = decodeString('YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yMg');
							/* loopingna ini di pake kalo menunya mau dynamis dari database */

							//   foreach ($menus as $key => $iModule2) {
							// 	$id2 = $iModule2["id"];
							// 	$name2 = $iModule2["name"];
							// 	$encode2 ='';
							// 	$name2 = str_replace($group.'-','&rsaquo;',$name2);
							// 	if ($iModule2["name"] == 'Pelayanan Pajak - Penerangan Jalan' || $iModule2["name"] == 'Pelayanan Pajak - Parkir' || $iModule2["name"] =='Pelayanan Pajak - Hotel' ||  $iModule2["name"] =='Pelayanan Pajak - Hiburan' ||  $iModule2["name"] == 'Pelayanan Pajak - Makanan dan/atau Minuman') {
							// echo "<li class='$active'><a href='main.php?param=$encode' class='menu-item'>$name</option></a></li>";
							// 	}
							//  }

						

							$hiburan = 'main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yMg==';
							$hotel = 'main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yMw==';
							$resoran = 'main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yOA==';
							$parkir = 'main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yNQ==';
							$listrik = 'main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yNp==';
							if(decodeString() == $hiburan ){
								$act = 'active';
							}else if(decodeString() == $hotel){
								$act2 = 'active';
							}elseif (decodeString() == $resoran) {
								$act3 = 'active';
							}elseif (decodeString() == $parkir) {
								$act4 = 'active';
							}elseif (decodeString() == $listrik) {
								$act5 = 'active';
							}
							echo " <li class='$act'><a href='main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yMg==' class='menu-item'>Jasa Kesenian dan Hiburan</option></a></li>";
							echo " <li class='$act2'><a href='main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yMw==' class='menu-item'>Jasa Perhotelan</option></a></li>";
							echo " <li class='$act3'><a href='main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yOA==' class='menu-item'>Makanan dan/atau Minuman</option></a></li>";
							echo " <li class='$act4'><a href='main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yNQ==' class='menu-item'>Jasa Parkir</option></a></li>";
							echo " <li class='$act5'><a href='main.php?param=YT1hUGF0ZGEmbT1tUGF0ZGFQZWxheWFuYW5QZWxhcG9yNp==' class='menu-item'>Tenaga Listrik</option></a></li>";
							echo "</ul>
									  </li>";
						}
						if ($iModule["name"] == 'Pelayanan Pajak - Air Bawah Tanah' || $iModule["name"] == 'Pelayanan Pajak - Reklame' || $iModule["name"] == 'Pelayanan Pajak - Mineral Non Logam dan Batuan' || $iModule["name"] == 'Pelayanan Pajak - Sarang Walet') {
							echo "<li class='$active'><a href='main.php?param=$encode' class='menu-item'>$name</option></a></li>";
						}
					} else {
						echo "<li class='$active'><a href='main.php?param=$encode' class='menu-item'>$name</option></a></li>";
					}

					// echo $name
				}
			}
			echo "</ul>";
		}
		echo "</li>";
	} else {

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
	echo "</ul>";
	echo "</div>
  </div>";
}
