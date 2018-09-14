<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  public static $batch = array('delete');

  public static function start()
  {
    parent::start();
    hook::add('doActionDeleteDone', function($argId){ universal\upload::unlinkByIds($argId); });
    hook::add('doActionBatchDeleteDone', function($argIds){ universal\upload::unlinkByIds($argIds); });
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
    $rqStatus = base::getNum(request::get('status'), -1);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $account = self::account();
    $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
    $variable['-batch-list'] = implode(',', $batchAry);
    $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
    $dal = new dal();
    if ($rqStatus != -1) $dal -> status = $rqStatus;
    $dal -> orderBy('id', 'desc');
    $pagi = new pagi($dal);
    $rsAry = $pagi -> getDataAry($page, $pagesize);
    $variable = array_merge($variable, $pagi -> getVars());
    $tmpstr = tpl::takeAndAssign('manage.list', $rsAry, $variable);
    $tmpstr = $account -> replaceAccountTag($tmpstr);
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }
}
?>