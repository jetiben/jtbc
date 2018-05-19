<?php
header('content-type: text/xml; charset=utf-8');
header('cache-control: no-cache, must-revalidate');
define('JTBC', 'common/incfiles/jtbc.php');
if (is_file(JTBC)) require_once(JTBC);
elseif (is_file('../' . JTBC)) require_once('../' . JTBC);
elseif (is_file('../../' . JTBC)) require_once('../../' . JTBC);
elseif (is_file('../../../' . JTBC)) require_once('../../../' . JTBC);
require_once_this_file_inc(__FILE__);
echo jtbc\ui::getResult();
?>
