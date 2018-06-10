<?php
header('content-type: text/html; charset=utf-8');
function p($p){return is_file($p)? $p: p("../$p");};
require_once(p('common/incfiles/jtbc.php'));
require_inc(__FILE__);
header('location: ' . jtbc\ui::getRedirect());
?>