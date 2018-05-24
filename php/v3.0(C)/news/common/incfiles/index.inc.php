<?php
namespace jtbc;
class ui extends page {
  public static function start()
  {
    self::setPara('adjunct_default', 'list');
    self::setPageTitle(tpl::take('index.title', 'lng'));
  }

  public static function moduleDetail()
  {
    $tmpstr = '';
    $id = base::getNum(request::get('id'), 0);
    $dal = new dal();
    $dal -> publish = 1;
    $dal -> id = $id;
    $rs = $dal -> select();
    if (is_array($rs))
    {
      self::setPageTitle($dal -> val($rs, 'topic'));
      $tmpstr = tpl::take('index.detail', 'tpl');
      $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
      $tmpstr = tpl::parse($tmpstr);
    }
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
    $category = base::getNum(request::get('category'), 0);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $tmpstr = tpl::take('index.list', 'tpl');
    $tpl = new tpl($tmpstr);
    $loopString = $tpl -> getLoopString('{@}');
    $dal = new dal();
    $dal -> publish = 1;
    $dal -> lang = self::getPara('lang');
    if ($category != 0)
    {
      self::setPageTitle(universal\category::getCategoryTopicByID(self::getPara('genre'), self::getPara('lang'), $category));
      $dal -> setIn('category', universal\category::getCategoryFamilyID(self::getPara('genre'), self::getPara('lang'), $category));
    }
    $dal -> orderBy('time', 'desc');
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
    $variable['-category'] = $category;
    $tmpstr = $tpl -> assign($variable) -> assign($pagi -> getVars()) -> getTpl();
    $tmpstr = tpl::replaceTagByAry($tmpstr, $variable);
    $tmpstr = tpl::parse($tmpstr);
    return $tmpstr;
  }
}
?>
