<?php
header('content-type: text/html; charset=utf-8');
define('JTBC', 'common/incfiles/jtbc.php');
function realjtbc($p){ return is_file($p)? $p: realjtbc('../' . $p); }
require_once(realjtbc(JTBC));
echo(require_inc_and_get_result(__FILE__));
?><?php if (SITESTATUS == 0) header('location: _install');?>
