<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\console {
  use jtbc\page as oripage;
  class page extends oripage
  {
    public static $account = null;
    public static $checkCurrentGenre = true;

    public static function account()
    {
      $account = null;
      if (!is_null(self::$account)) $account = self::$account;
      else $account = self::$account = new account(self::getPara('genre'));
      return $account;
    }

    public static function getResult()
    {
      $tmpstr = '';
      $account = self::account();
      $class = get_called_class();
      if (method_exists($class, 'consolePageInit')) call_user_func(array($class, 'consolePageInit'));
      if ($account -> checkLogin())
      {
        if (self::$checkCurrentGenre == true)
        {
          if ($account -> checkCurrentGenrePopedom()) $tmpstr = parent::getResult();
        }
        else $tmpstr = parent::getResult();
      }
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>