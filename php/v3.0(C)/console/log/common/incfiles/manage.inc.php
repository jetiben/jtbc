<?php
namespace jtbc;
class ui extends console\page {
  use console\snippet\batch;
  use console\snippet\delete;
  public static $batch = array('delete');

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
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
      $sql = new sql($db, $table, $prefix, 'id');
      $sqlstr = $sql -> sql;
      $pagi = new pagi($db);
      $rsAry = $pagi -> getDataAry($sqlstr, $page, $pagesize);
      if (is_array($rsAry))
      {
        foreach($rsAry as $rs)
        {
          $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
          $tpl -> insertLoopLine(tpl::parse($loopLineString));
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

  public static function moduleActionEmpty()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom('empty'))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $table = tpl::take('config.db_table', 'cfg');
      $db = conn::db();
      if (!is_null($db))
      {
        $re = $db -> exec('truncate table ' . $table);
        if (is_numeric($re))
        {
          $status = 1;
          $account -> creatCurrentGenreLog('manage.log-empty-1');
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>
