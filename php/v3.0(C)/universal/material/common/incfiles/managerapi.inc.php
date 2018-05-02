<?php
namespace jtbc;
class ui extends page {
  public static $account = null;

  public static function account()
  {
    $account = null;
    if (!is_null(self::$account)) $account = self::$account;
    else $account = self::$account = new console\account(self::getPara('genre'));
    return $account;
  }

  public static function ppGetFileJSON($argRs, $argPrefix = '')
  {
    $tmpstr = '';
    $rs = $argRs;
    $prefix = $argPrefix;
    if (is_array($rs))
    {
      $paraArray = array();
      $paraArray['filename'] = $rs[$prefix . 'topic'];
      $paraArray['filesize'] = $rs[$prefix . 'filesize'];
      $paraArray['filetype'] = $rs[$prefix . 'filetype'];
      $paraArray['filepath'] = $rs[$prefix . 'filepath'];
      $paraArray['fileurl'] = $rs[$prefix . 'fileurl'];
      $paraArray['filesizetext'] = base::formatFileSize(base::getNum($paraArray['filesize'], 0));
      $tmpstr = json_encode($paraArray);
    }
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $selectmode = 'single';
    $mode = base::getString(request::get('mode'));
    $keyword = base::getString(request::get('keyword'));
    $sort = base::getNum(request::get('sort'), 1);
    $filegroup = base::getNum(request::get('filegroup'), -1);
    if ($mode == 'multiple') $selectmode = 'multiple';
    $db = self::db();
    if (!is_null($db))
    {
      $account = self::account();
      $tmpstr = tpl::take('managerapi.list', 'tpl');
      $tpl = new tpl();
      $tpl -> tplString = $tmpstr;
      $loopString = $tpl -> getLoopString('{@}');
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      $sql = new sql($db, $table, $prefix);
      $sql -> lang = $account -> getLang();
      if ($filegroup != -1) $sql -> filegroup = $filegroup;
      if (!base::isEmpty($keyword)) $sql -> setFuzzyLike('topic', $keyword);
      if ($sort == 1) $sql -> orderBy('hot');
      else $sql -> orderBy('time');
      $sql -> orderBy('id');
      $sqlstr = $sql -> sql . ' limit 100';
      $rsa = $db -> fetchAll($sqlstr);
      foreach ($rsa as $i => $rs)
      {
        $rsTopic = base::getString($rs[$prefix . 'topic']);
        $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
        $loopLineString = str_replace('{$-filejson}', base::htmlEncode(self::ppGetFileJSON($rs, $prefix)), $loopLineString);
        $loopLineString = str_replace('{$-topic-keyword-highlight}', base::replaceKeyWordHighlight(base::htmlEncode(base::replaceKeyWordHighlight($rsTopic, $keyword))), $loopLineString);
        $tpl -> insertLoopLine(tpl::parse($loopLineString));
      }
      $tmpstr = $tpl -> mergeTemplate();
      $variable['-selectmode'] = $selectmode;
      $variable['-filegroup'] = $filegroup;
      $variable['-sort'] = $sort;
      $variable['-keyword'] = $keyword;
      $tmpstr = tpl::replaceTagByAry($tmpstr, $variable);
      $tmpstr = tpl::parse($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionHot()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $id = base::getNum(request::get('id'), 0);
    $table = tpl::take('config.db_table', 'cfg');
    $prefix = tpl::take('config.db_prefix', 'cfg');
    $db = self::db();
    if (!is_null($db))
    {
      if ($db -> fieldNumberAdd($table, $prefix, 'hot', $id)) $status = 1;
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
      $tmpstr = parent::getResult();
    }
    return $tmpstr;
  }
}
?>
