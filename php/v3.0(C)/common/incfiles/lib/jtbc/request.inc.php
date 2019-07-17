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
      else $result = self::getHTTPParam($name, 'get');
      return $result;
    }

    public static function getCookie($argName, $argChildName = null)
    {
      $result = '';
      $name = $argName;
      $childName = $argChildName;
      $cookieArray = $_COOKIE;
      if (array_key_exists(APPNAME . $name, $cookieArray))
      {
        $tempResult = $cookieArray[APPNAME . $name];
        if (is_null($childName)) $result = $tempResult;
        else
        {
          if (is_array($tempResult))
          {
            if (array_key_exists($childName, $tempResult)) $result = $tempResult[$childName];
          }
        }
      }
      return $result;
    }

    public static function getPost($argName)
    {
      $name = $argName;
      $result = self::post($name);
      return $result;
    }

    public static function getFile($argName)
    {
      $result = null;
      $name = $argName;
      $filesArray = $_FILES;
      if (array_key_exists($name, $filesArray)) $result = $filesArray[$name];
      return $result;
    }

    public static function getForeLang()
    {
      $language = is_null(env::$language)? LANGUAGE: env::$language;
      $lang = base::getNum(tpl::take('global.config.lang-' . $language, 'cfg'), 0);
      $cookieValue = base::getString(self::getCookie('config', 'language'));
      if (!base::isEmpty($cookieValue))
      {
        $cookieLang = base::getNum(tpl::take('global.config.lang-' . $cookieValue, 'cfg'), -1);
        if ($cookieLang != -1) $lang = $cookieLang;
      }
      return $lang;
    }

    public static function getHeaderParam($argName)
    {
      $name = $argName;
      $result = self::server('HTTP_' . strtoupper($name));
      return $result;
    }

    public static function getHTTPParam($argName, $argType = 'auto')
    {
      $result = '';
      $name = $argName;
      $type = $argType;
      if ($type == 'auto')
      {
        $result = self::getHTTPParam($name, 'post');
        if (base::isEmpty($result)) $result = self::getHTTPParam($name, 'get');
      }
      else if ($type == 'post')
      {
        $post = $_POST;
        if (array_key_exists($name, $post))
        {
          $value = $post[$name];
          if (is_array($value)) $result = json_encode($value);
          else $result = base::getString($value);
        }
      }
      else if ($type == 'get')
      {
        $get = $_GET;
        if (array_key_exists($name, $get))
        {
          $value = $get[$name];
          if (is_array($value)) $result = json_encode($value);
          else $result = base::getString($value);
        }
      }
      return $result;
    }

    public static function getRemortIP()
    {
      $IPaddress = self::server('HTTP_X_FORWARDED_FOR');
      if (base::isEmpty($IPaddress)) $IPaddress = self::server('HTTP_CLIENT_IP');
      if (base::isEmpty($IPaddress)) $IPaddress = self::server('REMOTE_ADDR');
      return $IPaddress;
    }

    public static function post($argName = null)
    {
      $name = $argName;
      if (is_null($name)) $result = $_POST;
      else $result = self::getHTTPParam($name, 'post');
      return $result;
    }

    public static function isHTTPS()
    {
      $bool = false;
      if (self::server('HTTPS') == 'on' || self::server('HTTP_X_FORWARDED_PROTO') == 'https' || self::server('HTTP_X_CLIENT_SCHEME') == 'https') $bool = true;
      return $bool;
    }

    public static function isMobileAgent()
    {
      $bool = false;
      $userAgent = strtolower(self::server('HTTP_USER_AGENT'));
      if (strpos($userAgent, 'android') && strpos($userAgent, 'mobile')) $bool = true;
      else if (strpos($userAgent, 'iphone')) $bool = true;
      else if (strpos($userAgent, 'ipod')) $bool = true;
      return $bool;
    }

    public static function server($argName = null)
    {
      $name = $argName;
      $result = null;
      if (is_null($name)) $result = $_SERVER;
      else
      {
        $server = $_SERVER;
        if (array_key_exists($name, $server)) $result = $server[$name];
      }
      return $result;
    }

    public static function replaceQuerystring($argStrers, $argValue = '', $argUrs = '')
    {
      $tmpstr = '';
      $strers = $argStrers;
      $value = $argValue;
      $urs = $argUrs;
      if (base::isEmpty($urs)) $urs = self::server('QUERY_STRING');
      if (base::getLeft($urs, 1) == '?') $urs = base::getLRStr($urs, '?', 'rightr');
      $myAry = array();
      if (!base::isEmpty($urs))
      {
        $paramAry = explode('&', $urs);
        foreach ($paramAry as $key => $val)
        {
          $paramItem = trim($val);
          if (!base::isEmpty($paramItem))
          {
            $paramItemAry = explode('=', $paramItem);
            if (count($paramItemAry) == 2) $myAry[$paramItemAry[0]] = $paramItemAry[1];
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