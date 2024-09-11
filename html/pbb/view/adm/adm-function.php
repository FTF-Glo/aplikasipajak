<?php

// Prevent direct access to file
if ($User == null) {
	die();
}

// Mode management Func
if ($subMode == "i") {
	// Submode insert
	if ($action) {
		// get parameter
		insertFuncAction();
	} else {
		insertFunc();
	}
} else if ($subMode == "e") {
	// Submode edit
	if ($action) {
		// get parameter
		editFuncAction();
	} else {
		editFunc();
	}
} else if ($subMode == "d") {
	// Submode delete
	deleteFunc();
} else {
	// List all
	$bOK = printFuncSetting();
	if (!$bOK) {
		return;
	}
}

?>
<script type='text/javascript'>
var winImage = null;

function showImageList() {
	if (!winImage) {
		// if (winImage.closed) {
		winImage = window.open(
			"view/adm/showImage.php",
			"Image List", 
			"toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1, width=300, height=500");
		// }
	} else if (winImage.closed) {
		winImage = window.open(
			"view/adm/showImage.php",
			"Image List", 
			"toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1, width=300, height=500");
	} else {
		winImage.focus();
	}
}
</script>
<?php

function printFuncSetting() {
	global $cData, $data, $json, $User, $Setting;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
				
		echo "<div class='subTitle'>List functions</div>";
		echo "<div class='spacer10'></div>";
		
		$url64 = base64_encode("setting=1&m=f&sm=i");
		echo "<a href='main.php?param=$url64'>Add new function</a>";
		echo "<div class='spacer10'></div>";
			
		// print table Func
		$bOK = $Setting->GetFunction($FuncIds);
		if ($bOK) {
			// var_dump($FuncIds);
?>
<script type='text/javascript'>
function confirmDeleteFunction(name, id) {
	var ans = confirm("Delete function '" + name + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=f&sm=d&i=' + id);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php
			echo "<table class=\"table table-bordered\" cellspacing='3px' cellpadding='3px'>";
			echo "<tr>";
			echo "<th>Option</td>";
			echo "<th>Id</td>";
			echo "<th>Module</td>";
			echo "<th>Name</td>";
			echo "<th>Page</td>";
			echo "<th>Image</td>";
			echo "<th>Position</td>";
			echo "</tr>";
			foreach ($FuncIds as $FuncId) {
				$id = $FuncId["id"];
				$mid = (isset($FuncId["mid"])?$FuncId["mid"]:'');
				$mname = (isset($FuncId["mname"])?$FuncId["mname"]:'');
				$name = $FuncId["name"];
				$page = $FuncId["page"];
				$image = $FuncId["image"];
				$pos = $FuncId["pos"];
				
				echo "<tr>";
				echo "<td align='center'>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=f&sm=e&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit' title='Edit'></img></a>";
				echo "&nbsp;";
				echo "<a href='#' onClick='confirmDeleteFunction(\"$name\", \"$id\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>";
				echo "&nbsp;";
				echo "</td>";
				echo "<td>$id&nbsp;</td>";
				echo "<td>$mname&nbsp;</td>";
				echo "<td>$name&nbsp;</td>";
				echo "<td>$page&nbsp;</td>";
				echo "<td>&nbsp;<img src='image/icon/$image' alt=''></img> $image&nbsp;</td>";
				
				if ($pos == 0) {
					echo "<td>Per Terminal&nbsp;</td>";
				} else if ($pos == 1) {
					echo "<td>Per Module&nbsp;</td>";
				} else if ($pos == 2) {
					echo "<td>Hide&nbsp;</td>";
				}
				echo "</tr>";
			}
			echo "</table>";
		}
	}
	return $bOK;
}

function deleteFunc() {
	global $Setting, $id;
	$bOK = $Setting->DeleteFunction($id);
	if ($bOK) {
		echo "<div>Function successfully deleted...</div>";
	} else {
		echo "<div>Function fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=f");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function insertFunc() {
	global $Setting, $User, $idFunc, $moduleFunc, $nameFunc, $pageFunc, $imageFunc, $posFunc, $mid, $r, $mode;
	
	// $idFunc = (isset($_REQUEST['idFunc']) ? trim($_REQUEST['idFunc']) : '');
	// $moduleFunc = (isset($_REQUEST['moduleFunc']) ? trim($_REQUEST['moduleFunc']) : '');
	// $nameFunc = (isset($_REQUEST['nameFunc']) ? trim($_REQUEST['nameFunc']) : '');
	// $pageFunc = (isset($_REQUEST['pageFunc']) ? trim($_REQUEST['pageFunc']) : '');
	// $imageFunc = (isset($_REQUEST['imageFunc']) ? trim($_REQUEST['imageFunc']) : '');
	// $posFunc = (isset($_REQUEST['posFunc']) ? trim($_REQUEST['posFunc']) : '');
	
	if ($idFunc == "") {
		// Initial insert
		$idFunc = "f" . $Setting->GetNextFunctionId();
	}
	
	$idFunc = htmlentities($idFunc, ENT_QUOTES);
	$moduleFunc = htmlentities($moduleFunc, ENT_QUOTES);
	$nameFunc = htmlentities($nameFunc, ENT_QUOTES);
	$pageFunc = htmlentities($pageFunc, ENT_QUOTES);
	$imageFunc = htmlentities($imageFunc, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=f&sm=i&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<div class='subTitle'>Insert new function</div>";
	echo "<div class='spacer10'></div>";
			
	// NEW: direfer dari list module di role
	// $mid = (isset($_REQUEST['mid']) ? trim($_REQUEST['mid']) : '');
	// $roleId = (isset($_REQUEST['r']) ? trim($_REQUEST['r']) : '');
	$roleId = $r;
	if ($mid != "" && $roleId != "") {
		$rolename = $User->GetRoleName($roleId);
		
		echo "<input type='hidden' id='mid' name='mid' value='$mid'></input>";
		echo "<input type='hidden' id='r' name='r' value='$roleId'></input>";
	
		$url64 = base64_encode("setting=1&m=r&sm=l&i=$roleId");
		echo "<a href='main.php?param=$url64'>&lsaquo; Back to list modules in role '$rolename'</a>";
		echo "<div class='spacer10'></div>";
		
		// override moduleId
		$moduleFunc = $mid;
	}
	
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Module</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	// module choice
	if ($Setting->GetModule($arModule)) {
		echo "<select id='moduleFunc' name='moduleFunc'>";
		echo "<option value='0'";
		if (!$moduleFunc || $moduleFunc == "0") {
			echo " selected";
		}
		echo ">--------</option>";
		foreach ($arModule as $mod) {
			$moduleId = $mod["id"];
			$moduleName = $mod["name"];
			echo "<option value='$moduleId'";
			if ($moduleFunc == $moduleId) {
				echo " selected";
			}
			echo ">$moduleName</option>";
		}
		echo "</select>";
	}
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='idFunc' name='idFunc' length='30' size='20' value='$idFunc' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameFunc' name='nameFunc' length='100' size='30' value='$nameFunc' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Page</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>function/<input type='text' id='pageFunc' name='pageFunc' length='100' size='30' value='$pageFunc' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Image</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>image/icon/";
	echo "<input type='text' id='imageFunc' name='imageFunc' length='100' size='15' value='$imageFunc' autocomplete='off'></input>";
	if ($imageFunc != "") {
		echo "<img id='imageSrcFunc' name='imageSrcFunc' src='image/icon/$imageFunc' alt=''></img>";
	} else {
		echo "<img id='imageSrcFunc' name='imageSrcFunc' src='' alt=''></img>";
	}
	echo "<input type='button' value='List' onClick='showImageList()'></input>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Position</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<select id='posFunc' name='posFunc'>";
	echo "<option value='0'>Per terminal</option>";
	echo "<option value='1'>Per module</option>";
	echo "<option value='2'>Hide</option>";
	echo "</select>";
	echo "</td>";
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

function insertFuncAction() {
	global $Setting, $User, $idFunc, $moduleFunc, $nameFunc, $pageFunc, $imageFunc, $posFunc, $mid, $r;
	
	// $idFunc = (isset($_REQUEST['idFunc']) ? trim($_REQUEST['idFunc']) : '');
	// $moduleFunc = (isset($_REQUEST['moduleFunc']) ? trim($_REQUEST['moduleFunc']) : '');
	// $nameFunc = (isset($_REQUEST['nameFunc']) ? trim($_REQUEST['nameFunc']) : '');
	// $pageFunc = (isset($_REQUEST['pageFunc']) ? trim($_REQUEST['pageFunc']) : '');
	// $imageFunc = (isset($_REQUEST['imageFunc']) ? trim($_REQUEST['imageFunc']) : '');
	// $posFunc = (isset($_REQUEST['posFunc']) ? trim($_REQUEST['posFunc']) : '');

	$bOK = $Setting->InsertFunction($idFunc, $moduleFunc, $nameFunc, $pageFunc, $imageFunc, $posFunc);

	$idFunc = htmlentities($idFunc, ENT_QUOTES);
	$moduleNameFunc = $User->GetModuleName($moduleFunc);
	$moduleNameFunc = htmlentities($moduleNameFunc, ENT_QUOTES);
	$nameFunc = htmlentities($nameFunc, ENT_QUOTES);
	$pageFunc = htmlentities($pageFunc, ENT_QUOTES);
	$imageFunc = htmlentities($imageFunc, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=f&sm=i");
	echo "<form method='POST' action='main.php?param=$url64' id='formFunc'>";
	
	// NEW: direfer dari list module di role
	// $mid = (isset($_REQUEST['mid']) ? trim($_REQUEST['mid']) : '');
	// $roleId = (isset($_REQUEST['r']) ? trim($_REQUEST['r']) : '');
	$roleId = $r;
	if ($mid != "" && $roleId != "") {
		$rolename = $User->GetRoleName($roleId);
		
		echo "<input type='hidden' id='mid' name='mid' value='$mid'></input>";
		echo "<input type='hidden' id='r' name='r' value='$roleId'></input>";
	}
	
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert Function";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Module</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='moduleFunc' name='moduleFunc' value='$moduleFunc'></input>$moduleNameFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idFunc' name='idFunc' value='$idFunc'></input>$idFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameFunc' name='nameFunc' value='$nameFunc'></input>$nameFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Page</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='pageFunc' name='pageFunc' value='$pageFunc'></input>$pageFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Image</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<input type='hidden' id='imageFunc' name='imageFunc' value='$imageFunc'></input>";
	echo "<img src='image/icon/$imageFunc' alt=''></img>";
	echo "($imageFunc)";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Position</td>";
	echo "<td valign='top'>:</td>";
	if ($posFunc == 0) {
		echo "<td>Per Terminal&nbsp;</td>";
	} else if ($posFunc == 1) {
		echo "<td>Per Module&nbsp;</td>";
	} else if ($posFunc == 2) {
		echo "<td>Hide&nbsp;</td>";
	}
	echo "<input type='hidden' id='posFunc' name='posFunc' value='$posFunc'></input>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formFunc\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		if ($mid != "" && $roleId != "") {
			// redirect ke list module
			$url64 = base64_encode("setting=1&m=r&sm=l&i=$roleId");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
		} else {
			$url64 = base64_encode("setting=1&m=f");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
		}
	}
	echo "</div>";
	echo "</form>";
}

function editFuncAction() {
	global $Setting, $User, $idFunc, $moduleFunc, $nameFunc, $pageFunc, $imageFunc, $posFunc;
	
	// $idFunc = (isset($_REQUEST['idFunc']) ? trim($_REQUEST['idFunc']) : '');
	// $moduleFunc = (isset($_REQUEST['moduleFunc']) ? trim($_REQUEST['moduleFunc']) : '');
	// $nameFunc = (isset($_REQUEST['nameFunc']) ? trim($_REQUEST['nameFunc']) : '');
	// $pageFunc = (isset($_REQUEST['pageFunc']) ? trim($_REQUEST['pageFunc']) : '');
	// $imageFunc = (isset($_REQUEST['imageFunc']) ? trim($_REQUEST['imageFunc']) : '');
	// $posFunc = (isset($_REQUEST['posFunc']) ? trim($_REQUEST['posFunc']) : '');

	$bOK = $Setting->EditFunction($idFunc, $moduleFunc, $nameFunc, $pageFunc, $imageFunc, $posFunc);
	
	$idFunc = htmlentities($idFunc, ENT_QUOTES);
	$moduleNameFunc = $User->GetModuleName($moduleFunc);
	$moduleNameFunc = htmlentities($moduleNameFunc, ENT_QUOTES);
	$nameFunc = htmlentities($nameFunc, ENT_QUOTES);
	$pageFunc = htmlentities($pageFunc, ENT_QUOTES);
	$imageFunc = htmlentities($imageFunc, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=f&sm=e");
	echo "<form method='POST' action='main.php?param=$url64' id='formFunc'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit Function";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Module</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='moduleFunc' name='moduleFunc' value='$moduleFunc'></input>$moduleNameFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idFunc' name='idFunc' value='$idFunc'></input>$idFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameFunc' name='nameFunc' value='$nameFunc'></input>$nameFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Page</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='pageFunc' name='pageFunc' value='$pageFunc'></input>$pageFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Image</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<input type='hidden' id='imageFunc' name='imageFunc' value='$imageFunc'></input>";
	echo "<img src='image/icon/$imageFunc' alt=''></img>";
	if ($imageFunc)
		echo "($imageFunc)";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Position</td>";
	echo "<td valign='top'>:</td>";
	if ($posFunc == 0) {
		echo "<td>Per Terminal&nbsp;</td>";
	} else if ($posFunc == 1) {
		echo "<td>Per Module&nbsp;</td>";
	} else if ($posFunc == 2) {
		echo "<td>Hide&nbsp;</td>";
	}
	echo "<input type='hidden' id='posFunc' name='posFunc' value='$posFunc'></input>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formFunc\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=f");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
}

function editFunc() {
	global $Setting, $id;
	
	$arFunc = $Setting->GetFunctionDetail($id);
	$idFunc = $arFunc["id"];
	$moduleFunc = $arFunc["mid"];
	$nameFunc = $arFunc["name"];
	// $privFunc = $arFunc["priv"];
	$pageFunc = $arFunc["page"];
	$imageFunc = $arFunc["image"];
	$posFunc = $arFunc["pos"];

	$idFunc = htmlentities($idFunc, ENT_QUOTES);
	$moduleFunc = htmlentities($moduleFunc, ENT_QUOTES);
	$nameFunc = htmlentities($nameFunc, ENT_QUOTES);
	// $privFunc = htmlentities($privFunc, ENT_QUOTES);
	$pageFunc = htmlentities($pageFunc, ENT_QUOTES);
	$imageFunc = htmlentities($imageFunc, ENT_QUOTES);
	
	echo "<div class='subTitle'>Edit function '$nameFunc'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=f&sm=e&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Module</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	// module choice
	if ($Setting->GetModule($arModule)) {
		echo "<select id='moduleFunc' name='moduleFunc'>";
		echo "<option value='0'";
		if (!$moduleFunc || $moduleFunc == "0") {
			echo " selected";
		}
		echo ">--------</option>";
		foreach ($arModule as $mod) {
			$moduleId = $mod["id"];
			$moduleName = $mod["name"];
			echo "<option value='$moduleId'";
			if ($moduleFunc == $moduleId) {
				echo " selected";
			}
			echo ">$moduleName</option>";
		}
		echo "</select>";
	}
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idFunc' name='idFunc' value='$idFunc'></input>$idFunc</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameFunc' name='nameFunc' length='100' size='30' value='$nameFunc' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Page</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>function/<input type='text' id='pageFunc' name='pageFunc' length='100' size='20' value='$pageFunc' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Image</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>image/icon/";
	echo "<input type='text' id='imageFunc' name='imageFunc' length='100' size='25' value='$imageFunc' autocomplete='off'></input>";
	echo "<img id='imageSrcFunc' name='imageSrcFunc' src='image/icon/$imageFunc' alt=''></img>";
	echo "<input type='button' value='List' onClick='showImageList()'></input>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Position</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<select id='posFunc' name='posFunc'>";
	echo "<option value='0'>Per terminal</option>";
	if ($posFunc == 1) {
		echo "<option value='1' selected>Per module</option>";
	} else {
		echo "<option value='1'>Per module</option>";
	}
	if ($posFunc == 2) {
		echo "<option value='2' selected>Hide</option>";
	} else {
		echo "<option value='2'>Hide</option>";
	}
	echo "</select>";
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

?>
