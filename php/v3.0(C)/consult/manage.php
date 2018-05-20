<?php
header('content-type: text/xml; charset=utf-8');
header('cache-control: no-cache, must-revalidate');
define('JTBC', 'common/incfiles/jtbc.php');
function require_jtbc($path){ if (is_file($path)){ require_once($path); return true; } }
if (require_jtbc(JTBC) || require_jtbc('../' . JTBC) || require_jtbc('../../' . JTBC) || require_jtbc('../../../' . JTBC)) echo(require_inc_and_get_result(__FILE__));
?>
