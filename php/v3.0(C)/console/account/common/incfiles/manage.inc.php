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
    $db = conn::db();
    if (!is_null($db))
    {
      $optionUnselected = tpl::take('global.config.xmlselect_unselect', 'tpl');
      $optionselected = tpl::take('global.config.xmlselect_select', 'tpl');
      if ($role == -1) $tmpstr .= $optionselected;
      else $tmpstr .= $optionUnselected;
      $tmpstr = str_replace('{$explain}', tpl::take(':/role:manage.text-super', 'lng'), $tmpstr);
      $tmpstr = str_replace('{$value}', '-1', $tmpstr);
      $table = tpl::take(':/role:config.db_table', 'cfg');
      $prefix = tpl::take(':/role:config.db_prefix', 'cfg');
      $sql = new sql($db, $table, $prefix, 'time');
      $sqlstr = $sql -> sql;
      $rsa = $db -> fetchAll($sqlstr);
      foreach ($rsa as $i => $rs)
      {
        $rsId = base::getNum($rs[$prefix . 'id'], 0);
        $rsTopic = base::getString($rs[$prefix . 'topic']);
        if ($role == $rsId) $tmpstr .= $optionselected;
        else $tmpstr .= $optionUnselected;
        $tmpstr = str_replace('{$explain}', base::htmlEncode($rsTopic), $tmpstr);
        $tmpstr = str_replace('{$value}', $rsId, $tmpstr);
      }
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
      $db = conn::db();
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
          $rsRole = base::getNum($rs[$prefix . 'role'], 0);
          $tmpstr = tpl::take('manage.edit', 'tpl');
          $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
          $tmpstr = str_replace('{$-select-role-html}', self::ppGetSelectRoleHTML($rsRole), $tmpstr);
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
    $db = conn::db();
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
          $loopLineString = str_replace('{$-role-topic}', base::htmlEncode($account -> getRoleTopicById($rs[$prefix . 'role'])), $loopLineString);
          $tpl -> insertLoopLine($loopLineString);
        }
      }
      $tmpstr = $tpl -> mergeTemplate();
      $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
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
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      auto::pushAutoRequestErrorByTable($error, $table);
      if (base::isEmpty($password)) array_push($error, tpl::take('manage.text-tips-field-error-1', 'lng'));
      if ($password != $cpassword) array_push($error, tpl::take('manage.text-tips-field-error-2', 'lng'));
      if (count($error) == 0)
      {
        $db = conn::db();
        if (!is_null($db))
        {
          $sql = new sql($db, $table, $prefix);
          $sql -> username = $username;
          $sqlstr = $sql -> sql;
          $rs = $db -> fetch($sqlstr);
          if (is_array($rs)) array_push($error, tpl::take('manage.text-tips-add-error-101', 'lng'));
          else
          {
            $preset = array();
            $preset[$prefix . 'password'] = md5($password);
            $preset[$prefix . 'time'] = base::getDateTime();
            $sqlstr = auto::getAutoInsertSQLByRequest($table, $preset);
            $re = $db -> exec($sqlstr);
            if (is_numeric($re))
            {
              $status = 1;
              $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $db -> lastInsertId));
            }
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
    $username = request::getPost('username');
    $password = request::getPost('password');
    $cpassword = request::getPost('cpassword');
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      auto::pushAutoRequestErrorByTable($error, $table);
      if (!base::isEmpty($password) && $password != $cpassword) array_push($error, tpl::take('manage.text-tips-field-error-2', 'lng'));
      if (count($error) == 0)
      {
        $db = conn::db();
        if (!is_null($db))
        {
          $sql = new sql($db, $table, $prefix);
          $sql -> username = $username;
          $sql -> setUnequal('id', $id);
          $sqlstr = $sql -> sql;
          $rs = $db -> fetch($sqlstr);
          if (is_array($rs)) array_push($error, tpl::take('manage.text-tips-edit-error-101', 'lng'));
          else
          {
            $specialFiled = $prefix . 'password';
            $sqlstr = auto::getAutoUpdateSQLByRequest($table, $prefix . 'id', $id, null, $specialFiled);
            $re = $db -> exec($sqlstr);
            if (is_numeric($re))
            {
              $status = 1;
              $message = tpl::take('manage.text-tips-edit-done', 'lng');
              $account -> creatCurrentGenreLog('manage.log-edit-1', array('id' => $id));
              if (!base::isEmpty($password)) $db -> exec("update " . $table . " set " . $prefix . "password='" . md5($password) . "' where " . $prefix . "id=" . $id);
            }
          }
        }
      }
    }
    if (count($error) != 0) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>
