<?php
namespace jtbc;
class ui extends page {
  public static $account = null;

  public static function start()
  {
    env::$majorGenre = CONSOLEDIR;
    self::setParam('noCache', true);
    self::setParam('contentType', 'text/xml');
  }

  public static function account()
  {
    $account = null;
    if (!is_null(self::$account)) $account = self::$account;
    else $account = self::$account = new console\account(self::getParam('genre'));
    return $account;
  }

  public static function moduleDashbord()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkLogin())
    {
      $tmpstr = tpl::take('manage.dashbord', 'tpl');
      $variable['-sys-para-0'] = request::server('SERVER_SOFTWARE');
      $variable['-sys-para-1'] = VERSION;
      $variable['-sys-para-2'] = PHP_VERSION;
      $variable['-sys-para-3'] = DB;
      $variable['-sys-para-4'] = strtoupper(php_sapi_name());
      $variable['-sys-para-5'] = base::getSwapString(request::server('SERVER_ADDR'), gethostbyname(request::server('SERVER_NAME')));
      $variable['-sys-para-6'] = base::formatDate(base::getDateTime(), '100');
      $variable['-sys-para-7'] = get_cfg_var('max_execution_time');
      $variable['-sys-para-8'] = get_cfg_var('post_max_size');
      $variable['-sys-para-9'] = get_cfg_var('upload_max_filesize');
      $variable['-sys-para-10'] = get_cfg_var('memory_limit');
      $variable['-hello'] = tpl::replaceOriginalTagByAry(tpl::take('manage.text-hello', 'lng'), array('-username' => $account -> getMyInfo('username'), '-lastip' => $account -> getMyInfo('lastip'), '-lasttime' => $account -> getMyInfo('lasttime'), '-role-topic' => $account -> getRoleTopicById($account -> getMyInfo('role'))));
      $tmpstr = tpl::replaceTagByAry($tmpstr, $variable);
      $tmpstr = $account -> replaceAccountTag($tmpstr);
      $tmpstr = tpl::parse($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleModifyPassword()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkLogin())
    {
      $tmpstr = tpl::take('manage.modifypassword', 'tpl');
      $tmpstr = tpl::parse($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleGetLang()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkLogin())
    {
      $lang = $account -> getLang();
      $myRoleLang = base::getString($account -> getMyInfo('lang', 'role'));
      $allLangAry = tpl::take('::sel_lang.*', 'lng');
      $currentLang = tpl::take('::sel_lang.' . $lang, 'lng');
      $tmpstr = tpl::take('manage.getlang', 'tpl');
      $tpl = new tpl($tmpstr);
      $loopString = $tpl -> getLoopString('{@}');
      foreach ($allLangAry as $key => $val)
      {
        if ($account -> checkIsSuper() || base::checkInstr($myRoleLang, $key))
        {
          $loopLineString = $loopString;
          $loopLineString = str_replace('{$-lang-val}', base::htmlEncode($key), $loopLineString);
          $loopLineString = str_replace('{$-lang-text}', base::htmlEncode($val), $loopLineString);
          $tpl -> insertLoopLine($loopLineString);
        }
      }
      $tmpstr = $tpl -> getTpl();
      $tmpstr = str_replace('{$-current-lang-val}', base::htmlEncode($lang), $tmpstr);
      $tmpstr = str_replace('{$-current-lang-text}', base::htmlEncode($currentLang), $tmpstr);
      $tmpstr = tpl::parse($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleDefault()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if (!$account -> checkLogin())
    {
      $connTest = dal::connTest();
      $tmpstr = tpl::take('manage.login', 'tpl');
      $tmpstr = str_replace('{$-db-error}', $connTest? '0': '1', $tmpstr);
      $tmpstr = tpl::parse($tmpstr);
    }
    else
    {
      $tmpstr = tpl::take('manage.console', 'tpl');
      $tmpstr = str_replace('{$-account-username}', base::htmlEncode($account -> getMyInfo('username')), $tmpstr);
      $tmpstr = str_replace('{$-account-leftmenu}', $account -> getMyConsoleMenu(tpl::take('manage.part-leftmenu', 'tpl'), tpl::take('manage.part-leftmenu-dl', 'tpl')), $tmpstr);
      $tmpstr = tpl::parse($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionLogin()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $account = self::account();
    $username = request::getPost('username');
    $password = request::getPost('password');
    $remember = request::getPost('remember');
    if (!$account -> checkLoginErrorMax($username))
    {
      if ($account -> checkLoginInfo($username, $password))
      {
        $status = 1;
        $cookiesExpireTime = 0;
        if ($remember == '1') $cookiesExpireTime = time() + 31536000;
        setcookie(APPNAME . 'console[username]', $username, $cookiesExpireTime, COOKIESPATH, null, null, true);
        setcookie(APPNAME . 'console[authentication]', md5(WEBKEY . md5($password)), $cookiesExpireTime, COOKIESPATH, null, null, true);
        $account -> creatCurrentGenreLog('manage.log-login-1');
      }
      else $message = tpl::take('manage.msg-login-1', 'lng');
    }
    else
    {
      $message = tpl::take('manage.msg-login-2', 'lng');
      $message = str_replace('{$num}', tpl::take('config.login-error-max', 'cfg'), $message);
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionLogout()
  {
    $tmpstr = '';
    $status = 1;
    $message = '';
    $account = self::account();
    $account -> logout();
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionModifyPassword()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $password = request::getPost('password');
    $newpassword = request::getPost('newpassword');
    $newcpassword = request::getPost('newcpassword');
    if (base::isEmpty($password)) $message = tpl::take('manage.text-modifypassword-error-1', 'lng');
    else if (base::isEmpty($newpassword)) $message = tpl::take('manage.text-modifypassword-error-2', 'lng');
    else if ($newpassword != $newcpassword) $message = tpl::take('manage.text-modifypassword-error-3', 'lng');
    else
    {
      $account = self::account();
      if ($account -> checkLogin())
      {
        if ($account -> modifyPassword($password, $newpassword))
        {
          $status = 1;
          $message = tpl::take('manage.text-modifypassword-done', 'lng');
          $account -> creatCurrentGenreLog('manage.log-modifypassword-1');
        }
        else $message = tpl::take('manage.text-modifypassword-error-4', 'lng');
      }
      else $message = tpl::take('manage.text-modifypassword-error-4', 'lng');
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionSetLang()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $lang = base::getNum(request::get('lang'), 0);
    $account = self::account();
    if ($account -> checkLogin())
    {
      if ($account -> setLang($lang)) $status = 1;
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>