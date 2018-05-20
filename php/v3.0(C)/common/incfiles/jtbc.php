<?php
require_once('const.php');

function require_inc($argFile)
{
  require_once('common/incfiles/' . basename($argFile, '.php') . '.inc.php');
}

function require_inc_and_get_result($argFile)
{
  require_inc($argFile);
  return jtbc\ui::getResult();
}

spl_autoload_register(function($argClass){
  $class = $argClass;
  if (substr($class, 0, 4) == 'jtbc')
  {
    $file = __DIR__ . '/lib/' . str_replace('\\', '/', $class) . '.inc.php';
    if (is_file($file)) require_once($file);
  }
  else
  {
    $file = __DIR__ . '/vendor/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) require_once($file);
  }
});
?>