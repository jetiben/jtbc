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

  public static function ppGetPathNav($argGenre, $argFid)
  {
    $genre = $argGenre;
    $fid = base::getNum($argFid, 0);
    $db = self::db();
    $pathnavHTML = tpl::take('::console.link', 'tpl', 0, array('text' => base::htmlEncode(tpl::take('global.' . $genre . ':category.title', 'cfg')) . ':/', 'link' => '?type=list&amp;genre=' . urlencode($genre)));
    if (!is_null($db))
    {
      $getChildHTML = function($argCFid) use ($db, $genre, &$getChildHTML)
      {
        $tmpstr = '';
        $cfid = base::getNum($argCFid, 0);
        $table = tpl::take('config.db_table', 'cfg');
        $prefix = tpl::take('config.db_prefix', 'cfg');
        $sql = new sql($db, $table, $prefix);
        $sql -> id = $cfid;
        $sqlstr = $sql -> sql;
        $rs = $db -> fetch($sqlstr);
        if (is_array($rs))
        {
          $rsId = base::getNum($rs[$prefix . 'id'], 0);
          $rsFId = base::getNum($rs[$prefix . 'fid'], 0);
          $rsTopic = base::getString($rs[$prefix . 'topic']);
          $tmpstr = tpl::take('::console.link', 'tpl', 0, array('text' => base::htmlEncode($rsTopic) . '/', 'link' => '?type=list&amp;genre=' . urlencode($genre) . '&amp;fid=' . $cfid));
          if ($rsFId != 0) $tmpstr = $getChildHTML($rsFId) . $tmpstr;
        }
        return $tmpstr;
      };
      $pathnavHTML .= $getChildHTML($fid);
    }
    return $pathnavHTML;
  }

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $genre = request::get('genre');
    $fid = base::getNum(request::get('fid'), 0);
    $account = self::account();
    if ($account -> checkPopedom(self::getPara('genre'), 'add'))
    {
      $hasImage = 0;
      $hasIntro = 0;
      $allGenre = universal\category::getAllGenre();
      if (in_array($genre, $allGenre))
      {
        $hasImage = base::getNum(tpl::take('global.' . $genre . ':category.has_image', 'cfg'), 0);
        $hasIntro = base::getNum(tpl::take('global.' . $genre . ':category.has_intro', 'cfg'), 0);
      }
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-genre}', base::htmlEncode($genre), $tmpstr);
      $tmpstr = str_replace('{$-fid}', base::htmlEncode($fid), $tmpstr);
      $tmpstr = str_replace('{$-has_image}', base::htmlEncode($hasImage), $tmpstr);
      $tmpstr = str_replace('{$-has_intro}', base::htmlEncode($hasIntro), $tmpstr);
      $tmpstr = tpl::parse($tmpstr);
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
        $hasImage = 0;
        $hasIntro = 0;
        $allGenre = universal\category::getAllGenre();
        $table = tpl::take('config.db_table', 'cfg');
        $prefix = tpl::take('config.db_prefix', 'cfg');
        $sql = new sql($db, $table, $prefix);
        $sql -> id = $id;
        $sqlstr = $sql -> sql;
        $rs = $db -> fetch($sqlstr);
        if (is_array($rs))
        {
          $rsGenre = base::getString($rs[$prefix . 'genre']);
          if (in_array($rsGenre, $allGenre))
          {
            $hasImage = base::getNum(tpl::take('global.' . $rsGenre . ':category.has_image', 'cfg'), 0);
            $hasIntro = base::getNum(tpl::take('global.' . $rsGenre . ':category.has_intro', 'cfg'), 0);
          }
          $tmpstr = tpl::take('manage.edit', 'tpl');
          $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
          $tmpstr = str_replace('{$-has_image}', base::htmlEncode($hasImage), $tmpstr);
          $tmpstr = str_replace('{$-has_intro}', base::htmlEncode($hasIntro), $tmpstr);
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
    $fid = base::getNum(request::get('fid'), 0);
    $genre = base::getString(request::get('genre'));
    $db = self::db();
    if (!is_null($db))
    {
      $account = self::account();
      $allGenre = universal\category::getAllGenre();
      if ((base::isEmpty($genre) || !in_array($genre, $allGenre)))
      {
        $genre = '';
        if (!empty($allGenre)) $genre = universal\category::getFirstValidGenre($allGenre);
      }
      if (base::isEmpty($genre))
      {
        $tmpstr = tpl::take('manage.list-null', 'tpl');
        $tmpstr = tpl::parse($tmpstr);
      }
      else
      {
        $tmpstr = tpl::take('manage.list', 'tpl');
        $tpl = new tpl();
        $tpl -> tplString = $tmpstr;
        $loopString = $tpl -> getLoopString('{@}');
        $table = tpl::take('config.db_table', 'cfg');
        $prefix = tpl::take('config.db_prefix', 'cfg');
        $sql = new sql($db, $table, $prefix);
        $sql -> fid = $fid;
        $sql -> genre = $genre;
        $sql -> lang = $account -> getLang();
        $sql -> orderBy('order', 'asc');
        $sql -> orderBy('id', 'asc');
        $sqlstr = $sql -> sql;
        $rsa = $db -> fetchAll($sqlstr);
        foreach ($rsa as $i => $rs)
        {
          $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
          $tpl -> insertLoopLine(tpl::parse($loopLineString));
        }
        $tmpstr = $tpl -> mergeTemplate();
        $batchAry = array();
        if ($account -> checkPopedom(self::getPara('genre'), 'delete')) array_push($batchAry, 'delete');
        $variable['-batch-list'] = implode(',', $batchAry);
        $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
        $variable['-current-genre'] = $genre;
        $variable['-current-fid'] = $fid;
        $tmpstr = tpl::replaceTagByAry($tmpstr, $variable);
        $tmpstr = str_replace('{$-allgenre-select}', universal\category::getAllGenreSelect($allGenre, $genre), $tmpstr);
        $tmpstr = str_replace('{$-path-nav}', self::ppGetPathNav($genre, $fid), $tmpstr);
        $tmpstr = tpl::parse($tmpstr);
      }
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
    cache::removeByKey('universal-category');
    if (!$account -> checkPopedom(self::getPara('genre'), 'add'))
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
          $preset[$prefix . 'order'] = 888888;
          $preset[$prefix . 'lang'] = $account -> getLang();
          $preset[$prefix . 'time'] = base::getDateTime();
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
    cache::removeByKey('universal-category');
    $id = base::getNum(request::get('id'), 0);
    if (!$account -> checkPopedom(self::getPara('genre'), 'edit'))
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
          $specialFiled = $prefix . 'fid,' . $prefix . 'order,' . $prefix . 'time,' . $prefix . 'genre,' . $prefix . 'lang';
          $sqlstr = auto::getAutoRequestUpdateSQL($table, $prefix . 'id', $id, null, $specialFiled);
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

  public static function moduleActionSort()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    cache::removeByKey('universal-category');
    $ids = base::getString(request::get('ids'));
    if (!$account -> checkPopedom(self::getPara('genre'), 'edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      $db = self::db();
      if (!is_null($db))
      {
        if (base::checkIDAry($ids))
        {
          $status = 1;
          $table = tpl::take('config.db_table', 'cfg');
          $prefix = tpl::take('config.db_prefix', 'cfg');
          $index = 0;
          $idsAry = explode(',', $ids);
          foreach ($idsAry as $key => $val)
          {
            $id = base::getNum($val, 0);
            $db -> exec("update " . $table . " set " . $prefix . "order=" . $index . " where " . $prefix . "delete=0 and " . $prefix . "id=" . $id);
            $index += 1;
          }
          $account -> creatAutoLog('manage.log-sort-1', array('id' => $ids));
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
    cache::removeByKey('universal-category');
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
    cache::removeByKey('universal-category');
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
