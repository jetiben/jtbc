<?php
header('content-type: text/html; charset=utf-8');
require_once('common/incfiles/page.inc.php');
require_once_this_file_inc(__FILE__);
echo jtbc\ui::getResult();
?><?php if (SITESTATUS == 0) header('location: _install');?>
