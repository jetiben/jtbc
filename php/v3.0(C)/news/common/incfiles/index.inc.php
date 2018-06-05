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
      $tmpstr = tpl::takeAndAssign('index.detail', $rs);
    }
    return $tmpstr;
  }

  public static function moduleList()
  {
    $page = base::getNum(request::get('page'), 0);
    $category = base::getNum(request::get('category'), 0);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $variable['-category'] = $category;
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
    $variable = array_merge($variable, $pagi -> getVars());
    $tmpstr = tpl::takeAndAssign('index.list', $rsAry, $variable);
    return $tmpstr;
  }
}
?>
