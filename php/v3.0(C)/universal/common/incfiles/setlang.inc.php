<?php
namespace jtbc;
class ui extends page {
  public static function getRedirect()
  {
    $backurl = request::get('backurl');
    $language = request::get('language');
    if (base::isEmpty($backurl)) $backurl = route::getActualRoute('./');
    $lang = base::getNum(tpl::take('global.config.lang-' . $language, 'cfg'), -1);
    if ($lang != -1)
    {
      setcookie(APPNAME . 'config[language]', $language, time() + 31536000, COOKIESPATH);
    }
    return $backurl;
  }
}
?>