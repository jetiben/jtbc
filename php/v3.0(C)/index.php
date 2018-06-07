<?php
header('content-type: text/html; charset=utf-8');
function p($p){return is_file($p)? $p: p('../' . $p);}
require_once(p('common/incfiles/jtbc.php'));
echo require_inc_and_get_result(__FILE__);
?><?php if (SITESTATUS == 0) header('location: _install');?>