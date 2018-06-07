<?php
namespace jtbc;
class ui extends page {
  public static function start()
  {
    self::setPageTitle(tpl::take('index.title', 'lng'));
  }

  public static function moduleDefault()
  {
    $tmpstr = '';
    if (SITESTATUS == 0)
    {
      $tmpstr = tpl::take('index.index', 'tpl');
      $tmpstr = tpl::parse($tmpstr);
    }
    return $tmpstr;
  }
}
?>