<?php
namespace jtbc;
class ui extends page {
  public static function start()
  {
    self::setPageTitle(tpl::take('index.title', 'lng'));
  }
}
?>