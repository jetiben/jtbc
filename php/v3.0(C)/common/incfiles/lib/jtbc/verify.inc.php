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
        if (preg_match('/(^\d{18}$)|(^\d{17}(\d|X|x)$)/', $str))
        {
          $checkSum = 0;
          $cardBase = substr($str, 0, 17);
          $codeFactor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
          $verifyNumberList = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
          for ($ti = 0; $ti < strlen($cardBase); $ti ++)
          {
            $checkSum += substr($cardBase, $ti, 1) * $codeFactor[$ti];
          }
          $verifyNumber = $verifyNumberList[$checkSum % 11];
          if (strtoupper(substr($str, 17, 1)) == $verifyNumber) $bool = true;
        }
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