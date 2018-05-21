<?php
header('content-type: text/html; charset=utf-8');
function pathing($p){ return is_file($p)? $p: pathing('../' . $p); }
require_once(pathing('common/incfiles/jtbc.php'));
echo(require_inc_and_get_result(__FILE__));
?>
