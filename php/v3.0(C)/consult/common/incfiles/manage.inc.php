<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  public static $batch = array('dispose', 'delete');

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('add'))
    {
      $vars['-auto-field-format-by-table'] = auto::getAutoFieldFormatByTable();
      $tmpstr = tpl::takeAndAssign('manage.add', null, null, $vars);
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
        $vars['-auto-field-format-by-table'] = auto::getAutoFieldFormatByTable(1);
        $tmpstr = tpl::takeAndAssign('manage.edit', $rs, null, $vars);
        $tmpstr = $account -> replaceAccountTag($tmpstr);
      }
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $page = base::getNum(request::get('page'), 0);
    $dispose = base::getNum(request::get('dispose'), -1);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $account = self::account();
    $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
    $variable['-batch-list'] = implode(',', $batchAry);
    $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
    $dal = new dal();
    $dal -> lang = $account -> getLang();
    if ($dispose != -1) $dal -> dispose = $dispose;
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
        $preset['dispose'] = 0;
        $preset['userip'] = request::getRemortIP();
        $preset['lang'] = $account -> getLang();
        $preset['time'] = base::getDateTime();
        if ($account -> checkCurrentGenrePopedom('dispose')) $preset['dispose'] = base::getNum(request::getPost('dispose'), 0);
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
        $preset['dispose'] = 0;
        $preset['lang'] = $account -> getLang();
        if ($account -> checkCurrentGenrePopedom('dispose')) $preset['dispose'] = base::getNum(request::getPost('dispose'), 0);
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
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>