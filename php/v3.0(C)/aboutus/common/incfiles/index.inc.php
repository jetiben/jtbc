<?php
namespace jtbc;
class ui extends page {
  public static function start()
  {
    self::setPara('adjunct_default', 'detail');
    self::setPageTitle(tpl::take('index.title', 'lng'));
  }

  public static function moduleDetail()
  {
    $tmpstr = '';
    $id = base::getNum(request::get('id'), 0);
    $dal = new dal();
    $dal -> publish = 1;
    $dal -> lang = self::getPara('lang');
    if ($id != 0) $dal -> id = $id;
    $dal -> orderBy('time', 'desc');
    $rs = $dal -> select();
    if (is_array($rs))
    {
      self::setPageTitle($dal -> val($rs, 'topic'));
      $tmpstr = tpl::takeAndAssign('index.detail', $rs);
    }
    return $tmpstr;
  }
}
?>
