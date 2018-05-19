<?php
header('content-type: text/html; charset=utf-8');
define('PAGE', 'common/incfiles/page.inc.php');
if (is_file(PAGE)) require_once(PAGE);
elseif (is_file('../' . PAGE)) require_once('../' . PAGE);
elseif (is_file('../../' . PAGE)) require_once('../../' . PAGE);
elseif (is_file('../../../' . PAGE)) require_once('../../../' . PAGE);
require_once_this_file_inc(__FILE__);
header('location: ' . jtbc\ui::getRedirect());
?>