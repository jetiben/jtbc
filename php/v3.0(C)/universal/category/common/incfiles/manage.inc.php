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
    $pathnavHTML = tpl::take('::console.link', 'tpl', 0, array('text' => base::htmlEncode(tpl::take('global.' . $genre . ':category.title', 'cfg')) . ':/', 'link' => '?type=list&amp;genre=' . urlencode($genre)));
    $getChildHTML = function($argCFid) use ($genre, &$getChildHTML)
    {
      $tmpstr = '';
      $cfid = base::getNum($argCFid, 0);
      $dal = new dal();
      $dal -> id = $cfid;
      $rs = $dal -> select();
      if (is_array($rs))
      {
        $rsId = base::getNum($dal -> val('id'), 0);
        $rsFId = base::getNum($dal -> val('fid'), 0);
        $rsTopic = base::getString($dal -> val('topic'));
        $tmpstr = tpl::take('::console.link', 'tpl', 0, array('text' => base::htmlEncode($rsTopic) . '/', 'link' => '?type=list&amp;genre=' . urlencode($genre) . '&amp;fid=' . $cfid));
        if ($rsFId != 0) $tmpstr = $getChildHTML($rsFId) . $tmpstr;
      }
      return $tmpstr;
    };
    $pathnavHTML .= $getChildHTML($fid);
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
      $variable['-genre'] = $genre;
      $variable['-fid'] = $fid;
      $variable['-has_image'] = $hasImage;
      $variable['-has_intro'] = $hasIntro;
      $tmpstr = tpl::takeAndAssign('manage.add', null, $variable);
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
      $hasImage = 0;
      $hasIntro = 0;
      $allGenre = universal\category::getAllGenre();
      $dal = new dal();
      $dal -> id = $id;
      $rs = $dal -> select();
      if (is_array($rs))
      {
        $rsGenre = base::getString($dal -> val('genre'));
        if (in_array($rsGenre, $allGenre))
        {
          $hasImage = base::getNum(tpl::take('global.' . $rsGenre . ':category.has_image', 'cfg'), 0);
          $hasIntro = base::getNum(tpl::take('global.' . $rsGenre . ':category.has_intro', 'cfg'), 0);
        }
        $variable['-has_image'] = $hasImage;
        $variable['-has_intro'] = $hasIntro;
        $tmpstr = tpl::takeAndAssign('manage.edit', $rs, $variable);
        $tmpstr = $account -> replaceAccountTag($tmpstr);
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
      $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
      $variable['-batch-list'] = implode(',', $batchAry);
      $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
      $variable['-current-genre'] = $genre;
      $variable['-current-fid'] = $fid;
      $variable['-current-genre'] = $genre;
      $vars['-allgenre-select'] = universal\category::getAllGenreSelect($allGenre, $genre);
      $vars['-path-nav'] = self::ppGetPathNav($genre, $fid);
      $dal = new dal();
      $dal -> fid = $fid;
      $dal -> genre = $genre;
      $dal -> lang = $account -> getLang();
      $dal -> orderBy('order', 'asc');
      $dal -> orderBy('id', 'asc');
      $rsa = $dal -> selectAll();
      $tmpstr = tpl::takeAndAssign('manage.list', $rsa, $variable, $vars);
    }
    $tmpstr = $account -> replaceAccountTag($tmpstr);
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionAdd()
  {
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
      auto::pushAutoRequestErrorByTable($error);
      if (count($error) == 0)
      {
        $preset = array();
        $preset['order'] = 888888;
        $preset['lang'] = $account -> getLang();
        $preset['time'] = base::getDateTime();
        $re = auto::autoInsertByRequest($preset);
        if (is_numeric($re))
        {
          $status = 1;
          $id = auto::$lastInsertId;
          universal\upload::statusAutoUpdate(self::getParam('genre'), $id);
          $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $id));
        }
        else array_push($error, tpl::take('::console.text-tips-error-others', 'lng'));
      }
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionEdit()
  {
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
      auto::pushAutoRequestErrorByTable($error);
      if (count($error) == 0)
      {
        $re = auto::autoUpdateByRequest($id, null, 'fid,order,time,genre,lang');
        if (is_numeric($re))
        {
          $status = 1;
          universal\upload::statusAutoUpdate(self::getParam('genre'), $id);
          $message = tpl::take('manage.text-tips-edit-done', 'lng');
          $account -> creatCurrentGenreLog('manage.log-edit-1', array('id' => $id));
        }
        else array_push($error, tpl::take('::console.text-tips-error-others', 'lng'));
      }
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionSort()
  {
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    cache::removeByKey('universal-category');
    $ids = base::getString(request::get('ids'));
    if (!$account -> checkCurrentGenrePopedom('sort'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      if (base::checkIDAry($ids))
      {
        $status = 1;
        $index = 0;
        $idsAry = explode(',', $ids);
        foreach ($idsAry as $key => $val)
        {
          $id = base::getNum($val, 0);
          $re = auto::autoUpdateByVars($id, array('order' => $index));
          $index += 1;
        }
        $account -> creatCurrentGenreLog('manage.log-sort-1', array('id' => $ids));
      }
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>