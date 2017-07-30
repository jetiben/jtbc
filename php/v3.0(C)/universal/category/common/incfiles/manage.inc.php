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
        $sqlstr ="select * from " . $table . " where " . $prefix . "delete=0 and " . $prefix . "id=" . $cfid;
        $rq = $db -> query($sqlstr);
        $rs = $rq -> fetch();
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
    $account = self::account();
    if ($account -> checkPopedom(self::getPara('genre'), 'add'))
    {
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = tpl::parse($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleEdit()
  {
    $status = 1;
    $tmpstr = '';
    $id = base::getNum(request::getHTTPPara('id', 'get'), 0);
    $account = self::account();
    if ($account -> checkPopedom(self::getPara('genre'), 'edit'))
    {
      $db = self::db();
      if (!is_null($db))
      {
        $table = tpl::take('config.db_table', 'cfg');
        $prefix = tpl::take('config.db_prefix', 'cfg');
        $sqlstr = "select * from " . $table . " where " . $prefix . "delete=0 and " . $prefix . "id=" . $id;
        $rq = $db -> query($sqlstr);
        $rs = $rq -> fetch();
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
    $fid = base::getNum(request::getHTTPPara('fid', 'get'), 0);
    $genre = base::getString(request::getHTTPPara('genre', 'get'));
    $db = self::db();
    if (!is_null($db))
    {
      $account = self::account();
      $allGenre = universal\category::getAllGenre();
      if ((base::isEmpty($genre) || !in_array($genre, $allGenre)))
      {
        $genre = '';
        if (!empty($allGenre)) $genre = current($allGenre);
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
        $sqlstr = "select * from " . $table . " where " . $prefix . "fid=" . $fid . " and " . $prefix . "genre='" . addslashes($genre) . "' and " . $prefix . "delete=0 and " . $prefix . "lang=" . $account -> getLang() . " order by " . $prefix . "order asc," . $prefix . "id asc";
        $rq = $db -> query($sqlstr);
        while($rs = $rq -> fetch())
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
    $topic = request::getHTTPPara('topic', 'post');
    if (!$account -> checkPopedom(self::getPara('genre'), 'add'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      if (base::isEmpty($topic)) array_push($error, tpl::take('manage.text-tips-add-error-1', 'lng'));
      if (count($error) == 0)
      {
        $db = self::db();
        if (!is_null($db))
        {
          $table = tpl::take('config.db_table', 'cfg');
          $prefix = tpl::take('config.db_prefix', 'cfg');
          $specialFiled = $prefix . 'id,' . $prefix . 'delete';
          $preset = array();
          $preset[$prefix . 'order'] = 888888;
          $preset[$prefix . 'lang'] = $account -> getLang();
          $preset[$prefix . 'time'] = base::getDateTime();
          $sqlstr = smart::getAutoRequestInsertSQL($table, $specialFiled, $preset);
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
    $id = base::getNum(request::getHTTPPara('id', 'get'), 0);
    $topic = request::getHTTPPara('topic', 'post');
    if (!$account -> checkPopedom(self::getPara('genre'), 'edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      if (base::isEmpty($topic)) array_push($error, tpl::take('manage.text-tips-edit-error-1', 'lng'));
      if (count($error) == 0)
      {
        $db = self::db();
        if (!is_null($db))
        {
          $table = tpl::take('config.db_table', 'cfg');
          $prefix = tpl::take('config.db_prefix', 'cfg');
          $specialFiled = $prefix . 'id,' . $prefix . 'fid,' . $prefix . 'order,' . $prefix . 'time,' . $prefix . 'genre,' . $prefix . 'lang,' . $prefix . 'delete';
          $sqlstr = smart::getAutoRequestUpdateSQL($table, $specialFiled, $prefix . 'id', $id);
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

  public static function moduleActionSort()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    $ids = base::getString(request::getHTTPPara('ids', 'get'));
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
    $ids = base::getString(request::getHTTPPara('ids', 'get'));
    $batch = base::getString(request::getHTTPPara('batch', 'get'));
    if (base::checkIDAry($ids))
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      if ($batch == 'delete' && $account -> checkPopedom(self::getPara('genre'), 'delete'))
      {
        if (smart::dbFieldSwitch($table, $prefix, 'delete', $ids)) $status = 1;
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
    $id = base::getNum(request::getHTTPPara('id', 'get'), 0);
    $account = self::account();
    if (!$account -> checkPopedom(self::getPara('genre'), 'delete'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      if (smart::dbFieldSwitch($table, $prefix, 'delete', $id, 1))
      {
        $status = 1;
        $account -> creatAutoLog('manage.log-delete-1', array('id' => $id));
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
