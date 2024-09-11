<?php

class DefaultAuth extends AuthBase {
	
	// render input
	function element() {
		return array(
			array("label" => "Username", "id" => "usr", "input" => "<input type='text' id='usr' name='usr' value='' autocomplete='on' placeholder='Username' required />"),
			array("label" => "Password", "id" => "pwd", "input" => "<input type='password' id='pwd' name='pwd' value='' autocomplete='off' placeholder='Username' required />")
		);
	}
	
	// authentication process
	function auth(&$input, &$arResponse) {
		include("login.php");
		//var_dump($input);
		$usr = $input["usr"];
		$pwd = $input["pwd"];
		
		$arResponse = login($usr, $pwd);
		if (isset($arResponse["error"])) {
			// have error message, login failed
			return false;
		}
		return true;
	}
	
	
}
