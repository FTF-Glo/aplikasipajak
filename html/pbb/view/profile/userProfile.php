<?php
if ($data) {

	if ($mode == "chPass") {
		echo "<div class='spacer10'></div>";
		echo "<div class='subTitle'>Ganti Kata Kunci</div>";
		echo "<div class='spacer5'></div>";
	
		// Change Password
		$changed = false;
		//echo "ac=$ac";
		if (@isset($ac) && $ac == "1") {
			$username = $User->GetUserName($uid);
			//echo "$uid, $username, $pwdUser, $oldPwdUser";
			$bOK = $User->ChangePassword($uid, $username, $pwdUser, $oldPwdUser);
			if ($bOK) {
				echo "<div>Sukses Ganti Kata Kunci</div>";
				$url64 = base64_encode("userProfile=1");
				echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
				echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
				$changed = true;
			} else {
				echo "<div>Gagal Ganti Kata Kunci</div>";
			}
		}

		if (!$changed) {
			echo "<div class='spacer10'></div>";
			$url64 = base64_encode("userProfile=1&m=chPass&ac=1");
			echo "<form method='POST' action='main.php?param=$url64' onSubmit='return cekConfirmPassword(\"pwdUser\", \"pwdUser2\")'>";
			echo "<table class='transparent'>";
			echo "<tr>";
			echo "<td>Kata Kunci Lama</td>";
			echo "<td>:</td>";
			echo "<td><input type='password' id='oldPwdUser' name='oldPwdUser' length='30' size='30' autocomplete='off' value=''></input></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>Kata Kunci Sekarang</td>";
			echo "<td>:</td>";
			echo "<td><input type='password' id='pwdUser' name='pwdUser' length='30' size='30' autocomplete='off' value=''></input></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>Konfirmasi Kata Kunci</td>";
			echo "<td>:</td>";
			echo "<td><input type='password' id='pwdUser2' name='pwdUser2' length='30' size='30' autocomplete='off' value=''></input></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td colspan='3' height='10'></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td colspan='3' align='center'><input type='submit' value='Change'></input></td>";
			echo "</tr>";
			echo "</table>";
			echo "</form>";
		}
	}
	
	else if ($mode == "chLayout") {
		echo "<div class='spacer10'></div>";
		echo "<div class='subTitle'>Ganti Tata Letak</div>";
		echo "<div class='spacer5'></div>";
		
		if (@isset($ac) && $ac == "1") {
			$bOK = $User->setLayoutUser($uid, $styleId);
			// echo "uid = $uid<br />";
			// echo "styleId = $styleId<br />";
			if ($bOK) {
				echo "<div>Sukses Ganti Tata Letak</div>";
				$url64 = base64_encode("userProfile=1&m=chLayout&ac=1");
				echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
				echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
				$changed = true;
			} else {
				echo "<div>Gagal Ganti Tata Letak</div>";
			}
			return;
		}
		
		// Include library
		require_once("inc/lib/xml2array.php");
		
		// Table header
		echo "<table cellspacing='3' cellpadding='3'>";
		echo "<tr>";
		echo "<th>Choose</th>";
		echo "<th width='200px'>Name</th>";
		echo "<th>Creation Date</th>";
		echo "<th>Author</th>";
		echo "<th>Version</th>";
		echo "<th>Screenshot</th>";
		echo "</tr>";
		
		// List style directory
		$dirStyle = "style/";
		$descStyle = "desc.xml";
		if ($handle = opendir($dirStyle)) {
			while (false !== ($dir = readdir($handle))) {
				$descStyleFull = $dirStyle . $dir . "/" . $descStyle;
				if (file_exists($descStyleFull)) {
					// XML
					$arXml = xml2array(file_get_contents($descStyleFull));
					$styleDesc = $arXml["description"];
					$iName = $styleDesc["name"];
					$iDate = $styleDesc["creationDate"];
					$iAuthor = $styleDesc["author"];
					$iVersion = $styleDesc["version"];
					
					// File
					$arFiles = $styleDesc["files"];
					$iScreenshot = null;
					if (@isset($arFiles["screenshot"]) && trim($arFiles["screenshot"]) != "") {
						$iScreenshot = $arFiles["screenshot"];
					}
					
					echo "<tr>";
					echo "<td align='center'>";
					if ("style/$dir" == $MAINstyle["path"]) {
						echo "	-";
					} else {
						$url64 = base64_encode("userProfile=1&m=chLayout&ac=1&styleId=$dir");
						echo "	<a href='main.php?param=$url64'>";
						echo "		<img src='image/icon/accept.png' alt='' title='Choose' />";
						echo "	</a>";
					}
					echo "</td>";
					echo "<td>$iName</td>";
					echo "<td>$iDate</td>";
					echo "<td align='center'>$iAuthor</td>";
					echo "<td align='right'>$iVersion</td>";
					
					// Screenshot
					echo "<td height='62' align='center'>";
					if ($iScreenshot != null) {
						$sShotFull = $dirStyle . $dir . "/" . $iScreenshot;
						if (file_exists($sShotFull)) {
							echo "	<img src='$sShotFull' width='100' height='62' alt='$iName' border='0' />";
						} else {
							echo "	No screenshot";
						}
					} else {
						echo "	No screenshot";
					}
					echo "</td>";
					echo "</tr>";
				}
			}

			closedir($handle);
		}
		
		echo "</table>";
	}
	
	else {
		echo "<div class='spacer10'></div>";
		echo "<div class='subTitle'>Info Pemakai</div>";
		echo "<div class='spacer5'></div>";
		
		$arUser = $Setting->GetUserDetail($uid);
		if ($arUser != null) {
			$username = $arUser["uid"];
			$isAdmin = $arUser["isAdmin"];
			
			$username = htmlentities($username);
			$isAdmin = htmlentities($isAdmin);
		
			echo "<table class='transparent'>";
			echo "<tr>";
			echo "<td>Id</td>";
			echo "<td>:</td>";
			echo "<td>$uid</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>Nama Pemakai</td>";
			echo "<td>:</td>";
			echo "<td>$username</td>";
			echo "</tr>";
			echo "</table>";
		}
	}
}

?>
