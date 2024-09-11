<?php

function encrypt($st) {
	require("safe.key");
	$key = base64_encode($text);

	// $encrypt = mcrypt_encrypt(MCRYPT_CRYPT, $key, $st, MCRYPT_MODE_ECB);
	$encrypt = convert($st, $key);
	return $encrypt;
}

function decrypt($st) {
	require("safe.key");
	$key = base64_encode($text);

	// $decrypt = mcrypt_decrypt(MCRYPT_CRYPT, $key, $st, MCRYPT_MODE_ECB);
	$decrypt = convert($st, $key);
	// echo $decrypt;
	return $decrypt;
}

// String EnCrypt + DeCrypt function
// Author: halojoy, July 2006
// Modified and commented by: laserlight, August 2006
// Exploratory implementation using bitwise ops on strings; Weedpacket September 2006 
function convert($text, $key = '') {
    // return text unaltered if the key is blank
    if ($key == '') {
        return $text;
    }

    // remove the spaces in the key
    $key = str_replace(' ', '', $key);
    if (strlen($key) < 8) {
        exit('key error');
    }
    // set key length to be no more than 32 characters
    $key_len = strlen($key);
    if ($key_len > 32) {
        $key_len = 32;
    }

    // A wee bit of tidying in case the key was too long
    $key = substr($key, 0, $key_len);

    // We use this a couple of times or so
    $text_len = strlen($text);

    // fill key with the bitwise AND of the ith key character and 0x1F, padded to length of text.
    $lomask = str_repeat("\x1f", $text_len); // Probably better than str_pad
    $himask = str_repeat("\xe0", $text_len);
    $k = str_pad("", $text_len, $key); // this one _does_ need to be str_pad

    // {en|de}cryption algorithm
    $text = (($text ^ $k) & $lomask) | ($text & $himask);
	// echo "en|de = $text<br />\n";

    return $text;
}

?>
