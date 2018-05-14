<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class request
  {
    public static function get($argName = null)
    {
      $name = $argName;
      if (is_null($name)) $result = $_GET;
      else $result = self::getHTTPPara($name, 'get');
      return $result;
    }

    public static function getPost($argName)
    {
      $name = $argName;
      $tmpstr = self::post($name);
      return $tmpstr;
    }

    public static function getForeLang()
    {
      $language = LANGUAGE;
      $lang = base::getNum(tpl::take('global.config.lang-' . $language, 'cfg'), 0);
      $cookieValue = base::getString(@$_COOKIE[APPNAME . 'config']['language']);
      if (!base::isEmpty($cookieValue))
      {
        $cookieLang = base::getNum(tpl::take('global.config.lang-' . $cookieValue, 'cfg'), -1);
        if ($cookieLang != -1) $lang = $cookieLang;
      }
      return $lang;
    }

    public static function getHTTPPara($argName, $argType = 'auto')
    {
      $tmpstr = '';
      $name = $argName;
      $type = $argType;
      if ($type == 'auto')
      {
        $tmpstr = base::getString(@$_POST[$name]);
        if (base::isEmpty($tmpstr)) $tmpstr = base::getString(@$_GET[$name]);
      }
      else if ($type == 'post')
      {
        $tmpstr = base::getString(@$_POST[$name]);
      }
      else if ($type == 'get')
      {
        $tmpstr = base::getString(@$_GET[$name]);
      }
      return $tmpstr;
    }

    public static function getRemortIP()
    {
      $IPaddress = '';
      if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $IPaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
      else if (isset($_SERVER['HTTP_CLIENT_IP'])) $IPaddress = $_SERVER['HTTP_CLIENT_IP'];
      else $IPaddress = $_SERVER['REMOTE_ADDR'];
      return $IPaddress;
    }

    public static function post($argName = null)
    {
      $name = $argName;
      if (is_null($name)) $result = $_POST;
      else $result = self::getHTTPPara($name, 'post');
      return $result;
    }

    public static function replaceQuerystring($argStrers, $argValue = '', $argUrs = '')
    {
      $tmpstr = '';
      $strers = $argStrers;
      $value = $argValue;
      $urs = $argUrs;
      if (base::isEmpty($urs)) $urs = @$_SERVER['QUERY_STRING'];
      if (base::getLeft($urs, 1) == '?') $urs = base::getLRStr($urs, '?', 'rightr');
      $myAry = array();
      if (!base::isEmpty($urs))
      {
        $paraAry = explode('&', $urs);
        foreach ($paraAry as $key => $val)
        {
          $paraItem = trim($val);
          if (!base::isEmpty($paraItem))
          {
            $paraItemAry = explode('=', $paraItem);
            if (count($paraItemAry) == 2) $myAry[$paraItemAry[0]] = $paraItemAry[1];
          }
        }
      }
      if (is_array($strers))
      {
        foreach ($strers as $key => $val) $myAry[$key] = $val;
      }
      else
      {
        $myAry[$strers] = $value;
      }
      foreach ($myAry as $key => $val)
      {
        if (!is_null($val)) $tmpstr .= $key . '=' . $val . '&';
      }
      if (!base::isEmpty($tmpstr)) $tmpstr = base::getLRStr($tmpstr, '&', 'leftr');
      $tmpstr = '?' . $tmpstr;
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>