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
      $variable['-group'] = $group;
      $tmpstr = tpl::takeAndAssign('manage.add', null, $variable);
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
        $tmpstr = tpl::takeAndAssign('manage.edit', $rs);
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
    $group = base::getNum(request::get('group'), 1);
    $page = base::getNum(request::get('page'), 0);
    $publish = base::getNum(request::get('publish'), -1);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $account = self::account();
    $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
    $variable['-batch-list'] = implode(',', $batchAry);
    $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
    $variable['-group'] = $group;
    $dal = new dal();
    $dal -> group = $group;
    $dal -> lang = $account -> getLang();
    if ($publish != -1) $dal -> publish = $publish;
    $dal -> orderBy('time', 'desc');
    $pagi = new pagi($dal);
    $rsAry = $pagi -> getDataAry($page, $pagesize);
    $variable = array_merge($variable, $pagi -> getVars());
    $tmpstr = tpl::takeAndAssign('manage.list', $rsAry, $variable);
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
          universal\upload::statusAutoUpdate(self::getPara('genre'), $id);
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
          universal\upload::statusAutoUpdate(self::getPara('genre'), $id);
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
}
?>
