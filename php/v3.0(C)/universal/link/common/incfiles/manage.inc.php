<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  use universal\fragment\upload { doActionUpload as public moduleActionUpload; }
  public static $batch = array('publish', 'delete');

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    $group = base::getNum(request::get('group'), 1);
    if ($account -> checkCurrentGenrePopedom('add'))
    {
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-group}', base::htmlEncode($group), $tmpstr);
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
    $group = base::getNum(request::get('group'), 1);
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
      $sql -> group = $group;
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
      $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
      $variable['-batch-list'] = implode(',', $batchAry);
      $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
      $variable['-pagi-rscount'] = $pagi -> rscount;
      $variable['-pagi-pagenum'] = $pagi -> pagenum;
      $variable['-pagi-pagetotal'] = $pagi -> pagetotal;
      $variable['-group'] = $group;
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
}
?>
