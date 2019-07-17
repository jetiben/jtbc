<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\console {
  use jtbc\env;
  use jtbc\page as oripage;
  class page extends oripage
  {
    public static $account = null;
    public static $checkCurrentGenre = true;

    public static function start()
    {
      self::setParam('noCache', true);
      self::setParam('contentType', 'text/xml');
    }

    public static function account()
    {
      $account = null;
      if (!is_null(self::$account)) $account = self::$account;
      else $account = self::$account = new account(self::getParam('genre'));
      return $account;
    }

    public static function getResult()
    {
      $tmpstr = '';
      env::$majorGenre = CONSOLEDIR;
      $account = self::account();
      $class = get_called_class();
      if (method_exists($class, 'consolePageInit')) call_user_func(array($class, 'consolePageInit'));
      if ($account -> checkLogin())
      {
        parent::setParam('consoleLang', $account -> getLang());
        parent::setParam('consoleLangText', $account -> getLangText());
        if (self::$checkCurrentGenre == true)
        {
          if ($account -> checkCurrentGenrePopedom()) $tmpstr = parent::getResult();
        }
        else $tmpstr = parent::getResult();
      }
      else parent::$errorCode = 403;
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>