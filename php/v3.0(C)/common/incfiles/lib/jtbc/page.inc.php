<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class page
  {
    public static $errorCode = 0;
    public static $init = false;
    public static $para = array();
    private static $title = array();

    public static function formatResult($argStatus, $argResult)
    {
      $status = $argStatus;
      $result = $argResult;
      $tmpstr = '<?xml version="1.0" encoding="' . CHARSET . '"?>';
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
      $tmpstr = '<?xml version="1.0" encoding="' . CHARSET . '"?><result status="' . base::getNum($status, 0) . '" message="' . base::htmlEncode($message) . '" para="' . base::htmlEncode($para) . '"></result>';
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
        if ($type != 'default') self::$errorCode = 404;
        else
        {
          $tmpstr = tpl::take('.default', 'tpl');
          $tmpstr = tpl::parse($tmpstr);
          if (base::isEmpty($tmpstr))
          {
            $adjunctDefault = self::getPara('adjunct_default');
            $adjunctDefaultModule = 'module' . ucfirst($adjunctDefault);
            if (!is_callable(array($class, $adjunctDefaultModule))) self::$errorCode = 404;
            else $tmpstr = call_user_func(array($class, $adjunctDefaultModule));
          }
        }
      }
      self::setHeader();
      self::setPara('processtime', (microtime(true) - STARTTIME));
      //$tmpstr .= '<!--Processed in ' . base::formatSecond(self::getPara('processtime')) . '-->';
      return $tmpstr;
    }

    public static function getErrorResult($argCode = 404)
    {
      $tmpstr = '';
      $code = base::getNum($argCode, 0);
      if ($code == 403)
      {
        http_response_code(403);
        $tmpstr = tpl::take('global.config.403', 'tpl');
      }
      else if ($code == 404)
      {
        http_response_code(404);
        $tmpstr = tpl::take('global.config.404', 'tpl');
      }
      return $tmpstr;
    }

    public static function setHeader()
    {
      $noCache = self::getPara('noCache');
      $contentType = self::getPara('contentType');
      if (base::isEmpty($contentType)) $contentType = 'text/html';
      if ($noCache === true)
      {
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');
      }
      if ($contentType == 'text/html' || $contentType == 'text/xml')
      {
        header('Content-Type: ' . $contentType . '; charset=' . CHARSET);
      }
      else
      {
        header('Content-Type: ' . $contentType);
      }
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
      self::$para['http'] = request::isHTTPS() ? 'https://' : 'http://';
      self::$para['http_host'] = request::server('HTTP_HOST');
      self::$para['route'] = route::getRoute();
      self::$para['genre'] = route::getCurrentGenre();
      self::$para['assetspath'] = ASSETSPATH;
      self::$para['global.assetspath'] = route::getActualRoute(ASSETSPATH);
      self::$para['folder'] = route::getCurrentFolder();
      self::$para['filename'] = route::getCurrentFilename();
      self::$para['lang'] = request::getForeLang();
      self::$para['referer'] = request::server('HTTP_REFERER');
      self::$para['uri'] = route::getScriptName();
      self::$para['urs'] = request::server('QUERY_STRING');
      self::$para['url'] = base::isEmpty(self::$para['urs'])? self::$para['uri']: self::$para['uri'] . '?' . self::$para['urs'];
      self::$para['urlpre'] = self::$para['http'] . self::$para['http_host'];
      self::$para['fullurl'] = self::$para['urlpre'] . self::$para['url'];
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>