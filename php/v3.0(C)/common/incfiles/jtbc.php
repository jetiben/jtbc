<?php
use jtbc\ui as ui;
use jtbc\base as base;
use jtbc\route as route;
require_once('const.php');

function jtbc_get_result($argFile)
{
  $file = $argFile;
  $incFile = route::getIncFilePath($file);
  if (is_file($incFile))
  {
    require_once($incFile);
    $result = ui::getResult();
    $resultType = ui::getPara('resultType');
    if (empty($resultType)) $resultType = 'text';
    if ($resultType == 'text') echo $result;
    else if ($resultType == 'url') header('location: ' . $result);
  }
  else http_response_code(404);
}

function jtbc_get_pathinfo_result()
{
  $requestUri = @$_SERVER['REQUEST_URI'];
  $oriScriptName = @$_SERVER['SCRIPT_NAME'];
  if (strpos($requestUri, $oriScriptName) === 0) http_response_code(404);
  else
  {
    $scriptName = base::getScriptName();
    $filePath = base::getLRStr($scriptName, '/', 'rightr');
    $fileDir = pathinfo($filePath, PATHINFO_DIRNAME);
    if (is_dir($fileDir))
    {
      chdir($fileDir);
      jtbc_get_result($scriptName);
    }
    else http_response_code(404);
  }
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