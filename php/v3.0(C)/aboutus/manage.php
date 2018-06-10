<?php
header('content-type: text/xml; charset=utf-8');
header('cache-control: no-cache, must-revalidate');
function p($p){return is_file($p)? $p: p("../$p");};
require_once(p('common/incfiles/jtbc.php'));
echo require_inc_and_get_result(__FILE__);
?>