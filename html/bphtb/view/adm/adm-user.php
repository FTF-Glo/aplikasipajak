<script type='text/javascript'>			
function clickManagement(obj) {
	var admin = document.getElementById('isAdmin');
	var supervisor = document.getElementById('isSupervisor');
	
	if (obj.id == admin.id) {
		// Supervisor jadi off, jika admin on
		if (obj.checked) {
			supervisor.checked = false;
		}
	} else if (obj.id == supervisor.id) {
		// Admin jadi off, jika supervisor on
		if (obj.checked) {
			admin.checked = false;
		}
	}
}
</script>
<?php

// Prevent direct access to file
if ($User == null) {
	die();
}

// Mode management User
if ($subMode == "i") {
	// Submode insert
	if ($action) {
		// get parameter
		insertUserAction();
	} else {
		insertUser();
	}
} else if ($subMode == "e") {
	// Submode edit
	if ($action) {
		// get parameter
		editUserAction();
	} else {
		editUser();
	}
} else if ($subMode == "d") {
	// Submode delete
	deleteUser();
} else if ($subMode == "l") {
	// Submode list
	listApp();
} else if ($subMode == "c") {
	// Submode change role
	changeRole();
} else if ($subMode == "b") {
	// Submode change block user
	changeBlockUser(true);
} else if ($subMode == "u") {
	// Submode change unblock user
	changeBlockUser(false);
} else {
	// List all
	$bOK = printUserSetting();
	if (!$bOK) {
		return;
	}
}

