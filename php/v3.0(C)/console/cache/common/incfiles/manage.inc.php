<?php
namespace jtbc;
class ui extends console\page {
  public static $batch = array('delete');

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $path = route::getActualRoute('cache/');
    $account = self::account();
    $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
    $variable['-batch-list'] = implode(',', $batchAry);
    $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
    $cacheAry = array();
    if (is_dir($path))
    {
      $dir = @dir($path);
      while($entry = $dir -> read())
      {
        if (is_file($path . $entry) && is_numeric(strpos($entry, '.inc.php')))
        {
          $info['topic'] = base::getLRStr($entry, '.inc.php', 'leftr');
          $info['-urlencode-topic'] = urlencode(base::getLRStr($entry, '.inc.php', 'leftr'));
          $info['lasttime'] = date('Y-m-d H:i:s', filemtime($path . $entry));
          $info['size'] = base::formatFileSize(filesize($path . $entry));
          array_push($cacheAry, $info);
        }
      }
    }
    $tmpstr = tpl::takeAndAssign('manage.list', $cacheAry, $variable);
    $tmpstr = $account -> replaceAccountTag($tmpstr);
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionBatch()
  {
    $status = 0;
    $message = '';
    $account = self::account();
    $ids = base::getString(request::get('ids'));
    $batch = base::getString(request::get('batch'));
    if ($batch == 'delete' && $account -> checkCurrentGenrePopedom('delete'))
    {
      $idAry = explode(',', $ids);
      foreach ($idAry as $key => $val)
      {
        if (!base::isEmpty($val))
        {
          if (cache::remove($val)) $status = 1;
        }
      }
    }
    if ($status == 1)
    {
      $account -> creatCurrentGenreLog('manage.log-batch-1', array('batch' => $batch));
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionEmpty()
  {
    $status = 0;
    $message = '';
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('empty'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      if (cache::remove())
      {
        $status = 1;
        $account -> creatCurrentGenreLog('manage.log-empty-1');
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionDelete()
  {
    $status = 0;
    $message = '';
    $id = base::getString(request::get('id'));
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('delete'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      if (cache::remove($id))
      {
        $status = 1;
        $account -> creatCurrentGenreLog('manage.log-delete-1', array('id' => $id));
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>