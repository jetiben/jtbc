<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class hook
  {
    private static $hooks = array();

    public static function add($argName, $argFunction)
    {
      $name = $argName;
      $function = $argFunction;
      $hooks = self::$hooks;
      if (!array_key_exists($name, $hooks))
      {
        self::appoint($name, $function);
      }
      else
      {
        $hook = $hooks[$name];
        if (is_object($hook))
        {
          $group = array();
          array_unshift($group, $hook);
          array_unshift($group, $function);
          self::appoint($name, $group);
        }
        else if (is_array($hook))
        {
          array_unshift($hook, $function);
          self::appoint($name, $hook);
        }
        else self::appoint($name, $function);
      }
    }

    public static function appoint($argName, $argFunction)
    {
      $name = $argName;
      $function = $argFunction;
      self::$hooks[$name] = $function;
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
          $hook = $hooks[$name];
          $trigger = function($argHook) use ($args, &$result)
          {
            $myHook = $argHook;
            $length = count($args);
            if ($length == 1) $result = $myHook();
            else
            {
              $myArgs = array();
              for ($i = 1; $i < $length; $i ++)
              {
                array_push($myArgs, $args[$i]);
              }
              $result = call_user_func_array($myHook, $myArgs);
            }
          };
          if (is_object($hook)) $trigger($hook);
          else if (is_array($hook))
          {
            foreach ($hook as $key => $val)
            {
              if (is_object($val)) $trigger($val);
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