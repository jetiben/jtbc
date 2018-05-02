<?php
namespace jtbc;
class ui extends page {
  public static $account = null;
  public static $allowFiletype = 'txt,css,js,htm,html,asp,aspx,cs,php,java,jsp,config,sql,svg,jtbc';

  public static function account()
  {
    $account = null;
    if (!is_null(self::$account)) $account = self::$account;
    else $account = self::$account = new console\account();
    return $account;
  }

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
    $tmpstr = tpl::take('manage.list', 'tpl');
    $tpl = new tpl();
    $tpl -> tplString = $tmpstr;
    $loopString = $tpl -> getLoopString('{@}');
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
        $loopLineString = $loopString;
        $loopLineString = str_replace('{$path}', base::htmlEncode($path), $loopLineString);
        $loopLineString = str_replace('{$topic}', base::htmlEncode(self::ppGetFolderAndFileName($key)), $loopLineString);
        $loopLineString = str_replace('{$lasttime}', base::htmlEncode(date('Y-m-d H:i:s', filemtime($val))), $loopLineString);
        $loopLineString = str_replace('{$-val}', base::htmlEncode(urlencode($val . '/')), $loopLineString);
        $loopLineString = str_replace('{$-style}', '', $loopLineString);
        $loopLineString = str_replace('{$-linkurl}', '?type=list&amp;path=' . urlencode(self::ppGetFolderAndFileName($val . '/')), $loopLineString);
        $tpl -> insertLoopLine($loopLineString);
      }
      foreach ($files as $key => $val)
      {
        $loopLineString = $loopString;
        $loopLineString = str_replace('{$path}', base::htmlEncode($path), $loopLineString);
        $loopLineString = str_replace('{$topic}', base::htmlEncode(self::ppGetFolderAndFileName($key)), $loopLineString);
        $loopLineString = str_replace('{$lasttime}', base::htmlEncode(date('Y-m-d H:i:s', filemtime($val))), $loopLineString);
        $loopLineString = str_replace('{$-val}', base::htmlEncode(urlencode($val)), $loopLineString);
        $loopLineString = str_replace('{$-style}', 'background-image:url(' . ASSETSPATH . '/icon/filetype/' . base::htmlEncode(base::getLRStr(self::ppGetFolderAndFileName($key), '.', 'right')) . '.svg),url(' . ASSETSPATH . '/icon/filetype/others.svg)', $loopLineString);
        $loopLineString = str_replace('{$-linkurl}', '?type=edit&amp;path=' . urlencode(self::ppGetFolderAndFileName($val)), $loopLineString);
        $tpl -> insertLoopLine($loopLineString);
      }
    }
    $tmpstr = $tpl -> mergeTemplate();
    $tmpstr = str_replace('{$-path}', base::htmlEncode($path), $tmpstr);
    $tmpstr = str_replace('{$-path-nav}', $pathnavHTML, $tmpstr);
    $tmpstr = tpl::parse($tmpstr);
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
    if ($account -> checkPopedom(self::getPara('genre'), 'edit'))
    {
      if (base::checkInstr(self::$allowFiletype, $filetype, ','))
      {
        $tmpstr = tpl::take('manage.edit', 'tpl');
        $tmpstr = str_replace('{$-filemode}', base::htmlEncode($filemode), $tmpstr);
        $tmpstr = str_replace('{$-file-content}', base::htmlEncode(@file_get_contents($path)), $tmpstr);
      }
      else $tmpstr = tpl::take('manage.edit-lock', 'tpl');
      $tmpstr = str_replace('{$-path}', base::htmlEncode($path), $tmpstr);
      $tmpstr = str_replace('{$-path-urlencode}', urlencode($path), $tmpstr);
      $tmpstr = str_replace('{$-path-nav}', $pathnavHTML, $tmpstr);
      $tmpstr = tpl::parse($tmpstr);
      $tmpstr = $account -> replaceAccountTag($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleGetInfo()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $val = base::getString(request::get('val'));
    if (is_dir($val))
    {
      $message = $val;
      $info = base::getFolderInfo($val);
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
    $tmpstr = '';
    $status = 0;
    $message = '';
    $name = base::getString(request::get('name'));
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkPopedom(self::getPara('genre'), 'add'))
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
          $account -> creatAutoLog('manage.log-addfolder-1', array('path' => $myPath . $name));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionAddFile()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkPopedom(self::getPara('genre'), 'add'))
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
          $account -> creatAutoLog('manage.log-addfile-1', array('path' => $myPath . $filename));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionEditFile()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $content = base::getString(request::getPost('content'));
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkPopedom(self::getPara('genre'), 'edit'))
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
          $account -> creatAutoLog('manage.log-editfile-1', array('path' => $myPath));
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
    $tmpstr = '';
    $status = 0;
    $message = '';
    $name = base::getString(request::get('name'));
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkPopedom(self::getPara('genre'), 'edit'))
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
          $account -> creatAutoLog('manage.log-rename-1', array('name' => $name, 'path' => $myPath));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionDelete()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $path = base::getString(request::get('path'));
    $pathRoot = route::getActualRoute('./');
    $account = self::account();
    if (!$account -> checkPopedom(self::getPara('genre'), 'delete'))
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
          $account -> creatAutoLog('manage.log-delete-1', array('path' => $myPath));
        }
      }
      else if (is_dir($path))
      {
        if (base::removeDir($path))
        {
          $status = 1;
          $account -> creatAutoLog('manage.log-delete-1', array('path' => $myPath));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function getResult()
  {
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkLogin())
    {
      if ($account -> checkPopedom(self::getPara('genre')))
      {
        $tmpstr = parent::getResult();
      }
    }
    return $tmpstr;
  }
}
?>
