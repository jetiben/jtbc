<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  use universal\fragment\upload { doActionUpload as public moduleActionUpload; }
  public static $batch = array('delete');

  public static function ppGetPathNav($argGenre, $argFid)
  {
    $genre = $argGenre;
    $fid = base::getNum($argFid, 0);
    $db = conn::db();
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
    if ($account -> checkCurrentGenrePopedom('add'))
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
    if ($account -> checkCurrentGenrePopedom('edit'))
    {
      $db = conn::db();
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
    $db = conn::db();
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
        $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
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
          $preset[$prefix . 'order'] = 888888;
          $preset[$prefix . 'lang'] = $account -> getLang();
          $preset[$prefix . 'time'] = base::getDateTime();
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
    cache::removeByKey('universal-category');
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
          $specialFiled = $prefix . 'fid,' . $prefix . 'order,' . $prefix . 'time,' . $prefix . 'genre,' . $prefix . 'lang';
          $sqlstr = auto::getAutoUpdateSQLByRequest($table, $prefix . 'id', $id, null, $specialFiled);
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

  public static function moduleActionSort()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    cache::removeByKey('universal-category');
    $ids = base::getString(request::get('ids'));
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      $db = conn::db();
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
          $account -> creatCurrentGenreLog('manage.log-sort-1', array('id' => $ids));
        }
      }
    }
    if (count($error) != 0) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>
