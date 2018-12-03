<?php
use jtbc\ui as ui;
use jtbc\page as page;
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
    if ($error404 == true) print(page::getErrorResult(404));
    else header('location: ' . $requestUri . '/');
  }
}

function jtbc_get_pathinfo_result()
{
  $requestUri = request::server('REQUEST_URI');
  $oriScriptName = request::server('SCRIPT_NAME');
  if (strpos($requestUri, $oriScriptName) === 0)
  {
    print(page::getErrorResult(404));
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
      print(page::getErrorResult(404));
    }
  }
}

spl_autoload_register(function($argClass){
  $class = $argClass;
  $classPath = str_replace('\\', '/', $class);
  $firstPath = strstr($classPath, '/', true);
  $requireFile = null;
  if ($firstPath == 'app' || $firstPath == 'jtbc') $requireFile = __DIR__ . '/lib/' . $classPath . '.inc.php';
  else if ($firstPath == 'web')
  {
    $childPath = ltrim(strstr($classPath, '/'), '/');
    if (!is_numeric(strpos($childPath, '/'))) $requireFile = __DIR__ . '/lib/' . $childPath . '.inc.php';
    else
    {
      $folder = substr($childPath, 0, strrpos($childPath, '/'));
      $childFile = ltrim(substr($childPath, strrpos($childPath, '/')), '/');
      $requireFile = __DIR__ . '/../../' . $folder . '/common/incfiles/lib/' . $childFile . '.inc.php';
    }
  }
  else $requireFile = __DIR__ . '/vendor/' . $classPath . '.php';
  if (!is_null($requireFile) && is_file($requireFile)) require_once($requireFile);
});
?>