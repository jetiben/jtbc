<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class hook
  {
    private static $hooks = array();

    public static function add($argName, $argCallback)
    {
      $name = $argName;
      $callback = $argCallback;
      self::$hooks[$name] = $callback;
    }

    public static function remove($argName)
    {
      $name = $argName;
      $result = false;
      if (array_key_exists($name, self::$hooks))
      {
        unset(self::$hooks[$name]);
        $result = true;
      }
      return $result;
    }

    public static function trigger()
    {
      $result = null;
      $hooks = self::$hooks;
      $args = func_get_args();
      if (!empty($args))
      {
        $name = $args[0];
        if (array_key_exists($name, $hooks))
        {
          $function = $hooks[$name];
          if (is_object($function))
          {
            $length = count($args);
            if ($length == 1) $result = $function();
            else
            {
              $myArgs = array();
              for ($i = 1; $i < $length; $i ++)
              {
                array_push($myArgs, $args[$i]);
              }
              $result = call_user_func_array($function, $myArgs);
            }
          }
        }
      }
      return $result;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>