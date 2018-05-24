<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  public static $batch = array('publish', 'delete');

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('add'))
    {
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-auto-field-format-by-table}', auto::getAutoFieldFormatByTable(), $tmpstr);
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
      $dal = new dal();
      $dal -> id = $id;
      $rs = $dal -> select();
      if (is_array($rs))
      {
        $tmpstr = tpl::take('manage.edit', 'tpl');
        $tmpstr = str_replace('{$-auto-field-format-by-table}', auto::getAutoFieldFormatByTable(1), $tmpstr);
        $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
        $tmpstr = tpl::parse($tmpstr);
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
    $page = base::getNum(request::get('page'), 0);
    $publish = base::getNum(request::get('publish'), -1);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $account = self::account();
    $tmpstr = tpl::take('manage.list', 'tpl');
    $tpl = new tpl($tmpstr);
    $loopString = $tpl -> getLoopString('{@}');
    $dal = new dal();
    $dal -> lang = $account -> getLang();
    if ($publish != -1) $dal -> publish = $publish;
    $dal -> orderBy('time', 'desc');
    $pagi = new pagi($dal);
    $rsAry = $pagi -> getDataAry($page, $pagesize);
    if (is_array($rsAry))
    {
      foreach($rsAry as $rs)
      {
        $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
        $tpl -> insertLoopLine(tpl::parse($loopLineString));
      }
    }
    $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
    $variable['-batch-list'] = implode(',', $batchAry);
    $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
    $tmpstr = $tpl -> assign($variable) -> assign($pagi -> getVars()) -> getTpl();
    $tmpstr = tpl::parse($tmpstr);
    $tmpstr = $account -> replaceAccountTag($tmpstr);
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
      auto::pushAutoRequestErrorByTable($error);
      if (count($error) == 0)
      {
        $preset = array();
        $preset['publish'] = 0;
        $preset['lang'] = $account -> getLang();
        $preset['time'] = base::getDateTime();
        if ($account -> checkCurrentGenrePopedom('publish')) $preset['publish'] = base::getNum(request::getPost('publish'), 0);
        $re = auto::autoInsertByRequest($preset);
        if (is_numeric($re))
        {
          $status = 1;
          $id = auto::$lastInsertId;
          $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $id));
        }
        else array_push($error, tpl::take('::console.text-tips-error-others', 'lng'));
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
      auto::pushAutoRequestErrorByTable($error);
      if (count($error) == 0)
      {
        $preset = array();
        $preset['publish'] = 0;
        $preset['lang'] = $account -> getLang();
        if ($account -> checkCurrentGenrePopedom('publish')) $preset['publish'] = base::getNum(request::getPost('publish'), 0);
        $re = auto::autoUpdateByRequest($id, $preset);
        if (is_numeric($re))
        {
          $status = 1;
          $message = tpl::take('manage.text-tips-edit-done', 'lng');
          $account -> creatCurrentGenreLog('manage.log-edit-1', array('id' => $id));
        }
        else array_push($error, tpl::take('::console.text-tips-error-others', 'lng'));
      }
    }
    if (count($error) != 0) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>
