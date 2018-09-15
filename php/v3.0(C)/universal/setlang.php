<?php
function p($p){return is_file($p)? $p: p("../$p");};
require_once(p('common/incfiles/jtbc.php'));
jtbc_get_result(__FILE__);
?>