<?php
header('content-type: text/html; charset=utf-8');
define('JTBC', 'common/incfiles/jtbc.php');
function realjtbc($p){ while(!is_file($p)){ $p = '../' . $p; } return $p; }
require_once(realjtbc(JTBC));
require_inc(__FILE__);
header('location: ' . jtbc\ui::getRedirect());
?>
