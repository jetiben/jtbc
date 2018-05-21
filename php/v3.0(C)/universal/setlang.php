<?php
header('content-type: text/html; charset=utf-8');
function pathing($p){ return is_file($p)? $p: pathing('../' . $p); }
require_once(pathing('common/incfiles/jtbc.php'));
require_inc(__FILE__);
header('location: ' . jtbc\ui::getRedirect());
?>
