<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  public static $batch = array('delete');

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $account = self::account();
    $tmpstr = tpl::take('manage.list', 'tpl');
    $tpl = new tpl($tmpstr);
    $loopString = $tpl -> getLoopString('{@}');
    $dal = new dal();
    $dal -> orderBy('id', 'desc');
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
      $dal = new dal();
      $re = $dal -> truncate();
      if (is_numeric($re))
      {
        $status = 1;
        $account -> creatCurrentGenreLog('manage.log-empty-1');
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>
