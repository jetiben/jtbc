<?php
use jtbc\ui as ui;
use jtbc\base as base;
use jtbc\route as route;
use jtbc\request as request;
use jtbc\tpl as tpl;
require_once('const.php');

function jtbc_get_result($argFile)
{
  $file = $argFile;
  $incFile = route::getIncFilePath($file);
  if (is_file($incFile))
  {
    require_once($incFile);
    $result = ui::getResult();
    $errorCode = ui::$errorCode;
    if ($errorCode != 0) print(ui::getErrorResult($errorCode));
    else
    {
      $resultType = ui::getPara('resultType');
      if ($resultType == 'url') header('location: ' . $result);
      else print($result);
    }
  }
  else
  {
    $error404 = true;
    $requestUri = request::server('REQUEST_URI');
    $lastName = base::getLRStr($requestUri, '/', 'right');
    if (!base::isEmpty($lastName))
    {
      if (strpos($lastName, '.') === false)
      {
        if (empty(request::get()) && empty(request::post())) $error404 = false;
      }
    }
    if ($error404 == true) print(ui::getErrorResult(404));
    else header('location: ' . $requestUri . '/');
  }
}

function jtbc_get_pathinfo_result()
{
  $requestUri = request::server('REQUEST_URI');
  $oriScriptName = request::server('SCRIPT_NAME');
  if (strpos($requestUri, $oriScriptName) === 0)
  {
    print(ui::getErrorResult(404));
  }
  else
  {
    $scriptName = route::getScriptName();
    $filePath = base::getLRStr($scriptName, '/', 'rightr');
    $fileDir = pathinfo($filePath, PATHINFO_DIRNAME);
    if (is_dir($fileDir))
    {
      chdir($fileDir);
      jtbc_get_result($scriptName);
    }
    else
    {
      print(ui::getErrorResult(404));
    }
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