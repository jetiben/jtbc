<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  public static $batch = array('lock', 'delete');

  protected static function ppGetSelectRoleHTML($argRole = -1)
  {
    $tmpstr = '';
    $role = base::getNum($argRole, -1);
    $optionUnselected = tpl::take('global.config.xmlselect_unselect', 'tpl');
    $optionselected = tpl::take('global.config.xmlselect_select', 'tpl');
    if ($role == -1) $tmpstr .= $optionselected;
    else $tmpstr .= $optionUnselected;
    $tmpstr = str_replace('{$explain}', tpl::take(':/role:manage.text-super', 'lng'), $tmpstr);
    $tmpstr = str_replace('{$value}', '-1', $tmpstr);
    $table = tpl::take(':/role:config.db_table', 'cfg');
    $prefix = tpl::take(':/role:config.db_prefix', 'cfg');
    $dal = new dal($table, $prefix);
    $dal -> orderBy('time', 'desc');
    $rsa = $dal -> selectAll();
    foreach ($rsa as $i => $rs)
    {
      $rsId = base::getNum($dal -> val($rs, 'id'), 0);
      $rsTopic = base::getString($dal -> val($rs, 'topic'));
      if ($role == $rsId) $tmpstr .= $optionselected;
      else $tmpstr .= $optionUnselected;
      $tmpstr = str_replace('{$explain}', base::htmlEncode($rsTopic), $tmpstr);
      $tmpstr = str_replace('{$value}', $rsId, $tmpstr);
    }
    return $tmpstr;
  }

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('add'))
    {
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-select-role-html}', self::ppGetSelectRoleHTML(), $tmpstr);
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
        $rsRole = base::getNum($dal -> val($rs, 'role'), 0);
        $tmpstr = tpl::take('manage.edit', 'tpl');
        $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
        $tmpstr = str_replace('{$-select-role-html}', self::ppGetSelectRoleHTML($rsRole), $tmpstr);
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
    $lock = base::getNum(request::get('lock'), 0);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $account = self::account();
    $tmpstr = tpl::take('manage.list', 'tpl');
    $tpl = new tpl($tmpstr);
    $loopString = $tpl -> getLoopString('{@}');
    $dal = new dal();
    if ($lock == 1) $dal -> lock = 1;
    $dal -> orderBy('time', 'desc');
    $pagi = new pagi($dal);
    $rsAry = $pagi -> getDataAry($page, $pagesize);
    if (is_array($rsAry))
    {
      foreach($rsAry as $rs)
      {
        $rsRole = base::getNum($dal -> val($rs, 'role'), 0);
        $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
        $loopLineString = str_replace('{$-role-topic}', base::htmlEncode($account -> getRoleTopicById($rsRole)), $loopLineString);
        $tpl -> insertLoopLine($loopLineString);
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
    $username = request::getPost('username');
    $password = request::getPost('password');
    $cpassword = request::getPost('cpassword');
    if (!$account -> checkCurrentGenrePopedom('add'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      auto::pushAutoRequestErrorByTable($error);
      if (base::isEmpty($password)) array_push($error, tpl::take('manage.text-tips-field-error-1', 'lng'));
      if ($password != $cpassword) array_push($error, tpl::take('manage.text-tips-field-error-2', 'lng'));
      if (count($error) == 0)
      {
        $dal = new dal();
        $dal -> username = $username;
        $rs = $dal -> select();
        if (is_array($rs)) array_push($error, tpl::take('manage.text-tips-add-error-101', 'lng'));
        else
        {
          $preset = array();
          $preset['password'] = md5($password);
          $preset['time'] = base::getDateTime();
          $re = auto::autoInsertByRequest($preset);
          if (is_numeric($re))
          {
            $status = 1;
            $id = auto::$lastInsertId;
            $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $id));
          }
        }
      }
    }
    if (!empty($error)) $message = implode('|', $error);
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
    $username = request::getPost('username');
    $password = request::getPost('password');
    $cpassword = request::getPost('cpassword');
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      auto::pushAutoRequestErrorByTable($error);
      if (!base::isEmpty($password) && $password != $cpassword) array_push($error, tpl::take('manage.text-tips-field-error-2', 'lng'));
      if (count($error) == 0)
      {
        $dal = new dal();
        $dal -> username = $username;
        $dal -> setUnequal('id', $id);
        $rs = $dal -> select();
        if (is_array($rs)) array_push($error, tpl::take('manage.text-tips-edit-error-101', 'lng'));
        else
        {
          $re = auto::autoUpdateByRequest($id, null, 'password');
          if (is_numeric($re))
          {
            $status = 1;
            $message = tpl::take('manage.text-tips-edit-done', 'lng');
            $account -> creatCurrentGenreLog('manage.log-edit-1', array('id' => $id));
            if (!base::isEmpty($password)) $re = auto::autoUpdateByVars($id, array('password' => md5($password)));
          }
        }
      }
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>
