<?php
header('content-type: text/html; charset=utf-8');
define('JTBC', 'common/incfiles/jtbc.php');
function realjtbc($p){ while(!is_file($p)){ $p = '../' . $p; } return $p; }
require_once(realjtbc(JTBC));
echo(require_inc_and_get_result(__FILE__));
?>
