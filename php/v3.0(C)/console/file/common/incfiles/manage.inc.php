<?php
namespace jtbc;
class ui extends console\page {
  public static $allowFiletype = 'txt,css,js,htm,html,asp,aspx,cs,php,java,jsp,config,sql,svg,jtbc';

  protected static function ppGetFolderAndFileName($argName)
  {
    $tmpstr = $argName;
    $php = base::getNum(base::getLRStr(PHP_VERSION, '.', 'left'), 0);
    if ($php < 7) $tmpstr = iconv('cp936', CHARSET, $tmpstr);
    return $tmpstr;
  }

  protected static function ppSetFolderAndFileName($argName)
  {
    $tmpstr = $argName;
    $php = base::getNum(base::getLRStr(PHP_VERSION, '.', 'left'), 0);
    if ($php < 7) $tmpstr = iconv(CHARSET, 'cp936', $tmpstr);
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $pathnavHTML = tpl::take('::console.link', 'tpl', 0, array('text' => '/', 'link' => '?type=list'));
    if (base::isEmpty($path)) $path = $pathRoot;
    else
    {
      $pathCurrent = $pathRoot;
      $pathArray = explode('/', base::getLRStr($path, $pathRoot, 'rightr'));
      foreach ($pathArray as $key => $val)
      {
        if (!base::isEmpty($val))
        {
          $pathnavHTML .= tpl::take('::console.link', 'tpl', 0, array('text' => base::htmlEncode($val) . '/', 'link' => '?type=list&amp;path=' . urlencode($pathCurrent . $val . '/')));
          $pathCurrent .= $val . '/';
        }
      }
    }
    $account = self::account();
    $variable['-path'] = $path;
    $vars['-path-nav'] = $pathnavHTML;
    $listAry = array();
    if (is_dir($path))
    {
      $dir = @dir($path);
      $floders = array();
      $files = array();
      while($entry = $dir -> read())
      {
        if ($entry != '.' && $entry != '..')
        {
          if (is_dir($path . $entry))
          {
            $floders[$entry] = $path . $entry;
          }
          else if (is_file($path . $entry))
          {
            $files[$entry] = $path . $entry;
          }
        }
      }
      foreach ($floders as $key => $val)
      {
        $info['path'] = $path;
        $info['topic'] = self::ppGetFolderAndFileName($key);
        $info['lasttime'] = date('Y-m-d H:i:s', filemtime($val));
        $info['-val'] = urlencode($val . '/');
        $info['-style'] = '';
        $info['-linkurl'] = '?type=list&path=' . urlencode(self::ppGetFolderAndFileName($val . '/'));
        array_push($listAry, $info);
      }
      foreach ($files as $key => $val)
      {
        $info['path'] = $path;
        $info['topic'] = self::ppGetFolderAndFileName($key);
        $info['lasttime'] = date('Y-m-d H:i:s', filemtime($val));
        $info['-val'] = urlencode($val . '/');
        $info['-style'] = 'background-image:url(' . ASSETSPATH . '/icon/filetype/' . base::getLRStr(self::ppGetFolderAndFileName($key), '.', 'right') . '.svg),url(' . ASSETSPATH . '/icon/filetype/others.svg)';
        $info['-linkurl'] = '?type=edit&path=' . urlencode(self::ppGetFolderAndFileName($val));
        array_push($listAry, $info);
      }
    }
    $tmpstr = tpl::takeAndAssign('manage.list', $listAry, $variable, $vars);
    $tmpstr = $account -> replaceAccountTag($tmpstr);
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleEdit()
  {
    $status = 1;
    $tmpstr = '';
    $filemode = 'xml';
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $filetype = strtolower(base::getLRStr($path, '.', 'right'));
    $pathnavHTML = tpl::take('::console.link', 'tpl', 0, array('text' => '/', 'link' => '?type=list'));
    if ($filetype == 'css') $filemode = 'css';
    else if ($filetype == 'js') $filemode = 'javascript';
    else if ($filetype == 'php') $filemode = 'php';
    else if ($filetype == 'htm') $filemode = 'htmlmixed';
    else if ($filetype == 'html') $filemode = 'htmlmixed';
    if (base::isEmpty($path)) $path = $pathRoot;
    else
    {
      $pathCurrent = $pathRoot;
      $pathArray = explode('/', base::getLRStr($path, $pathRoot, 'rightr'));
      foreach ($pathArray as $key => $val)
      {
        if (!base::isEmpty($val))
        {
          if ($key == count($pathArray) - 1)
          {
            $pathnavHTML .= tpl::take('::console.link', 'tpl', 0, array('text' => base::htmlEncode($val), 'link' => '?type=edit&amp;path=' . urlencode($pathCurrent . $val)));
          }
          else
          {
            $pathnavHTML .= tpl::take('::console.link', 'tpl', 0, array('text' => base::htmlEncode($val) . '/', 'link' => '?type=list&amp;path=' . urlencode($pathCurrent . $val . '/')));
            $pathCurrent .= $val . '/';
          }
        }
      }
    }
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('edit'))
    {
      $variable['-path'] = $path;
      $variable['-path-urlencode'] = urlencode($path);
      $vars['-path-nav'] = $pathnavHTML;
      if (base::checkInstr(self::$allowFiletype, $filetype, ','))
      {
        $variable['-filemode'] = $filemode;
        $variable['-file-content'] = @file_get_contents($path);
        $tmpstr = tpl::takeAndAssign('manage.edit', null, $variable, $vars);
      }
      else $tmpstr = tpl::takeAndAssign('manage.edit-lock', null, $variable, $vars);
      $tmpstr = $account -> replaceAccountTag($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleGetInfo()
  {
    $status = 0;
    $message = '';
    $val = base::getString(request::get('val'));
    if (is_dir($val))
    {
      $message = $val;
      $info = file::getFolderInfo($val);
      if (is_array($info)) $message = tpl::take('manage.text-folder-info', 'lng', 0, array('size' => base::formatFileSize($info['size']), 'file' => base::getString($info['file']), 'folder' => base::getString($info['folder'])));
    }
    else if (is_file($val))
    {
      $message = tpl::take('manage.text-file-info', 'lng', 0, array('size' => base::formatFileSize(filesize($val))));
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionAddFolder()
  {
    $status = 0;
    $message = '';
    $name = base::getString(request::get('name'));
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('add'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $myPath = base::getLRStr($path, $pathRoot, 'rightr');
      if (is_dir($path))
      {
        if (@mkdir($path . $name))
        {
          $status = 1;
          $account -> creatCurrentGenreLog('manage.log-addfolder-1', array('path' => $myPath . $name));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionAddFile()
  {
    $status = 0;
    $message = '';
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('add'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      if (is_dir($path))
      {
        $myPath = base::getLRStr($path, $pathRoot, 'rightr');
        $filename = @$_FILES['file']['name'];
        $tmp_filename = @$_FILES['file']['tmp_name'];
        $newfilepath = $path . self::ppSetFolderAndFileName($filename);
        if (move_uploaded_file($tmp_filename, $newfilepath))
        {
          $status = 1;
          $account -> creatCurrentGenreLog('manage.log-addfile-1', array('path' => $myPath . $filename));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionEditFile()
  {
    $status = 0;
    $message = '';
    $content = base::getString(request::getPost('content'));
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $myPath = base::getLRStr($path, $pathRoot, 'rightr');
      if (is_file($path))
      {
        if (@file_put_contents($path, $content))
        {
          $status = 1;
          $message = tpl::take('manage.text-tips-edit-done', 'lng');
          $account -> creatCurrentGenreLog('manage.log-editfile-1', array('path' => $myPath));
        }
        else $message = tpl::take('manage.text-tips-edit-error-2', 'lng');
      }
      else $message = tpl::take('manage.text-tips-edit-error-1', 'lng');
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionRename()
  {
    $status = 0;
    $message = '';
    $name = base::getString(request::get('name'));
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $myPath = base::getLRStr($path, $pathRoot, 'rightr');
      if (is_file($path) || is_dir($path))
      {
        if (@rename($path, base::getLRStr($path, '/', 'leftr') . '/' . $name))
        {
          $status = 1;
          $account -> creatCurrentGenreLog('manage.log-rename-1', array('name' => $name, 'path' => $myPath));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionDelete()
  {
    $status = 0;
    $message = '';
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('delete'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $myPath = base::getLRStr($path, $pathRoot, 'rightr');
      $path = self::ppSetFolderAndFileName($path);
      if (is_file($path))
      {
        if (@unlink($path))
        {
          $status = 1;
          $account -> creatCurrentGenreLog('manage.log-delete-1', array('path' => $myPath));
        }
      }
      else if (is_dir($path))
      {
        if (file::removeDir($path))
        {
          $status = 1;
          $account -> creatCurrentGenreLog('manage.log-delete-1', array('path' => $myPath));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>