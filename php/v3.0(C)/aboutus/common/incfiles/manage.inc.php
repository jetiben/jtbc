<?php
namespace jtbc;
class ui extends console\page {
  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('add'))
    {
      $table = tpl::take('config.db_table', 'cfg');
      $autoFieldFormatByTable = auto::getAutoFieldFormatByTable($table);
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-auto-field-format-by-table}', $autoFieldFormatByTable, $tmpstr);
      $tmpstr = tpl::parse($tmpstr);
      $tmpstr = $account -> replaceAccountTag($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

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
          $autoFieldFormatByTable = auto::getAutoFieldFormatByTable($table, 1);
          $tmpstr = str_replace('{$-auto-field-format-by-table}', $autoFieldFormatByTable, $tmpstr);
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
    $publish = base::getNum(request::get('publish'), -1);
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
      $sql = new sql($db, $table, $prefix, 'time');
      $sql -> lang = $account -> getLang();
      if ($publish != -1) $sql -> publish = $publish;
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
      $batchAry = array();
      if ($account -> checkCurrentGenrePopedom('publish')) array_push($batchAry, 'publish');
      if ($account -> checkCurrentGenrePopedom('delete')) array_push($batchAry, 'delete');
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
    $tmpstr = '';
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('add'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      auto::pushAutoRequestErrorByTable($error, $table);
      if (count($error) == 0)
      {
        $db = conn::db();
        if (!is_null($db))
        {
          $preset = array();
          $preset[$prefix . 'publish'] = 0;
          $preset[$prefix . 'lang'] = $account -> getLang();
          $preset[$prefix . 'time'] = base::getDateTime();
          if ($account -> checkCurrentGenrePopedom('publish')) $preset[$prefix . 'publish'] = base::getNum(request::getPost('publish'), 0);
          $sqlstr = auto::getAutoRequestInsertSQL($table, $preset);
          $re = $db -> exec($sqlstr);
          if (is_numeric($re))
          {
            $status = 1;
            $id = $db -> lastInsertId;
            universal\upload::statusAutoUpdate(self::getPara('genre'), $id, $table, $prefix);
            $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $id));
          }
        }
      }
    }
    if (count($error) != 0) $message = implode('|', $error);
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
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      auto::pushAutoRequestErrorByTable($error, $table);
      if (count($error) == 0)
      {
        $db = conn::db();
        if (!is_null($db))
        {
          $preset = array();
          $preset[$prefix . 'publish'] = 0;
          $preset[$prefix . 'lang'] = $account -> getLang();
          if ($account -> checkCurrentGenrePopedom('publish')) $preset[$prefix . 'publish'] = base::getNum(request::getPost('publish'), 0);
          $sqlstr = auto::getAutoRequestUpdateSQL($table, $prefix . 'id', $id, $preset);
          $re = $db -> exec($sqlstr);
          if (is_numeric($re))
          {
            $status = 1;
            universal\upload::statusAutoUpdate(self::getPara('genre'), $id, $table, $prefix);
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

  public static function moduleActionBatch()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $account = self::account();
    $ids = base::getString(request::get('ids'));
    $batch = base::getString(request::get('batch'));
    if (base::checkIDAry($ids))
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      $db = conn::db();
      if (!is_null($db))
      {
        if ($batch == 'delete' && $account -> checkCurrentGenrePopedom('delete'))
        {
          if ($db -> fieldSwitch($table, $prefix, 'delete', $ids)) $status = 1;
        }
        else if ($batch == 'publish' && $account -> checkCurrentGenrePopedom('publish'))
        {
          if ($db -> fieldSwitch($table, $prefix, 'publish', $ids)) $status = 1;
        }
      }
      if ($status == 1)
      {
        $account -> creatCurrentGenreLog('manage.log-batch-1', array('id' => $ids, 'batch' => $batch));
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
    $id = base::getNum(request::get('id'), 0);
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('delete'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      $db = conn::db();
      if (!is_null($db))
      {
        if ($db -> fieldSwitch($table, $prefix, 'delete', $id, 1))
        {
          $status = 1;
          $account -> creatCurrentGenreLog('manage.log-delete-1', array('id' => $id));
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionUpload()
  {
    $status = 0;
    $message = '';
    $para = '';
    $account = self::account();
    if (!($account -> checkCurrentGenrePopedom('add') || $account -> checkCurrentGenrePopedom('edit')))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $upResult = universal\upload::up2self(@$_FILES['file']);
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
            $account -> creatCurrentGenreLog('manage.log-upload-1', array('filepath' => $paraArray['filepath']));
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message, $para);
    return $tmpstr;
  }
}
?>
