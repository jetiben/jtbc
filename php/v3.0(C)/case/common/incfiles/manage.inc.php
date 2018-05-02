<?php
namespace jtbc;
class ui extends page {
  public static $account = null;

  public static function account()
  {
    $account = null;
    if (!is_null(self::$account)) $account = self::$account;
    else $account = self::$account = new console\account();
    return $account;
  }

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $category = base::getNum(request::get('category'), 0);
    $account = self::account();
    if ($account -> checkPopedom(self::getPara('genre'), 'add'))
    {
      $table = tpl::take('config.db_table', 'cfg');
      $autoFieldFormatByTable = auto::getAutoFieldFormatByTable($table);
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-auto-field-format-by-table}', $autoFieldFormatByTable, $tmpstr);
      $tmpstr = str_replace('{$-category-nav}', universal\category::getCategoryNavByID(self::getPara('genre'), $account -> getLang(), $category), $tmpstr);
      $tmpstr = str_replace('{$-category-select}', universal\category::getCategorySelectByGenre(self::getPara('genre'), $account -> getLang(), $account -> getGenrePopedom(self::getPara('genre'), 'category'), 'id=' . $category), $tmpstr);
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
    if ($account -> checkPopedom(self::getPara('genre'), 'edit'))
    {
      $db = self::db();
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
          $tmpstr = str_replace('{$-category-select}', universal\category::getCategorySelectByGenre(self::getPara('genre'), $account -> getLang(), $account -> getGenrePopedom(self::getPara('genre'), 'category'), 'id=' . $rsCategory), $tmpstr);
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
    $db = self::db();
    if (!is_null($db))
    {
      $account = self::account();
      $myCategory = $account -> getGenrePopedom(self::getPara('genre'), 'category');
      $tmpstr = tpl::take('manage.list', 'tpl');
      $tpl = new tpl();
      $tpl -> tplString = $tmpstr;
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
      $batchAry = array();
      if ($account -> checkPopedom(self::getPara('genre'), 'publish')) array_push($batchAry, 'publish');
      if ($account -> checkPopedom(self::getPara('genre'), 'delete')) array_push($batchAry, 'delete');
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

  public static function moduleCategory()
  {
    $status = 1;
    $tmpstr = '';
    $fid = base::getNum(request::get('fid'), 0);
    $account = self::account();
    if ($account -> checkPopedom(self::getPara('genre')))
    {
      $prefix = universal\category::getPrefix();
      $myCategory = $account -> getGenrePopedom(self::getPara('genre'), 'category');
      $categoryAry = universal\category::getCategoryAryByGenre(self::getPara('genre'), $account -> getLang());
      $tmpstr = tpl::take('manage.category', 'tpl');
      $tpl = new tpl();
      $tpl -> tplString = $tmpstr;
      $loopString = $tpl -> getLoopString('{@}');
      foreach ($categoryAry as $myKey => $myVal)
      {
        if (is_array($myVal))
        {
          $rsid = base::getNum($myVal[$prefix . 'id'], -1);
          $rsfid = base::getNum($myVal[$prefix . 'fid'], -1);
          if ($rsfid == $fid && (base::isEmpty($myCategory) || base::checkInstr($myCategory, $rsid)))
          {
            $loopLineString = tpl::replaceTagByAry($loopString, $myVal, 10);
            $tpl -> insertLoopLine(tpl::parse($loopLineString));
          }
        }
      }
      $tmpstr = $tpl -> mergeTemplate();
      $tmpstr = tpl::parse($tmpstr);
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
    if (!$account -> checkPopedom(self::getPara('genre'), 'add') || !$account -> checkPopedomByCategory(self::getPara('genre'), $category))
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
        $db = self::db();
        if (!is_null($db))
        {
          $preset = array();
          $preset[$prefix . 'publish'] = 0;
          $preset[$prefix . 'lang'] = $account -> getLang();
          $preset[$prefix . 'time'] = base::getDateTime();
          if ($account -> checkPopedom(self::getPara('genre'), 'publish')) $preset[$prefix . 'publish'] = base::getNum(request::getPost('publish'), 0);
          $sqlstr = auto::getAutoRequestInsertSQL($table, $preset);
          $re = $db -> exec($sqlstr);
          if (is_numeric($re))
          {
            $status = 1;
            $id = $db -> lastInsertId;
            universal\upload::statusAutoUpdate(self::getPara('genre'), $id, $table, $prefix);
            $account -> creatAutoLog('manage.log-add-1', array('id' => $id));
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
    if (!$account -> checkPopedom(self::getPara('genre'), 'edit') || !$account -> checkPopedomByCategory(self::getPara('genre'), $category))
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
        $db = self::db();
        if (!is_null($db))
        {
          $preset = array();
          $preset[$prefix . 'publish'] = 0;
          $preset[$prefix . 'lang'] = $account -> getLang();
          if ($account -> checkPopedom(self::getPara('genre'), 'publish')) $preset[$prefix . 'publish'] = base::getNum(request::getPost('publish'), 0);
          $sqlstr = auto::getAutoRequestUpdateSQL($table, $prefix . 'id', $id, $preset);
          $re = $db -> exec($sqlstr);
          if (is_numeric($re))
          {
            $status = 1;
            universal\upload::statusAutoUpdate(self::getPara('genre'), $id, $table, $prefix);
            $message = tpl::take('manage.text-tips-edit-done', 'lng');
            $account -> creatAutoLog('manage.log-edit-1', array('id' => $id));
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
      $db = self::db();
      if (!is_null($db))
      {
        if ($batch == 'delete' && $account -> checkPopedom(self::getPara('genre'), 'delete'))
        {
          if ($db -> fieldSwitch($table, $prefix, 'delete', $ids)) $status = 1;
        }
        else if ($batch == 'publish' && $account -> checkPopedom(self::getPara('genre'), 'publish'))
        {
          if ($db -> fieldSwitch($table, $prefix, 'publish', $ids)) $status = 1;
        }
      }
      if ($status == 1)
      {
        $account -> creatAutoLog('manage.log-batch-1', array('id' => $ids, 'batch' => $batch));
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
    if (!$account -> checkPopedom(self::getPara('genre'), 'delete'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      $db = self::db();
      if (!is_null($db))
      {
        if ($db -> fieldSwitch($table, $prefix, 'delete', $id, 1))
        {
          $status = 1;
          $account -> creatAutoLog('manage.log-delete-1', array('id' => $id));
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
    $limit = base::getString(request::get('limit'));
    $account = self::account();
    if (!($account -> checkPopedom(self::getPara('genre'), 'add') || $account -> checkPopedom(self::getPara('genre'), 'edit')))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $upResult = universal\upload::up2self(@$_FILES['file'], $limit);
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
            $account -> creatAutoLog('manage.log-upload-1', array('filepath' => $paraArray['filepath']));
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message, $para);
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
