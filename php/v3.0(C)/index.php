<?php
header('content-type: text/html; charset=utf-8');
define('JTBC', 'common/incfiles/jtbc.php');
if (is_file(JTBC)) require_once(JTBC);
elseif (is_file('../' . JTBC)) require_once('../' . JTBC);
elseif (is_file('../../' . JTBC)) require_once('../../' . JTBC);
elseif (is_file('../../../' . JTBC)) require_once('../../../' . JTBC);
require_once_this_file_inc(__FILE__);
echo jtbc\ui::getResult();
?><?php if (SITESTATUS == 0) header('location: _install');?>
