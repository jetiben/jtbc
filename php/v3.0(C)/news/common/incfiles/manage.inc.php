<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  use universal\fragment\category { doCategory as public moduleCategory; }
  use universal\fragment\upload { doActionUpload as public moduleActionUpload; }
  public static $batch = array('publish', 'delete');

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $category = base::getNum(request::get('category'), 0);
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('add'))
    {
      $table = tpl::take('config.db_table', 'cfg');
      $autoFieldFormatByTable = auto::getAutoFieldFormatByTable($table);
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-auto-field-format-by-table}', $autoFieldFormatByTable, $tmpstr);
      $tmpstr = str_replace('{$-category-nav}', universal\category::getCategoryNavByID(self::getPara('genre'), $account -> getLang(), $category), $tmpstr);
      $tmpstr = str_replace('{$-category-select}', universal\category::getCategorySelectByGenre(self::getPara('genre'), $account -> getLang(), $account -> getCurrentGenrePopedom('category'), 'id=' . $category), $tmpstr);
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
    $category = base::getNum(request::get('category'), 0);
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
          $rsCategory = base::getNum($rs[$prefix . 'category'], 0);
          $tmpstr = tpl::take('manage.edit', 'tpl');
          $autoFieldFormatByTable = auto::getAutoFieldFormatByTable($table, 1);
          $tmpstr = str_replace('{$-auto-field-format-by-table}', $autoFieldFormatByTable, $tmpstr);
          $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
          $tmpstr = str_replace('{$-category-nav}', universal\category::getCategoryNavByID(self::getPara('genre'), $account -> getLang(), $category), $tmpstr);
          $tmpstr = str_replace('{$-category-select}', universal\category::getCategorySelectByGenre(self::getPara('genre'), $account -> getLang(), $account -> getCurrentGenrePopedom('category'), 'id=' . $rsCategory), $tmpstr);
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
    $category = base::getNum(request::get('category'), 0);
    $keyword = base::getString(request::get('keyword'));
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $db = conn::db();
    if (!is_null($db))
    {
      $account = self::account();
      $myCategory = $account -> getCurrentGenrePopedom('category');
      $tmpstr = tpl::take('manage.list', 'tpl');
      $tpl = new tpl($tmpstr);
      $loopString = $tpl -> getLoopString('{@}');
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      $sql = new sql($db, $table, $prefix, 'time');
      $sql -> lang = $account -> getLang();
      if ($publish != -1) $sql -> publish = $publish;
      if (!base::isEmpty($myCategory) && base::checkIDAry($myCategory)) $sql -> setIn('category', $myCategory);
      if ($category != 0) $sql -> setIn('category', universal\category::getCategoryFamilyID(self::getPara('genre'), $account -> getLang(), $category));
      if (!base::isEmpty($keyword)) $sql -> setFuzzyLike('topic', $keyword);
      $sqlstr = $sql -> sql;
      $pagi = new pagi($db);
      $rsAry = $pagi -> getDataAry($sqlstr, $page, $pagesize);
      if (is_array($rsAry))
      {
        foreach($rsAry as $rs)
        {
          $rsTopic = base::getString($rs[$prefix . 'topic']);
          $rsCategory = base::getNum($rs[$prefix . 'category'], 0);
          $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
          $loopLineString = str_replace('{$-category-topic}', base::htmlEncode(universal\category::getCategoryTopicByID(self::getPara('genre'), $account -> getLang(), $rsCategory)), $loopLineString);
          $loopLineString = str_replace('{$-topic-keyword-highlight}', base::replaceKeyWordHighlight(base::htmlEncode(base::replaceKeyWordHighlight($rsTopic, $keyword))), $loopLineString);
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
      $variable['-keyword'] = $keyword;
      $variable['-category'] = $category;
      $tmpstr = tpl::replaceTagByAry($tmpstr, $variable);
      $tmpstr = str_replace('{$-category-nav}', universal\category::getCategoryNavByID(self::getPara('genre'), $account -> getLang(), $category), $tmpstr);
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
    $category = base::getNum(request::getPost('category'), 0);
    if (!$account -> checkCurrentGenrePopedom('add') || !$account -> checkCurrentGenrePopedomByCategory($category))
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
          $sqlstr = auto::getAutoInsertSQLByRequest($table, $preset);
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
    $category = base::getNum(request::getPost('category'), 0);
    if (!$account -> checkCurrentGenrePopedom('edit') || !$account -> checkCurrentGenrePopedomByCategory($category))
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
          $sqlstr = auto::getAutoUpdateSQLByRequest($table, $prefix . 'id', $id, $preset);
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
