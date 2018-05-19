<?php
header('content-type: text/xml; charset=utf-8');
header('cache-control: no-cache, must-revalidate');
define('PAGE', 'common/incfiles/page.inc.php');
if (is_file(PAGE)) require_once(PAGE);
elseif (is_file('../' . PAGE)) require_once('../' . PAGE);
elseif (is_file('../../' . PAGE)) require_once('../../' . PAGE);
elseif (is_file('../../../' . PAGE)) require_once('../../../' . PAGE);
require_once_this_file_inc(__FILE__);
echo jtbc\ui::getResult();
?>