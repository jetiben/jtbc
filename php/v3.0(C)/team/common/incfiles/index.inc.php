<?php
namespace jtbc;
class ui extends page {
  public static function start()
  {
    self::setPara('adjunct_default', 'list');
    self::setPageTitle(tpl::take('index.title', 'lng'));
  }

  public static function moduleList()
  {
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $tmpstr = tpl::take('index.list', 'tpl');
    $tpl = new tpl($tmpstr);
    $loopString = $tpl -> getLoopString('{@}');
    $dal = new dal();
    $dal -> publish = 1;
    $dal -> lang = self::getPara('lang');
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
    $tmpstr = $tpl -> assign($pagi -> getVars()) -> getTpl();
    $tmpstr = tpl::parse($tmpstr);
    return $tmpstr;
  }
}
?>
