<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class verify
  {
    public static function isEmail($argStr)
    {
      $bool = false;
      $str = $argStr;
      if (!base::isEmpty($str))
      {
        if (preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $str)) $bool = true;
      }
      return $bool;
    }

    public static function isIDCard($argStr)
    {
      $bool = false;
      $str = $argStr;
      if (!base::isEmpty($str))
      {
        if (preg_match('/(^\d{18}$)|(^\d{17}(\d|X|x)$)/', $str)) $bool = true;
      }
      return $bool;
    }

    public static function isMobile($argStr)
    {
      $bool = false;
      $str = $argStr;
      if (!base::isEmpty($str))
      {
        if (preg_match('/^1\d{10}$/', $str)) $bool = true;
      }
      return $bool;
    }

    public static function isNumber($argStr)
    {
      $bool = false;
      $str = $argStr;
      if (!base::isEmpty($str))
      {
        if (preg_match('/^[0-9]*$/', $str)) $bool = true;
      }
      return $bool;
    }

    public static function isNatural($argStr)
    {
      $bool = false;
      $str = $argStr;
      if (!base::isEmpty($str))
      {
        if (preg_match('/^[a-zA-Z0-9_-]+$/', $str)) $bool = true;
      }
      return $bool;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>