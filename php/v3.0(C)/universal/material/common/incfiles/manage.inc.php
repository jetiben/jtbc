<?php
namespace jtbc;
class ui extends console\page {
  use console\snippet\batch;
  use console\snippet\delete;
  public static $batch = array('delete');

  public static function moduleEdit()
  {
    $status = 1;
    $tmpstr = '';
    $id = base::getNum(request::get('id'), 0);
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('edit'))
    {
      $db = conn::db();
      if (!is_null($db))
      {
        $table = tpl::take('config.db_table', 'cfg');
        $prefix = tpl::take('config.db_prefix', 'cfg');
        $sql = new sql($db, $table, $prefix);
        $sql -> id = $id;
        $sqlstr = $sql -> sql;
        $rs = $db -> fetch($sqlstr);
        if (is_array($rs))
        {
          $tmpstr = tpl::take('manage.edit', 'tpl');
          $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
          $tmpstr = tpl::parse($tmpstr);
          $tmpstr = $account -> replaceAccountTag($tmpstr);
        }
      }
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
    $filegroup = base::getNum(request::get('filegroup'), -1);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $db = conn::db();
    if (!is_null($db))
    {
      $account = self::account();
      $tmpstr = tpl::take('manage.list', 'tpl');
      $tpl = new tpl();
      $tpl -> tplString = $tmpstr;
      $loopString = $tpl -> getLoopString('{@}');
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      $sql = new sql($db, $table, $prefix);
      $sql -> lang = $account -> getLang();
      if ($filegroup != -1) $sql -> filegroup = $filegroup;
      $sql -> orderBy('time');
      $sql -> orderBy('id');
      $sqlstr = $sql -> sql;
      $pagi = new pagi($db);
      $rsAry = $pagi -> getDataAry($sqlstr, $page, $pagesize);
      if (is_array($rsAry))
      {
        foreach($rsAry as $rs)
        {
          $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
          $tpl -> insertLoopLine(tpl::parse($loopLineString));
        }
      }
      $tmpstr = $tpl -> mergeTemplate();
      $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
      $variable['-batch-list'] = implode(',', $batchAry);
      $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
      $variable['-pagi-rscount'] = $pagi -> rscount;
      $variable['-pagi-pagenum'] = $pagi -> pagenum;
      $variable['-pagi-pagetotal'] = $pagi -> pagetotal;
      $tmpstr = tpl::replaceTagByAry($tmpstr, $variable);
      $tmpstr = tpl::parse($tmpstr);
      $tmpstr = $account -> replaceAccountTag($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionAdd()
  {
    $status = 0;
    $message = '';
    $para = '';
    $account = self::account();
    if (!($account -> checkCurrentGenrePopedom('add')))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $db = conn::db();
      if (!is_null($db))
      {
        $upResult = universal\upload::up2self(@$_FILES['file'], '', '', false);
        $upResultArray = json_decode($upResult, 1);
        if (is_array($upResultArray))
        {
          $status = $upResultArray['status'];
          $message = $upResultArray['message'];
          $para = $upResultArray['para'];
          if ($status == 1)
          {
            $paraArray = json_decode($para, 1);
            if (is_array($paraArray))
            {
              $table = tpl::take('config.db_table', 'cfg');
              $prefix = tpl::take('config.db_prefix', 'cfg');
              $preset = array();
              $preset[$prefix . 'topic'] = $paraArray['filename'];
              $preset[$prefix . 'filepath'] = $paraArray['filepath'];
              $preset[$prefix . 'fileurl'] = $paraArray['fileurl'];
              $preset[$prefix . 'filetype'] = $paraArray['filetype'];
              $preset[$prefix . 'filesize'] = $paraArray['filesize'];
              $preset[$prefix . 'filegroup'] = base::getFileGroup($paraArray['filetype']);
              $preset[$prefix . 'lang'] = $account -> getLang();
              $preset[$prefix . 'time'] = base::getDateTime();
              $sqlstr = auto::getAutoInsertSQLByVars($table, $preset);
              $re = $db -> exec($sqlstr);
              if (is_numeric($re))
              {
                $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $db -> lastInsertId, 'filepath' => $paraArray['filepath']));
              }
            }
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message, $para);
    return $tmpstr;
  }

  public static function moduleActionReplace()
  {
    $status = 0;
    $message = '';
    $account = self::account();
    $id = base::getNum(request::get('id'), 0);
    if (!($account -> checkCurrentGenrePopedom('edit')))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $db = conn::db();
      if (!is_null($db))
      {
        $table = tpl::take('config.db_table', 'cfg');
        $prefix = tpl::take('config.db_prefix', 'cfg');
        $sql = new sql($db, $table, $prefix);
        $sql -> id = $id;
        $sqlstr = $sql -> sql;
        $rs = $db -> fetch($sqlstr);
        if (is_array($rs))
        {
          $rsFilePath = base::getString($rs[$prefix . 'filepath']);
          $upResult = universal\upload::up2self(@$_FILES['file'], '', $rsFilePath, false);
          $upResultArray = json_decode($upResult, 1);
          if (is_array($upResultArray))
          {
            $status = $upResultArray['status'];
            $message = $upResultArray['message'];
            $para = $upResultArray['para'];
            if ($status == 1)
            {
              $paraArray = json_decode($para, 1);
              if (is_array($paraArray))
              {
                $preset = array();
                $preset[$prefix . 'topic'] = $paraArray['filename'];
                $preset[$prefix . 'filesize'] = $paraArray['filesize'];
                $sqlstr = auto::getAutoUpdateSQLByVars($table, $prefix . 'id', $id, $preset);
                $re = $db -> exec($sqlstr);
                if (is_numeric($re))
                {
                  $account -> creatCurrentGenreLog('manage.log-replace-1', array('id' => $id));
                }
              }
            }
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionEdit()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    $id = base::getNum(request::get('id'), 0);
    $topic = request::getPost('topic');
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      if (base::isEmpty($topic)) array_push($error, tpl::take('manage.text-tips-edit-error-1', 'lng'));
      if (count($error) == 0)
      {
        $db = conn::db();
        if (!is_null($db))
        {
          $table = tpl::take('config.db_table', 'cfg');
          $prefix = tpl::take('config.db_prefix', 'cfg');
          $specialFiled = $prefix . 'filepath,' . $prefix . 'fileurl,' . $prefix . 'filetype,' . $prefix . 'filesize,' . $prefix . 'filegroup,' . $prefix . 'hot';
          $preset = array();
          $preset[$prefix . 'lang'] = $account -> getLang();
          $sqlstr = auto::getAutoRequestUpdateSQL($table, $prefix . 'id', $id, $preset, $specialFiled);
          $re = $db -> exec($sqlstr);
          if (is_numeric($re))
          {
            $status = 1;
            $message = tpl::take('manage.text-tips-edit-done', 'lng');
            $account -> creatCurrentGenreLog('manage.log-edit-1', array('id' => $id));
          }
        }
      }
    }
    if (count($error) != 0) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>