function printUserSetting() {
	global $cData, $data, $json, $User, $Setting;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
				
		echo "<div class='subTitle'>List users</div>";
		echo "<div class='spacer10'></div>";
		
		$url64 = base64_encode("setting=1&m=u&sm=i");
		echo "<a href='main.php?param=$url64'>Add new user</a>";
		echo "<div class='spacer10'></div>";
		
		// print table User
		$bOK = $Setting->GetUser($UserIds);
		if ($bOK) {
			// var_dump($UserIds);
?>
<script type='text/javascript'>			
function confirmDeleteUser(name, id) {
	var ans = confirm("Delete user '" + name + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=u&sm=d&i=' + id);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php	
			echo "<table cellspacing='3px' cellpadding='3px'>";
			echo "<tr>";
			echo "<th>Option</th>";
			echo "<th>Id</th>";
			echo "<th>Name</th>";
			echo "<th>Blocked</th>";
			echo "<th>Management</th>";
			echo "<th>Multiple Login</th>";
			echo "</tr>";
			
			foreach ($UserIds as $UserId) {
				$id = $UserId["id"];
				$uid = $UserId["uid"];
				$isAdmin = $UserId["isAdmin"];
				$blocked = $UserId["blocked"];
				$multLogin = $UserId["multLogin"];
				
				// NEW: pakai manageBit
				$manageBit = $isAdmin + 0;
				
				echo "<tr>";
				echo "<td align='center'>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=u&sm=e&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit' title='Edit'></img></a>";
				echo "&nbsp;";
				echo "<a href='#' onClick='confirmDeleteUser(\"$uid\", \"$id\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=u&sm=l&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/list-items.gif' alt='List role' title='List role'></img></a>";
				echo "&nbsp;";
				
				// NEW: unblok / block user
				// to block image --> block_16.png
				// to unblock image --> accept.png
				if ($blocked == 0) {
					$url64 = base64_encode("setting=1&m=u&sm=b&i=$id");
					echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/block_16.png' alt='Block user' title='Block user'></img></a>";
				} else {
					$url64 = base64_encode("setting=1&m=u&sm=u&i=$id");
					echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/accept.png' alt='Unblock user' title='Unblock user'></img></a>";
				}
				echo "&nbsp;";
				
				echo "</td>";
				echo "<td>$id&nbsp;</td>";
				echo "<td>$uid&nbsp;</td>";
				if ($blocked == 0) {
					echo "<td align='center'>-</td>";
				} else {
					echo "<td align='center'><img src='image/icon/block_16.png' alt='Blocked' /></td>";
				}
				if (($manageBit & 1) == 1) {
					echo "<td align='center'>Admin</td>";
				} else if (($manageBit & 10) == 10) {
					echo "<td align='center'>Supervisor</td>";
				} else {
					echo "<td align='center'>-</td>";
				}
				
				// NEW: Multiple login
				echo "<td align='center'>";
				if ($multLogin == 1) {
					echo "Yes";
				} else {
					echo "-";
				}
				echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}
	return $bOK;
}

function deleteUser() {
	global $Setting, $id;
	$bOK = $Setting->DeleteUser($id);
	if ($bOK) {
		echo "<div>User successfully deleted...</div>";
	} else {
		echo "<div>User fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=u");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function insertUser() {
	global $Setting, $idUser, $nameUser, $isAdmin, $mode, $multLogin;
	
	if ($idUser == "") {
		// initial insert, ambil next id 'u?'
		$idUser = "u" . $Setting->GetNextUserId();
	}

	$idUser = htmlentities($idUser, ENT_QUOTES);
	$nameUser = htmlentities($nameUser, ENT_QUOTES);
	
	echo "<div class='subTitle'>Insert new user</div>";
	echo "<div class='spacer10'></div>";
		
	$url64 = base64_encode("setting=1&m=u&sm=i&a=1");
	echo "<form method='POST' action='main.php?param=$url64' onSubmit='return cekConfirmPassword(\"pwdUser\", \"pwdUser2\");'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='idUser' name='idUser' length='30' size='20' value='$idUser' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameUser' name='nameUser' length='100' size='30' value='$nameUser' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Management</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<div>";
	echo "<label>";
	echo "<input type='checkbox' id='isAdmin' name='isAdmin' value='1' onClick='clickManagement(this)'>&nbsp;Admin</input>";
	echo "</label>";
	echo "<input type='text' style='visibility:hidden'></input>";
	echo "</div>";
	echo "<div>";
	echo "<label>";
	echo "<input type='checkbox' id='isSupervisor' name='isSupervisor' value='1' onClick='clickManagement(this)'>&nbsp;Supervisor</input>";
	echo "</label>";
	echo "<input type='text' style='visibility:hidden'></input>";
	echo "</div>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Multiple Login</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='checkbox' id='multLogin' name='multLogin' ";
	if ($multLogin) {
		echo "checked";
	}
	echo "></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='password' id='pwdUser' name='pwdUser' length='100' size='30' value='' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Confirm Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='password' id='pwdUser2' name='pwdUser2' length='100' size='30' value='' autocomplete='off'></input></td>";
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

function insertUserAction() {
	global $Setting, $idUser, $nameUser, $pwdUser, $isAdmin, $isSupervisor, $multLogin;
	
	$bOK = $Setting->InsertUser($idUser, $nameUser, $pwdUser, $isAdmin, $isSupervisor, $multLogin);
	
	$idUser = htmlentities($idUser, ENT_QUOTES);
	$nameUser = htmlentities($nameUser, ENT_QUOTES);
	$n = strlen($pwdUser);
	$pwdUserDisp = "";
	for ($i = 0; $i < $n; $i++) {
		$pwdUserDisp .= "&bull;";
	}
	
	$url64 = base64_encode("setting=1&m=u&sm=i");
	echo "<form method='POST' action='main.php?param=$url64' id='formUser'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert User";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idUser' name='idUser' value='$idUser' autocomplete='off'></input>$idUser</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameUser' name='nameUser' value='$nameUser' autocomplete='off'></input>$nameUser</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>$pwdUserDisp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Management</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	$adm = false;
	if ($isAdmin) {
		echo "<li>Admin</li><br />";
		$adm = true;
	}
	if ($isSupervisor) {
		echo "<li>Supervisor</li><br />";
		$adm = true;
	}
	if (!$adm) {
		echo "-";
	}
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Multiple Login</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	if ($multLogin) {
		echo "Yes";
	} else {
		echo "No";
	}
	echo "<input type='hidden' id='multLogin' name='multLogin' value='$multLogin'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formUser\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=u");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
	echo "</form>";
}

function editUser() {
	global $Setting, $id;
	
	$arUser = $Setting->GetUserDetail($id);
	$idUser = $arUser["id"];
	$nameUser = $arUser["uid"];
	$isAdmin = $arUser["isAdmin"];
	$multLogin = $arUser["multLogin"];
	
	// NEW: manageBit
	$manageBit = $isAdmin + 0;
	
	// quote fix
	$idUser = htmlentities($idUser, ENT_QUOTES);
	$nameUser = htmlentities($nameUser, ENT_QUOTES);
	
	echo "<div class='subTitle'>Edit user '$nameUser'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=u&sm=e&a=1");
	echo "<form method='POST' action='main.php?param=$url64' onSubmit='return cekConfirmPassword(\"pwdUser\", \"pwdUser2\")'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idUser' name='idUser' value='$idUser' autocomplete='off'></input>$idUser</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameUser' name='nameUser' length='100' size='30' value='$nameUser' autocomplete='off'></input></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td valign='top'>Management</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<div>";
	echo "<label>";
	echo "<input type='checkbox' id='isAdmin' name='isAdmin' value='1' onClick='clickManagement(this)' ";
	if (($manageBit & 1) == 1) {
		echo "checked='true'";
	}
	echo ">&nbsp;Admin</input>";
	echo "</label>";
	echo "<input type='text' style='visibility:hidden'></input>";
	echo "</div>";
	echo "<div>";
	echo "<label>";
	echo "<input type='checkbox' id='isSupervisor' name='isSupervisor' value='1' onClick='clickManagement(this)' ";
	if (($manageBit & 10) == 10) {
		echo "checked='true'";
	}
	echo ">&nbsp;Supervisor</input>";
	echo "</label>";
	echo "<input type='text' style='visibility:hidden'></input>";
	echo "</div>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Multiple Login</td>";
	echo "<td>:</td>";
	echo "<td>";
	echo "<input type='checkbox' id='multLogin' name='multLogin' ";
	if ($multLogin == 1) {
		echo "checked";
	}
	echo "></input>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td colspan='3' height='25px' valign='bottom'>";
	echo "<div style='font-size:8pt; text-align:center;'><b>for reset password only</b></div>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<input type='password' id='pwdUser' name='pwdUser' length='100' size='30' value='' autocomplete='off'></input>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Confirm Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<input type='password' id='pwdUser2' name='pwdUser2' length='100' size='30' value='' autocomplete='off'></input>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function editUserAction() {
	global $Setting, $idUser, $nameUser, $pwdUser, $isAdmin, $isSupervisor, $multLogin;
	
	$n = strlen($pwdUser);
	$pwdUserDisp = "";
	for ($i = 0; $i < $n; $i++) {
		$pwdUserDisp .= "&bull;";
  }
	
  $bOK = $Setting->EditUser($idUser, $nameUser, $pwdUser, 0, $isAdmin, $isSupervisor, $multLogin);
	
	$url64 = base64_encode("setting=1&m=u&sm=e");
	echo "<form method='POST' action='main.php?param=$url64' id='formUser'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit User";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idUser' name='idUser' value='$idUser' autocomplete='off'></input>$idUser</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameUser' name='nameUser' value='$nameUser' autocomplete='off'></input>$nameUser</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>$pwdUserDisp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Management</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	$adm = false;
	if ($isAdmin) {
		echo "<li>Admin</li><br />";
		$adm = true;
	}
	if ($isSupervisor) {
		echo "<li>Supervisor</li><br />";
		$adm = true;
	}
	if (!$adm) {
		echo "-";
	}
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Multiple Login</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	if ($multLogin) {
		echo "Yes";
	} else {
		echo "No";
	}
	echo "<input type='hidden' id='multLogin' name='multLogin' value='$multLogin'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer10'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formUser\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=u");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
}

function listApp() {
	global $Setting, $User, $id;
	
	$arApp = null;
	$bOK = $Setting->GetApp($arApp);
	$bOK2 = $Setting->GetRole($arRole);
	
	$username = $User->GetUsername($id);
	
	echo "<div class='subTitle'>Application & role from user '$username'</div>";
	echo "<div class='spacer10'></div>";
	
	if ($bOK && $bOK2) {
		// var_dump($arApp);
		
?>
<script type='text/javascript'>
function showSelectRole(index) {
	// alert('selectRole' + index);
	document.getElementById('selectRole' + index).style.display = 'inline';
	document.getElementById('role' + index).style.display = 'none';
	document.getElementById('selectRole' + index).focus();
}

function hideSelectRole(index) {
	document.getElementById('selectRole' + index).style.display = 'none';
	document.getElementById('role' + index).style.display = 'inline';
}

function selectSelectRole(index) {
	// hide select
	var valueSelect = document.getElementById('selectRole' + index).value;
	hideSelectRole(index);
	// removeEvent(document.getElementById('selectRole' + index), 'blur', selectSelectRole);
}

function focusSelectRole(index) {
	// addEvent(document.getElementById('selectRole' + index), 'blur', selectSelectRole);
}

function changeRole(index, user, app, module) {
	var loadingRole = document.getElementById('loadingTextRole');
	if (loadingRole != null) {
		loadingRole.style.display = 'inline';
	}

	var selectRole = document.getElementById('selectRole' + index);
	var role = selectRole.value;
	if (role == '-99') {
		var url = Base64.encode('setting=1&m=r&sm=i&ref=changeRole&u=' + user + '&ar=' + app);
		window.location.href = 'main.php?param=' + url;
	} else if (role != '0') {
		var url = Base64.encode('setting=1&m=u&sm=c&i=' + user + '&ar=' + app + '&r=' + role);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php
		
		echo "<table cellspacing='3px' cellpadding='3px'>";
		echo "<tr>";
		echo "<th>Application</th>";
		echo "<th>Role Module</th>";
		echo "<th>Change</th>";
		echo "</tr>";
		
		$i = 1;
		foreach ($arApp as $app) {
			$appId = $app["id"];
			$appName = $app["name"];
			
			echo "<tr>";
			echo "<td align='center'>$appName&nbsp;</td>";

			echo "<td align='center'>";
			$role = $Setting->GetRoleInApp($id, $appId);
			if ($role) {
				$roleName = $User->GetRoleName($role);
				echo "<span id='role$i' style='color:green; font-weight:bold;'>Granted as $roleName</span>";
			} else {
				echo "<span id='role$i' style='color:red; font-weight:bold;'>Decline</span>";
			}
			echo "<select id='selectRole$i' name='selectRole$i' style='display:none' onFocus='focusSelectRole($i);' onBlur='selectSelectRole($i);' onChange='changeRole($i, \"$id\", \"$appId\");'>";
			echo "<option value='0'>---------</option>";
			echo "<option value='-1'>Decline</option>";
			foreach ($arRole as $rowRole) {
				$rowRoleId = $rowRole["id"];
				$rowRoleName = $rowRole["name"];
				$rowRoleName = htmlentities($rowRoleName);
				echo "<option value='$rowRoleId'>Grant as $rowRoleName</option>";
			}
			
			echo "<option value='-99'>&lt; Insert new role &gt;</option>";
			echo "</select>";
			echo "</td>";
			
			echo "<td align='center'>";
			echo "&nbsp;";
			echo "<a href='#' onClick='showSelectRole($i)'><img border='0' src='image/icon/group_key.png' alt='Change role' title='Change role'></img></a>";
			echo "&nbsp;";
			echo "</td>";
			echo "</tr>";
			
			$i++;
		}
		
		echo "</table>";
	}
}

function changeRole() {
	global $Setting, $id, $role, $ar, $r;
	
	// $appId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	// $roleId = (isset($_REQUEST['r']) ? trim($_REQUEST['r']) : '');
	$appId = $ar;
	$roleId = $r;

	// echo "user = $id<br />";
	// echo "app = $appId<br />";
	// echo "role = $roleId<br />";
	
	$bOK = $Setting->ChangeRole($id, $appId, $roleId);
	if ($bOK) {
		echo "<div>Role successfully changed...</div>";
	} else {
		echo "<div>Role fail to change...</div>";
	}
	$url64 = base64_encode("setting=1&m=u&sm=l&i=$id");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function changeBlockUser($blocked) {
	global $User, $id;
	
	$stBlock = "Unblock";
	if ($blocked) {
		$stBlock = "Block";
	}
	
	$bOK = $User->ChangeBlockUser($id, $blocked);
	if ($bOK) {
		echo "<div>$stBlock user successfully...</div>";
	} else {
		echo "<div>$stBlock user failed...</div>";
	}
	$url64 = base64_encode("setting=1&m=u");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

?>
