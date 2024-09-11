<?php

if ($User == null) {
	die();
}

// Mode management Role
if ($subMode == "i") {
	// Submode insert
	if ($action) {
		// get parameter
		insertAuthAction();
	} else {
		insertAuth();
	}
} else if ($subMode == "e") {
	// Submode edit
	if ($action) {
		// get parameter
		editAuthAction();
	} else {
		editAuth();
	}
} else if ($subMode == "d") {
	// Submode delete
	deleteAuth();
} else if ($subMode == "c") {
	// Submode activate
	activateAuth();
} else if ($subMode == "t") {
	// Submode deactivate
	deactivateAuth();
} else if ($subMode == "u") {
	// Submode move up
	moveUpAuth();
} else if ($subMode == "w") {
	// Submode move down
	moveDownAuth();
} else {
	// List all
	listAuth();
}

function listAuth() {
	global $Setting;
	$bOK = false;
	
	if ($Setting) {
?>
<script type='text/javascript'>
function confirmDeleteAuth(name, id) {
	var ans = confirm("Delete authenticator '" + name + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=t&sm=d&i=' + id);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php
	
		echo "<div class='subTitle'>Custom Authentication</div>";
		echo "<div class='spacer10'></div>";
		
		$url64 = base64_encode("setting=1&m=t&sm=i");
		echo "<a href='main.php?param=$url64'>Add new authenticator</a>";
		echo "<div class='spacer10'></div>";

		echo "<table class=\"table table-bordered\" cellspacing='3px' cellpadding='3px'>";
		echo "<tr>";
		echo "<th>Option</th>";
		echo "<th>Class Name</th>";
		echo "<th>Active</th>";
		echo "<th>Order</th>";
		echo "</tr>";
		
		$arAuth = $Setting->GetAllAuth(false);
		$i = 0;
		$count = count($arAuth);
		foreach ($arAuth as $auth) {
			$id = $auth["id"];
			$class = $auth["class"];
			$active = $auth["active"];
			$order = $auth["order"];
			
			echo "<tr>";
			echo "<td align='center' valign='top'>";
			echo "&nbsp;";
			
			// edit
			$url64 = base64_encode("setting=1&m=t&sm=e&i=$id");
			echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit' title='Edit'></img></a>";
			echo "&nbsp;";
			
			// delete
			echo "<a href='#' onClick='confirmDeleteAuth(\"$class\", \"$id\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>";
			echo "&nbsp;";
			
			// activate/deactivate
			if ($active == 1) {
				$url64 = base64_encode("setting=1&m=t&sm=t&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/delete.png' alt='Deactivate Authenticator' title='Deactivate Authenticator'></img></a>";
			} else {
				$url64 = base64_encode("setting=1&m=t&sm=c&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/accept.png' alt='Activate Authenticator' title='Activate Authenticator'></img></a>";
			}
			echo "&nbsp;";
			
			// DEPRECATED: cannot move up/down, use Edit instead
			/*
			// move up
			if ($i == 0) {
				echo "<img border='0' src='image/icon/up_16_gs.png' alt='Move Up Authenticator' title='Move Up Authenticator'></img>";
			} else {
				$url64 = base64_encode("setting=1&m=t&sm=u&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/up_16.png' alt='Move Up Authenticator' title='Move Up Authenticator'></img></a>";
			}
			echo "&nbsp;";
			
			// move down
			if ($i == $count - 1) {
				echo "<img border='0' src='image/icon/down_16_gs.png' alt='Move Down Authenticator' title='Move Down Authenticator'></img>";
			} else {
				$url64 = base64_encode("setting=1&m=t&sm=w&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/down_16.png' alt='Move Down Authenticator' title='Move Down Authenticator'></img></a>";
			}
			echo "&nbsp;";
			echo "</td>";
			*/
			
			if ($active == 1) {
				$active = "<span style='color:green; font-weight:bold;'>Active</span>";
			} else {
				$active = "-";
			}
			
			echo "<td valign='top'>$class&nbsp;</td>";
			echo "<td valign='top' align='center'>$active&nbsp;</td>";
			echo "<td valign='top' align='center'>$order&nbsp;</td>";
			echo "</tr>";
			
			$i++;
		}
		
		echo "</table>";
		echo "</form>";
	}
	
	echo "<div class='spacer20'></div>";
	
	liveShow();
}

function insertAuth() {
	global $Setting, $mode;
	
	$url64 = base64_encode("setting=1&m=t&sm=i&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<div class='subTitle'>Insert new auth</div>";
	echo "<div class='spacer10'></div>";
	
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Class Name</td>";
	echo "<td valign='top' align='center' width='20px'>:</td>";
	echo "<td valign='top'><input type='text' id='className' name='className' length='30' size='20' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='5px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Active</td>";
	echo "<td valign='top' align='center' width='20px'>:</td>";
	echo "<td valign='top'><input type='checkbox' id='active' name='active'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	$url64 = base64_encode("setting=1&m=$mode");
	echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	echo "&nbsp;&nbsp;";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function insertAuthAction() {
	global $Setting, $className, $active;
	
	if ($active == "on") {
		$active = 1;
	} else {
		$active = 0;
	}
	
	$bOK = $Setting->InsertAuth($className, $active);
	if ($bOK) {
		echo "<div>Authenticator successfully inserted...</div>";
	} else {
		echo "<div>Authenticator fail to inserted...</div>";
	}
	$url64 = base64_encode("setting=1&m=t");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function deleteAuth() {
	global $Setting, $id;
	
	$bOK = $Setting->DeleteAuth($id);
	if ($bOK) {
		echo "<div>Authenticator successfully deleted...</div>";
	} else {
		echo "<div>Authenticator fail to deleted...</div>";
	}
	$url64 = base64_encode("setting=1&m=t");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function editAuth() {
	global $Setting, $id, $mode;
	
	$arAuth = $Setting->GetAuth($id);
	if ($arAuth != null) {
		$className = $arAuth["class"];
		$active = $arAuth["active"];
		$order = $arAuth["order"];
	
		$url64 = base64_encode("setting=1&m=t&sm=e&a=1&i=$id");
		echo "<form method='POST' action='main.php?param=$url64'>";
		echo "<div class='subTitle'>Insert new auth</div>";
		echo "<div class='spacer10'></div>";
		
		echo "<table class='transparent'>";
		echo "<tr>";
		echo "<td valign='top'>Class Name</td>";
		echo "<td valign='top' align='center' width='20px'>:</td>";
		echo "<td valign='top'><input type='text' id='className' name='className' length='30' size='20' value='$className' autocomplete='off'></input></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td colspan='3' height='5px'>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign='top'>Active</td>";
		echo "<td valign='top' align='center' width='20px'>:</td>";
		echo "<td valign='top'><input type='checkbox' id='active' name='active'";
		if ($active == 1) {
			echo " checked";
		}
		echo "></input></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign='top'>Order</td>";
		echo "<td valign='top' align='center' width='20px'>:</td>";
		echo "<td valign='top'><input type='text' id='order' name='order' length='10' size='10' value='$order' autocomplete='off'></input></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td colspan='3' height='10px'>";
		echo "</tr>";
		echo "<tr>";
		echo "<td colspan='3' align='center'>";
		$url64 = base64_encode("setting=1&m=$mode");
		echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
		echo "&nbsp;&nbsp;";
		echo "<input type='submit' value='Save'></input>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "</form>";
	}
}

function editAuthAction() {
	global $Setting, $id, $className, $active, $order;
	
	if ($active == "on") {
		$active = 1;
	} else {
		$active = 0;
	}
	
	$bOK = $Setting->EditAuth($id, $className, $active, $order);
	if ($bOK) {
		echo "<div>Authenticator successfully edited...</div>";
	} else {
		echo "<div>Authenticator fail to edited...</div>";
	}
	$url64 = base64_encode("setting=1&m=t");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function activateAuth() {
	global $Setting, $id;
	
	if ($id == "") {
		return false;
	}
	
	$arAuth = $Setting->GetAuth($id);
	$className = $arAuth["class"];
	$order = $arAuth["order"];
	
	// Decline module
	$bOK = $Setting->EditAuth($id, $className, 1, $order);
	if ($bOK) {
		echo "<div>Authenticator successfully deactivated...</div>";
	} else {
		echo "<div>Authenticator fail to deactivated...</div>";
	}
	$url64 = base64_encode("setting=1&m=t");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function deactivateAuth() {
	global $Setting, $id;
	
	if ($id == "") {
		return false;
	}
	
	$arAuth = $Setting->GetAuth($id);
	$className = $arAuth["class"];
	$order = $arAuth["order"];
	
	// Decline module
	$bOK = $Setting->EditAuth($id, $className, 0, $order);
	if ($bOK) {
		echo "<div>Authenticator successfully deactivated...</div>";
	} else {
		echo "<div>Authenticator fail to deactivated...</div>";
	}
	$url64 = base64_encode("setting=1&m=t");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function moveDownAuth() {
	global $Setting, $id;
	
	if ($id == "") {
		return false;
	}
	
	$arAuth = $Setting->GetAuth($id);
	$className = $arAuth["class"];
	$active = $arAuth["active"];
	$order = $arAuth["order"] + 1;
	
	// Decline module
	$bOK = $Setting->EditAuth($id, $className, $active, $order);
	$url64 = base64_encode("setting=1&m=t");
	echo "<meta http-equiv='REFRESH' content='0;url=main.php?param=$url64'>";
}
function moveUpAuth() {
	global $Setting, $id;
	
	if ($id == "") {
		return false;
	}
	
	$arAuth = $Setting->GetAuth($id);
	$className = $arAuth["class"];
	$active = $arAuth["active"];
	$order = $arAuth["order"] - 1;
	
	// Decline module
	$bOK = $Setting->EditAuth($id, $className, $active, $order);
	$url64 = base64_encode("setting=1&m=t");
	echo "<meta http-equiv='REFRESH' content='0;url=main.php?param=$url64'>";
}

function liveShow() {
	global $Setting;
	
	echo "<h4>Example Login Page</h4>";
	
	$arAuth = $Setting->GetAllAuth(true);
	if ($arAuth != null) {
	
		require_once("inc/auth/AuthBase.php");
		echo "<table cellpadding='3' align='center' border='0'>";
		echo "<tr>";
		echo "<td colspan='3'></td>";
		echo "</tr>";
		
		foreach ($arAuth as $auth) {
			// class name
			$className = $auth["class"];
			$fileName = "inc/auth/" . $className . ".php";
			
			// exist?
			if (file_exists($fileName)) {
				include($fileName);

				$authClass = new $className();

				// get render input
				$input = $authClass->element();
				
				foreach ($input as $key2 => $value2) {
					$label = $value2["label"];
					$id = $value2["id"];
					$element = $value2["input"];
					$td = "";
					if (isset($value2["td"])) {
						$td = $value2["td"];
					}
					
					echo "<tr>";
					echo "<td $td>$label</td>";
					echo "<td width='5px'>&nbsp;</td>";
					echo "<td>$element</td>";
					echo "</tr>";
				}
			} else {
				echo "<tr>";
				echo "<td colspan='3' height='5px'><div class='error'>Class '$className' doesn't exist</div></td>";
				echo "</tr>";
			}
			
			echo "<tr>";
			echo "<td colspan='3' height='5px'></td>";
			echo "</tr>";
		}
		echo "</table>";
	} else {
		echo "<div class='error'>WARNING: No active authenticator</div>";
	}
}

?>
