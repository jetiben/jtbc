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

  protected static function ppGetPopedomJson($argPopedom)
  {
    $popedomJson = '';
    $popedom = $argPopedom;
    $popedomJsonArray = array();
    if (!base::isEmpty($popedom))
    {
      $popedomArray = explode('|', $popedom);
      foreach ($popedomArray as $key => $val)
      {
        if (!base::isEmpty($val))
        {
          $valArray = explode(':', $val);
          if (count($valArray) == 3)
          {
            $name = $valArray[0];
            $segment = $valArray[1];
            $category = $valArray[2];
            if (!base::isEmpty($segment)) $segment = base::getLRStr($segment, ',', 'leftr');
            if (!base::isEmpty($category)) $category = base::getLRStr($category, ',', 'leftr');
            $popedomJsonArray[$name] = array();
            $popedomJsonArray[$name]['segment'] = $segment;
            if (!base::isEmpty($category)) $popedomJsonArray[$name]['category'] = $category;
          }
        }
      }
      $popedomJson = json_encode($popedomJsonArray);
    }
    return $popedomJson;
  }

  protected static function ppGetSelectPopedomHTML($argPre = '', $argPopedom = '')
  {
    $has = false;
    $pre = $argPre;
    $popedom = $argPopedom;
    $popedomArray = array();
    $folder = smart::getFolderByGuide();
    $folderAry = explode('|+|', $folder);
    $categoryAry = universal\category::getAllGenre();
    $tmpstr = tpl::take('manage.part-select-popedom', 'tpl');
    $tpl = new tpl();
    $tpl -> tplString = $tmpstr;
    $loopString = $tpl -> getLoopString('{@}');
    if (!base::isEmpty($popedom)) $popedomArray = json_decode($popedom, true);
    foreach($folderAry as $key => $val)
    {
      if (!base::isEmpty($val))
      {
        $myval = $val;
        if (!base::isEmpty($pre))
        {
          $mypos = strpos($myval, $pre);
          if (!is_numeric($mypos) || $mypos != 0) $myval = '';
          else $myval = base::getLRStr($myval, $pre, 'rightr');
        }
        if (!base::isEmpty($myval) && !is_numeric(strpos($myval, '/')))
        {
          $has = true;
          $checked = '';
          $guide = json_decode(tpl::take('global.' . $val . ':guide.guide', 'cfg'), true);
          $guidePopedom = tpl::take('global.' . $val . ':guide.popedom', 'cfg');
          $chindMenu = self::ppGetSelectPopedomHTML($pre . $myval . '/', $popedom);
          if (array_key_exists($val, $popedomArray)) $checked = ' checked="checked"';
          $loopLineString = $loopString;
          $loopLineString = str_replace('{$genre}', base::htmlEncode($val), $loopLineString);
          $loopLineString = str_replace('{$text}', base::htmlEncode($guide['text']), $loopLineString);
          $loopLineString = str_replace('{$-level}', base::htmlEncode(substr_count($val, '/') + 1), $loopLineString);
          $loopLineString = str_replace('{$-checked}', $checked, $loopLineString);
          if (base::isEmpty($guidePopedom)) $loopLineString = str_replace('{$-popedom}', '', $loopLineString);
          else
          {
            $popedomSelect = '';
            $guidePopedomArray = explode(',', $guidePopedom);
            foreach ($guidePopedomArray as $pkey => $pval)
            {
              $checkedp = '';
              if (array_key_exists($val, $popedomArray) && is_array($popedomArray[$val]))
              {
                if (base::checkInstr(@$popedomArray[$val]['segment'], $pval, ',')) $checkedp = ' checked="checked"';
              }
              $popedomSelect .= tpl::take('manage.part-select-popedom-option', 'tpl');
              $popedomSelect = str_replace('{$genre}', base::htmlEncode($val), $popedomSelect);
              $popedomSelect = str_replace('{$popedom}', base::htmlEncode($pval), $popedomSelect);
              $popedomSelect = str_replace('{$text}', base::htmlEncode(tpl::take('::console.text-popedom-' . $pval, 'lng')), $popedomSelect);
              $popedomSelect = str_replace('{$-checked}', $checkedp, $popedomSelect);
            }
            $loopLineString = str_replace('{$-popedom}', $popedomSelect, $loopLineString);
          }
          if (!in_array($val, $categoryAry) || !universal\category::isValidGenre($val)) $loopLineString = str_replace('{$-category}', '', $loopLineString);
          else
          {
            $categoryValue = '';
            if (array_key_exists($val, $popedomArray) && is_array($popedomArray[$val])) $categoryValue = @$popedomArray[$val]['category'];
            $loopLineString = str_replace('{$-category}', tpl::take('manage.part-select-popedom-category', 'tpl', 1, array('category' => base::htmlEncode($categoryValue))), $loopLineString);
          }
          $loopLineString = str_replace('{$-child}', $chindMenu, $loopLineString);
          $tpl -> insertLoopLine($loopLineString);
        }
      }
    }
    $tmpstr = $tpl -> mergeTemplate();
    if ($has == false) $tmpstr = '';
    return $tmpstr;
  }

  public static function ppGetSelectCategoryHTML($argGenre, $argLang)
  {
    $genre = $argGenre;
    $lang = base::getNum($argLang, 0);
    $tmpstr = tpl::take('manage.part-select-category-li', 'tpl');
    $prefix = universal\category::getPrefix();
    $categoryAry = universal\category::getCategoryAryByGenre($genre, $lang);
    $getCategoryChild = function($argFid) use ($prefix, $categoryAry, &$getCategoryChild)
    {
      $afid = base::getNum($argFid, 0);
      $tmpstr = tpl::take('manage.part-select-category-dd', 'tpl');
      $tpl = new tpl();
      $tpl -> tplString = $tmpstr;
      $loopString = $tpl -> getLoopString('{@}');
      foreach ($categoryAry as $myKey => $myVal)
      {
        if (is_array($myVal))
        {
          $rsid = base::getNum($myVal[$prefix . 'id'], 0);
          $rsfid = base::getNum($myVal[$prefix . 'fid'], -1);
          if ($rsfid == $afid)
          {
            $loopLineString = $loopString;
            foreach ($myVal as $key => $val)
            {
              $key = base::getLRStr($key, '_', 'rightr');
              $loopLineString = str_replace('{$' . $key . '}', base::htmlEncode($val), $loopLineString);
            }
            $loopLineString = str_replace('{$-child}', $getCategoryChild($rsid), $loopLineString);
            $tpl -> insertLoopLine($loopLineString);
          }
        }
      }
      $tmpstr = $tpl -> mergeTemplate();
      $tmpstr = tpl::parse($tmpstr);
      return $tmpstr;
    };
    $tpl = new tpl();
    $tpl -> tplString = $tmpstr;
    $loopString = $tpl -> getLoopString('{@}');
    foreach ($categoryAry as $myKey => $myVal)
    {
      if (is_array($myVal))
      {
        $rsid = base::getNum($myVal[$prefix . 'id'], 0);
        $rsfid = base::getNum($myVal[$prefix . 'fid'], -1);
        if ($rsfid == 0)
        {
          $loopLineString = $loopString;
          foreach ($myVal as $key => $val)
          {
            $key = base::getLRStr($key, '_', 'rightr');
            $loopLineString = str_replace('{$' . $key . '}', base::htmlEncode($val), $loopLineString);
          }
          $loopLineString = str_replace('{$-child}', $getCategoryChild($rsid), $loopLineString);
          $tpl -> insertLoopLine($loopLineString);
        }
      }
    }
    $tmpstr = $tpl -> mergeTemplate();
    $tmpstr = tpl::parse($tmpstr);
    return $tmpstr;
  }

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkPopedom(self::getPara('genre'), 'add'))
    {
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-select-popedom-html}', self::ppGetSelectPopedomHTML(), $tmpstr);
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
          $rsPopedom = base::getString($rs[$prefix . 'popedom']);
          $tmpstr = tpl::take('manage.edit', 'tpl');
          $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
          $tmpstr = str_replace('{$-select-popedom-html}', self::ppGetSelectPopedomHTML('', $rsPopedom), $tmpstr);
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
    $lock = base::getNum(request::get('lock'), 0);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $db = self::db();
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
      if ($lock == 1) $sql -> lock = 1;
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
      if ($account -> checkPopedom(self::getPara('genre'), 'delete')) array_push($batchAry, 'delete');
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

  public static function moduleCategory()
  {
    $status = 1;
    $tmpstr = '';
    $genre = base::getString(request::get('genre'));
    $account = self::account();
    if ($account -> checkPopedom(self::getPara('genre'), 'add') || $account -> checkPopedom(self::getPara('genre'), 'edit'))
    {
      $langAry = tpl::take('::sel_lang.*', 'lng');
      $tmpstr = tpl::take('manage.category', 'tpl');
      $tpl = new tpl();
      $tpl -> tplString = $tmpstr;
      $loopString = $tpl -> getLoopString('{@}');
      foreach ($langAry as $key => $val)
      {
        $loopLineString = $loopString;
        $loopLineString = str_replace('{$key}', base::htmlEncode($key), $loopLineString);
        $loopLineString = str_replace('{$val}', base::htmlEncode($val), $loopLineString);
        $loopLineString = str_replace('{$-select-category-html}', self::ppGetSelectCategoryHTML($genre, $key), $loopLineString);
        $tpl -> insertLoopLine($loopLineString);
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
    $popedom = request::getPost('popedom');
    $popedomJson = self::ppGetPopedomJson($popedom);
    if (!$account -> checkPopedom(self::getPara('genre'), 'add'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      smart::pushAutoRequestErrorByTable($error, $table);
      if (count($error) == 0)
      {
        $db = self::db();
        if (!is_null($db))
        {
          $preset = array();
          $preset[$prefix . 'popedom'] = $popedomJson;
          $preset[$prefix . 'time'] = base::getDateTime();
          $sqlstr = smart::getAutoRequestInsertSQL($table, $preset);
          $re = $db -> exec($sqlstr);
          if (is_numeric($re))
          {
            $status = 1;
            $account -> creatAutoLog('manage.log-add-1', array('id' => $db -> lastInsertId));
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
    $popedom = request::getPost('popedom');
    $popedomJson = self::ppGetPopedomJson($popedom);
    if (!$account -> checkPopedom(self::getPara('genre'), 'edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      smart::pushAutoRequestErrorByTable($error, $table);
      if (count($error) == 0)
      {
        $db = self::db();
        if (!is_null($db))
        {
          $preset = array();
          $preset[$prefix . 'popedom'] = $popedomJson;
          $sqlstr = smart::getAutoRequestUpdateSQL($table, $prefix . 'id', $id, $preset);
          $re = $db -> exec($sqlstr);
          if (is_numeric($re))
          {
            $status = 1;
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
