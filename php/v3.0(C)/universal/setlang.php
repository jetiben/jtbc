<?php
header('content-type: text/html; charset=utf-8');
define('JTBC', 'common/incfiles/jtbc.php');
function pathing($p){ return is_file($p)? $p: pathing('../' . $p); }
require_once(pathing(JTBC));
require_inc(__FILE__);
header('location: ' . jtbc\ui::getRedirect());
?>
