<?php
// global variables
define("VERSION", "1.0.0.400");   // application version (DO NOT CHANGE THIS BY YOUR SELF OR AT YOUR OWN RISK!)

// identities
define("ONPAYS_ELECTRICITY_LOG_OWNER", "ONPAYS_CENTRAL_ELECTRICITY"); // DO NOT CHANGE THIS

// log purpose
//define("DEBUG", DEBUG_ERROR); // 0 = no debug, 2 = info, 4 = warning, 8 = error(can be OR'd)
define("DEBUG", 15|128); // 0 = no debug, 2 = info, 4 = warning, 8 = error(can be OR'd)

define("LOG_FILENAME", "./tmp/".strftime("%Y%m%d", time())."-DEVEL-web-onpays-vsi-c.log");

// session
define("ONPAYS_SESSION_INTERVAL", 10200); // in seconds, default 1 hours = 3.600 seconds

// authentication & authorization
define("ONPAYS_AUTH_ACTIVE", true);
define("ONPAYS_ACCESS_ACTIVE", true);
define("ONPAYS_BLOCKER_ACTIVE", true); // true: check if PP is blocked or not, continue check if subscriber is blocked at PP if PP was not blocked.

// development purpose (DO NOT CHANGE THIS VALUE)
//error_reporting(0);
