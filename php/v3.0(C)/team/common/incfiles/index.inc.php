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
    $page = base::getNum(request::get('page'), 0);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $dal = new dal();
    $dal -> publish = 1;
    $dal -> lang = self::getPara('lang');
    $pagi = new pagi($dal);
    $rsAry = $pagi -> getDataAry($page, $pagesize);
    $tmpstr = tpl::takeAndAssign('index.list', $rsAry, $pagi -> getVars());
    return $tmpstr;
  }
}
?>
