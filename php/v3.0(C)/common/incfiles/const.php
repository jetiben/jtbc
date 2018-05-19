<?php
ob_start();
session_start();
set_time_limit(1800);
ini_set('display_errors', '1');
date_default_timezone_set('PRC');
define('APPNAME', 'jtbc_');
define('ASSETSPATH', 'common/assets');
define('BASEDIR', '');
define('CACHEDIR', 'cache');
define('CHARSET', 'utf-8');
define('CONSOLEDIR', 'console');
define('COOKIESPATH', '/');
define('DB', 'MySQL');
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'db_jtbc');
define('DB_TABLE_PREFIX', 'jtbc_');
define('DB_STRUCTURE_CACHE', false);
define('LANGUAGE', 'zh-cn');
define('SEPARATOR', ' - ');
define('SITESTATUS', 100);
define('THEME', 'default');
define('TEMPLATE', 'default');
define('VERSION', '3.0.1.4');
define('WEBKEY', 'J1T2B3C4');
define('XMLSFX', '.jtbc');
?>
