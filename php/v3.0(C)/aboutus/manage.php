<?php
header('content-type: text/xml; charset=utf-8');
header('cache-control: no-cache, must-revalidate');
define('JTBC', 'common/incfiles/jtbc.php');
function pathing($p){ return is_file($p)? $p: pathing('../' . $p); }
require_once(pathing(JTBC));
echo(require_inc_and_get_result(__FILE__));
?>
