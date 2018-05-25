<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class page
  {
    public static $init = false;
    public static $para = array();
    private static $title = array();

    public static function formatResult($argStatus, $argResult)
    {
      $status = $argStatus;
      $result = $argResult;
      $tmpstr = '<?xml version="1.0" encoding="utf-8"?>';
      if (!is_array($result))
      {
        $result = str_replace(']]>', ']]]]><![CDATA[>', $result);
        $tmpstr .= '<result status="' . base::getNum($status, 0) . '"><![CDATA[' . $result . ']]></result>';
      }
      else
      {
        $tmpstr .= '<result status="' . base::getNum($status, 0) . '">';
        if (count($result) == count($result, 1))
        {
          $tmpstr .= '<item';
          foreach ($result as $key => $val)
          {
            if (!is_numeric($key))
            {
              $tmpstr .= ' ' . base::htmlEncode(base::getLRStr($key, '_', 'rightr')) . '="' . base::htmlEncode($val) . '"';
            }
          }
          $tmpstr .= '></item>';
        }
        else
        {
          foreach ($result as $i => $item)
          {
            if (is_array($item))
            {
              $tmpstr .= '<item';
              foreach ($item as $key => $val)
              {
                if (!is_numeric($key))
                {
                  $tmpstr .= ' ' . base::htmlEncode(base::getLRStr($key, '_', 'rightr')) . '="' . base::htmlEncode($val) . '"';
                }
              }
              $tmpstr .= '></item>';
            }
          }
        }
        $tmpstr .= '</result>';
      }
      return $tmpstr;
    }

    public static function formatMsgResult($argStatus, $argMessage, $argPara = '')
    {
      $status = $argStatus;
      $message = $argMessage;
      $para = $argPara;
      $tmpstr = '<?xml version="1.0" encoding="utf-8"?><result status="' . base::getNum($status, 0) . '" message="' . base::htmlEncode($message) . '" para="' . base::htmlEncode($para) . '"></result>';
      return $tmpstr;
    }

    public static function getPara($argName)
    {
      $para = null;
      $name = $argName;
      if (self::$init == false)
      {
        self::$init = true;
        self::init();
      }
      if (array_key_exists($name, self::$para)) $para = self::$para[$name];
      return $para;
    }

    public static function getPagePara($argName)
    {
      $name = $argName;
      $para = self::getPara($name);
      if (base::isEmpty($para)) $para = tpl::take('global.public.' . $name, 'lng');
      return $para;
    }

    public static function getPageTitle()
    {
      $tmpstr = '';
      $title = self::$title;
      if (!empty($title))
      {
        foreach ($title as $key => $val)
        {
          $tmpstr = $val . SEPARATOR . $tmpstr;
        }
      }
      $tmpstr = $tmpstr . tpl::take('global.index.title', 'lng');
      return $tmpstr;
    }

    public static function getResult()
    {
      $tmpstr = '';
      $type = request::get('type');
      $action = request::get('action');
      if (base::isEmpty($type)) $type = 'default';
      $class = get_called_class();
      $module = 'module' . ucfirst($type);
      if ($type == 'action') $module = 'moduleAction' . ucfirst($action);
      if (is_callable(array($class, 'start'))) call_user_func(array($class, 'start'));
      if (is_callable(array($class, $module))) $tmpstr = call_user_func(array($class, $module));
      else
      {
        if ($type == 'default')
        {
          $tmpstr = tpl::take('.default', 'tpl');
          $tmpstr = tpl::parse($tmpstr);
          if (base::isEmpty($tmpstr))
          {
            $adjunctDefault = self::getPara('adjunct_default');
            $adjunctDefaultModule = 'module' . ucfirst($adjunctDefault);
            if (is_callable(array($class, $adjunctDefaultModule))) $tmpstr = call_user_func(array($class, $adjunctDefaultModule));
          }
        }
      }
      self::setPara('processtime', (microtime(true) - STARTTIME));
      return $tmpstr;
    }

    public static function setPara($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      self::$para[$name] = $value;
      return $value;
    }

    public static function setPagePara($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      return self::setPara($name, $value);
    }

    public static function setPageTitle($argTitle)
    {
      $title = $argTitle;
      if (!base::isEmpty($title)) array_push(self::$title, $title);
      return self::getPageTitle();
    }

    public static function init()
    {
      self::$para['http'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
      self::$para['http_host'] = $_SERVER['HTTP_HOST'];
      self::$para['route'] = route::getRoute();
      self::$para['genre'] = route::getCurrentGenre();
      self::$para['assetspath'] = ASSETSPATH;
      self::$para['global.assetspath'] = route::getActualRoute(ASSETSPATH);
      self::$para['folder'] = route::getCurrentFolder();
      self::$para['filename'] = route::getCurrentFilename();
      self::$para['lang'] = request::getForeLang();
      self::$para['referer'] = @$_SERVER['HTTP_REFERER'];
      self::$para['uri'] = $_SERVER['SCRIPT_NAME'];
      self::$para['urs'] = $_SERVER['QUERY_STRING'];
      self::$para['url'] = self::$para['uri'];
      self::$para['urlpre'] = self::$para['http'] . self::$para['http_host'];
      if (!base::isEmpty(self::$para['urs'])) self::$para['url'] .= '?' . self::$para['urs'];
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
