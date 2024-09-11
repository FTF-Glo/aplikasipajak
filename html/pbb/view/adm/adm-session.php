<?php

if (!isset($data)) {
	return;
}

if ($subMode == "d") {
	// Submode delete
	deleteSession();
	return;
}

?>
<script type='text/javascript'>			
function confirmDeleteSession(session, id, name, inputUsername) {
	var ans = confirm("Delete session '" + session + "' of '" + name + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=n&sm=d&sessionId=' + session + "&inputId=" + id + "&inputUsername=" + inputUsername);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php

if (!isset($inputUsername)) {
	$inputUsername = "";
}

$inputUsername = trim($inputUsername);
echo "	<div class='subTitle'>Search sessions</div>\n";
echo "	<div class='spacer5'></div>\n";

$url64 = base64_encode("setting=1&m=n");
echo "	<form method='POST' action='main.php?param=$url64' >\n";
echo "		Username: \n";
echo "		<input type='text' size='25' id='inputUsername' name='inputUsername' value='$inputUsername' />\n";
echo "		<input type='submit' value='Search' />\n";
echo "	</form>\n";

//if ($inputUsername == "") {
//	return;
//}

echo "	<div class='spacer10'></div>\n";
echo "	<div class='subTitle'>List sessions of '$inputUsername'</div>\n";
echo "	<div class='spacer5'></div>\n";
$arSession = $Session->GetAllSession($inputUsername);
if ($arSession != null) {
	echo "	<table class=\"table table-bordered\" cellspacing='3px' cellpadding='3px'>\n";
	echo "		<tr>\n";
	echo "			<th>Option</th>\n";
	echo "			<th>Id</th>\n";
	echo "			<th>userId</th>\n";
	echo "			<th>Session</th>\n";
	echo "			<th>Last Session</th>\n";
	echo "			<th>Client IP</th>\n";
	echo "			<th align='center'>Active</th>\n";
	echo "		</tr>\n";
	
	// var_dump($arSession);
	foreach ($arSession as $iSession) {
		$id = $iSession["id"];
		$uid = $iSession["uid"];
		$session = $iSession["session"];
		$lastSession = $iSession["lastSession"];
		$stillActive = $iSession["stillActive"];
		$ip = $iSession["ip"];
		
		echo "		<tr>\n";
		echo "			<td align='center'>\n";
		echo "				&nbsp;\n";
		echo "				<a href='#' onClick='confirmDeleteSession(\"$session\", \"$id\", \"$inputUsername\", \"$inputUsername\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>\n";
		echo "				&nbsp;\n";
		echo "			</td>\n";
		echo "			<td>$id&nbsp;</td>\n";
		echo "			<td>$uid&nbsp;</td>\n";
		echo "			<td>$session&nbsp;</td>\n";
		echo "			<td>$lastSession&nbsp;</td>\n";
		echo "			<td>$ip&nbsp;</td>\n";
		if ($stillActive) {
			echo "			<td align='center'>Active&nbsp;</td>\n";
		} else {
			echo "			<td align='center'>-&nbsp;</td>\n";
		}
		echo "		</tr>\n";
	}
	echo "	</table>\n";
} else {
	echo "	<div>- Empty -</div>\n";
}

function deleteSession() {
	global $Session, $sessionId, $inputId, $inputUsername;
	$bOK = $Session->DeleteSessionFromDB($inputId, $sessionId);
	if ($bOK) {
		echo "<div>Session successfully deleted...</div>\n";
	} else {
		echo "<div>Session fail to delete...</div>\n";
	}
	$url64 = base64_encode("setting=1&m=n&inputUsername=$inputUsername");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>\n";
}

?>
