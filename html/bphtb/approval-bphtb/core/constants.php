<?php

defined('BASE_URL')     or define('BASE_URL', 'http://36.92.151.83:7083/approval-bphtb/');
defined('APP_NAME')     or define('APP_NAME', 'Approval BPHTB');
// PATH
defined('PAGES')        or define('PAGES', 'pages/');
defined('FUNCTIONS')    or define('FUNCTIONS', 'functions/');
defined('ACTIONS')      or define('ACTIONS', 'actions/');
// REAL PATH
defined('CORE_PATH')    or define('CORE_PATH', __DIR__);
defined('ROOT_PATH')    or define('ROOT_PATH', str_replace(basename(CORE_PATH), '', __DIR__));
defined('PAGE_PATH')    or define('PAGE_PATH', ROOT_PATH . PAGES);
defined('FUNC_PATH')    or define('FUNC_PATH', ROOT_PATH . FUNCTIONS);
defined('ACTION_PATH')  or define('ACTION_PATH', ROOT_PATH . ACTIONS);
// DB SW
defined('SW_DB_HOST')   or define('SW_DB_HOST', 'localhost');
defined('SW_DB_PORT')   or define('SW_DB_PORT', '3306');
defined('SW_DB_USER')   or define('SW_DB_USER', 'root');
defined('SW_DB_PASS')   or define('SW_DB_PASS', '@Lamsel2023');
defined('SW_DB_NAME')   or define('SW_DB_NAME', 'sw_ssb');
// DB GW
defined('GW_DB_HOST')   or define('GW_DB_HOST', 'localhost');
defined('GW_DB_PORT')   or define('GW_DB_PORT', '3306');
defined('GW_DB_USER')   or define('GW_DB_USER', 'root');
defined('GW_DB_PASS')   or define('GW_DB_PASS', '@Lamsel2023');
defined('GW_DB_NAME')   or define('GW_DB_NAME', 'gw_ssb');
// DB LOKAL
defined('DB_HOST')      or define('DB_HOST', 'localhost');
defined('DB_PORT')      or define('DB_PORT', '3306');
defined('DB_USER')      or define('DB_USER', 'root');
defined('DB_PASS')      or define('DB_PASS', '@Lamsel2023');
defined('DB_NAME')      or define('DB_NAME', 'approval_bphtb');
// QR
defined('QR_BR')        or define('QR_BR', '%0A');
