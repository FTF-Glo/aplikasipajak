<?php

include("image/captcha/securimage.php");

class CaptchaAuth extends AuthBase {

	// render input
	public function element() {
		return array(
			array("label" => "", "id" => null, "input" => "<img src='captcha2.php' alt='Captcha Image' id='captcha-image' />"),
			array("label" => "Verification Code", "id" => "cImage", "input" => "<input type='text' name='cImage' id='cImage' value ='' size='6' maxlength='10' autocomplete='off'></input>")
		);
	}
	
	// authentication process
	public function auth(&$input, &$arResponse) {
		$cImage = $input["cImage"];
	
		$img = new Securimage();
		$equal = ($img->check($cImage) );
		
		if (!$equal) {
			// echo "wrong verification code";
			$arResponse["error"] = "Wrong verification code";
		}
		// echo "verified...";
		// die();
		
		return $equal;
	}
}

?>
