<?php

$dbInstance = new db(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// connect to sw_ssb
$dbInstance->addConnection('sw_ssb', array(
    'host' => SW_DB_HOST,
    'username' => SW_DB_USER,
    'password' => SW_DB_PASS,
    'db' => SW_DB_NAME,
    'port' => SW_DB_PORT,
));

// connect to gw_ssb
$dbInstance->addConnection('gw_ssb', array(
    'host' => GW_DB_HOST,
    'username' => GW_DB_USER,
    'password' => GW_DB_PASS,
    'db' => GW_DB_NAME,
    'port' => GW_DB_PORT,
));
